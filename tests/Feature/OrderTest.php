<?php

namespace Tests\Feature;

use App\Models\Dish;
use App\Models\Menu;
use App\Models\Order;
use App\Models\User;
use App\Services\DeviceAuth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    private Menu $menu;

    /** @var Dish[] */
    private array $dishes;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-05 10:00'); // lundi, formulaire ouvert

        [$this->menu, $this->dishes] = self::createMenu();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * Menu déjà en cache (récent, image présente) : aucun appel à WordPress
     *
     * @return array{0: Menu, 1: Dish[]}
     */
    public static function createMenu(): array
    {
        $menu = Menu::query()->create([
            'img_src' => 'favicon-32x32.png',
            'img_figcaption' => 'Photo',
            'is_open' => true,
            'creation_date' => '2026-10-04',
            'modification_date' => now(),
        ]);

        $dishes = [];
        foreach (['Velouté de potimarron', 'Curry de légumes', 'Tarte aux pommes'] as $position => $name) {
            $dishes[] = Dish::query()->create([
                'name' => $name,
                'total' => 1,
                'menu_id' => $menu->id,
                'position' => $position,
                'category' => ['entree', 'plat', 'dessert'][$position],
            ]);
        }

        return [$menu, $dishes];
    }

    public function test_pages_render(): void
    {
        foreach (['/', '/commande', '/display-orders', '/ranking', '/classement', '/admin/login'] as $uri) {
            $this->get($uri)->assertOk();
        }

        $this->get('/')->assertSee('lundi 5 octobre 2026')->assertSee('Curry de légumes');
        $this->get('/commande')->assertSee('Passe ta commande');
    }

    public function test_order_is_stored_and_device_remembered(): void
    {
        $user = User::query()->create(['name' => 'Alice', 'creation_date' => '2026-01-01']);

        $response = $this->post('/create-order', [
            'user' => $user->id,
            'dishes' => [$this->dishes[0]->id => '2', $this->dishes[1]->id => '0', $this->dishes[2]->id => '1'],
            'perso' => "Sans sel, s'il te plaît",
        ]);

        $response->assertRedirect(route('orders.index'))->assertSessionHas('success');
        $response->assertCookie(DeviceAuth::COOKIE_NAME, null, false);

        $order = Order::query()->sole();
        $this->assertSame("Sans sel, s'il te plaît", $order->perso);
        $this->assertSame('2026-10-05', $order->creation_date);
        $this->assertSame(
            [$this->dishes[0]->id => 2, $this->dishes[1]->id => 0, $this->dishes[2]->id => 1],
            $order->quantitiesByDish(),
        );

        $token = $response->getCookie(DeviceAuth::COOKIE_NAME, false)->getValue();
        $this->assertSame(hash('sha256', $token), $user->fresh()->cookie_hash);

        $this->withUnencryptedCookie(DeviceAuth::COOKIE_NAME, $token)
            ->get('/display-orders')
            ->assertOk()
            ->assertSee('Moi')
            ->assertSee('data-action="edit-order"', false);
    }

    public function test_order_requires_a_dish_and_a_user(): void
    {
        $this->post('/create-order', ['dishes' => [$this->dishes[0]->id => '0']])
            ->assertRedirect(route('orders.create'))
            ->assertSessionHasErrors();

        $this->assertSame(0, Order::query()->count());
    }

    public function test_user_already_bound_to_another_device_cannot_order(): void
    {
        $user = User::query()->create(['name' => 'Alice', 'creation_date' => '2026-01-01', 'cookie_hash' => hash('sha256', 'other')]);

        $this->post('/create-order', ['user' => $user->id, 'dishes' => [$this->dishes[0]->id => '1']])
            ->assertRedirect(route('orders.create'))
            ->assertSessionHasErrors();

        $this->assertSame(0, Order::query()->count());
    }

    public function test_device_cannot_order_for_someone_else(): void
    {
        $token = str_repeat('a', 64);
        User::query()->create(['name' => 'Alice', 'creation_date' => '2026-01-01', 'cookie_hash' => hash('sha256', $token)]);
        $bob = User::query()->create(['name' => 'Bob', 'creation_date' => '2026-01-01']);

        $this->withUnencryptedCookie(DeviceAuth::COOKIE_NAME, $token)
            ->post('/create-order', ['user' => $bob->id, 'dishes' => [$this->dishes[0]->id => '1']])
            ->assertSessionHasErrors();

        $this->assertSame(0, Order::query()->count());
    }

    public function test_only_owner_can_update_or_delete_order(): void
    {
        $token = str_repeat('b', 64);
        $alice = User::query()->create(['name' => 'Alice', 'creation_date' => '2026-01-01', 'cookie_hash' => hash('sha256', $token)]);
        $order = $alice->orders()->create(['perso' => '']);
        $order->dishes()->attach([$this->dishes[0]->id => ['quantity' => 1]]);
        $payload = ['dishes' => [$this->dishes[0]->id => '0', $this->dishes[1]->id => '3'], 'perso' => 'Bien cuit'];

        // Appareil inconnu
        $this->post("/edit-order/{$order->id}", $payload)->assertSessionHasErrors();
        $this->post("/delete-order/{$order->id}")->assertSessionHasErrors();
        $this->assertSame([$this->dishes[0]->id => 1], $order->fresh()->quantitiesByDish());

        // Propriétaire
        $this->withUnencryptedCookie(DeviceAuth::COOKIE_NAME, $token)
            ->post("/edit-order/{$order->id}", $payload)
            ->assertSessionHas('success');
        $this->assertSame([$this->dishes[0]->id => 0, $this->dishes[1]->id => 3], $order->fresh()->quantitiesByDish());
        $this->assertSame('Bien cuit', $order->fresh()->perso);

        $this->withUnencryptedCookie(DeviceAuth::COOKIE_NAME, $token)
            ->post("/delete-order/{$order->id}")
            ->assertSessionHas('success');
        $this->assertNull($order->fresh());
    }

    public function test_totals_per_dish_for_the_day(): void
    {
        foreach ([[2, 1], [1, 0]] as $i => [$first, $second]) {
            $user = User::query()->create(['name' => "User $i", 'creation_date' => '2026-01-01']);
            $user->orders()->create()->dishes()->attach([
                $this->dishes[0]->id => ['quantity' => $first],
                $this->dishes[1]->id => ['quantity' => $second],
            ]);
        }

        $this->assertSame([$this->dishes[0]->id => 3, $this->dishes[1]->id => 1], Order::totalQuantityByDish());
    }
}
