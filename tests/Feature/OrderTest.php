<?php

namespace Tests\Feature;

use App\Models\Dish;
use App\Models\Menu;
use App\Models\Order;
use App\Models\User;
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

    /**
     * Utilisateur avec un compte activé (email + mot de passe)
     */
    public static function createUser(string $name, bool $isAdmin = false): User
    {
        return User::query()->create([
            'name' => $name,
            'email' => strtolower($name).'@example.com',
            'password' => 'mot-de-passe-solide',
            'is_admin' => $isAdmin,
            'creation_date' => '2026-01-01',
        ]);
    }

    public function test_pages_render(): void
    {
        foreach (['/', '/display-orders', '/ranking', '/classement', '/login', '/forgot-password'] as $uri) {
            $this->get($uri)->assertOk();
        }

        $this->get('/')->assertSee('lundi 5 octobre 2026')->assertSee('Curry de légumes');
    }

    public function test_order_form_requires_login(): void
    {
        $this->get('/commande')->assertRedirect(route('login'));

        $this->actingAs(self::createUser('Alice'))
            ->get('/commande')
            ->assertOk()
            ->assertSee('Passe ta commande')
            ->assertSee('Alice');
    }

    public function test_order_is_stored_for_the_logged_in_user(): void
    {
        $alice = self::createUser('Alice');

        $response = $this->actingAs($alice)->post('/create-order', [
            'dishes' => [$this->dishes[0]->id => '2', $this->dishes[1]->id => '0', $this->dishes[2]->id => '1'],
            'perso' => "Sans sel, s'il te plaît",
        ]);

        $response->assertRedirect(route('orders.index'))->assertSessionHas('success', 'Ta commande a bien été enregistrée Alice');

        $order = Order::query()->sole();
        $this->assertSame($alice->id, (int) $order->user_id);
        $this->assertSame("Sans sel, s'il te plaît", $order->perso);
        $this->assertSame('2026-10-05', $order->creation_date);
        $this->assertSame(
            [$this->dishes[0]->id => 2, $this->dishes[1]->id => 0, $this->dishes[2]->id => 1],
            $order->quantitiesByDish(),
        );

        $this->get('/display-orders')
            ->assertOk()
            ->assertSee('Moi')
            ->assertSee('data-action="edit-order"', false);
    }

    public function test_guest_cannot_order(): void
    {
        $this->post('/create-order', ['dishes' => [$this->dishes[0]->id => '1']])->assertRedirect(route('login'));

        $this->assertSame(0, Order::query()->count());
    }

    public function test_order_requires_a_dish(): void
    {
        $this->assertFlashedError($this->actingAs(self::createUser('Alice'))
            ->post('/create-order', ['dishes' => [$this->dishes[0]->id => '0']])
            ->assertRedirect(route('orders.create')), 'Il faut commander au moins un plat');

        $this->assertSame(0, Order::query()->count());
    }

    public function test_only_owner_can_update_or_delete_order(): void
    {
        $alice = self::createUser('Alice');
        $bob = self::createUser('Bob');
        $order = $alice->orders()->create(['perso' => '']);
        $order->dishes()->attach([$this->dishes[0]->id => ['quantity' => 1]]);
        $payload = ['dishes' => [$this->dishes[0]->id => '0', $this->dishes[1]->id => '3'], 'perso' => 'Bien cuit'];

        // Invité
        $this->post("/edit-order/{$order->id}", $payload)->assertRedirect(route('login'));

        // Autre utilisateur
        $this->assertFlashedError($this->actingAs($bob)->post("/edit-order/{$order->id}", $payload), 'Tu ne peux modifier que ta propre commande');
        $this->assertFlashedError($this->actingAs($bob)->post("/delete-order/{$order->id}"), 'Tu ne peux supprimer que ta propre commande');
        $this->assertSame([$this->dishes[0]->id => 1], $order->fresh()->quantitiesByDish());

        // Propriétaire
        $this->actingAs($alice)->post("/edit-order/{$order->id}", $payload)->assertSessionHas('success');
        $this->assertSame([$this->dishes[0]->id => 0, $this->dishes[1]->id => 3], $order->fresh()->quantitiesByDish());
        $this->assertSame('Bien cuit', $order->fresh()->perso);

        $this->actingAs($alice)->post("/delete-order/{$order->id}")->assertSessionHas('success');
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
