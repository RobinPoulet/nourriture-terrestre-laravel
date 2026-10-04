<?php

namespace Tests\Feature;

use App\Models\Dish;
use App\Models\Menu;
use App\Models\Rating;
use App\Models\User;
use App\Services\DeviceAuth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class RatingTest extends TestCase
{
    use RefreshDatabase;

    private Menu $menu;

    /** @var Dish[] */
    private array $dishes;

    private string $token;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-05 10:00');
        [$this->menu, $this->dishes] = OrderTest::createMenu();

        $this->token = str_repeat('c', 64);
        $this->user = User::query()->create(['name' => 'Alice', 'creation_date' => '2026-01-01', 'cookie_hash' => hash('sha256', $this->token)]);
        $this->user->orders()->create()->dishes()->attach([
            $this->dishes[0]->id => ['quantity' => 1],
            $this->dishes[1]->id => ['quantity' => 0],
        ]);

        Carbon::setTestNow('2026-10-05 13:00'); // vote ouvert
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function vote(int $dishId, int $rating)
    {
        return $this->withCredentials()
            ->withUnencryptedCookie(DeviceAuth::COOKIE_NAME, $this->token)
            ->postJson('/vote', ['dish_id' => $dishId, 'rating' => $rating]);
    }

    public function test_user_can_vote_once_for_an_ordered_dish(): void
    {
        $this->vote($this->dishes[0]->id, 4)->assertExactJson(['success' => true, 'avg' => 4.0, 'count' => 1]);
        $this->vote($this->dishes[0]->id, 5)->assertExactJson(['error' => 'Vous avez déjà voté pour ce plat']);

        $this->assertSame(4, Rating::query()->sole()->rating);
    }

    public function test_vote_is_refused_for_a_dish_not_ordered(): void
    {
        $this->vote($this->dishes[1]->id, 4)->assertExactJson(['error' => 'Ce plat ne fait pas partie de votre commande']);
    }

    public function test_vote_is_refused_outside_window_and_for_unknown_device(): void
    {
        $this->postJson('/vote', ['dish_id' => $this->dishes[0]->id, 'rating' => 4])
            ->assertExactJson(['error' => 'Utilisateur non identifié']);

        Carbon::setTestNow('2026-10-05 12:00');
        $this->vote($this->dishes[0]->id, 4)->assertExactJson(['error' => 'La fenêtre de vote est fermée']);
    }

    public function test_rankings_are_displayed(): void
    {
        $this->vote($this->dishes[0]->id, 5);

        $this->withUnencryptedCookie(DeviceAuth::COOKIE_NAME, $this->token)
            ->get('/ranking')
            ->assertOk()
            ->assertSee('Vote ouvert')
            ->assertSee('5.0');

        $this->get('/classement')->assertOk()->assertSee('Velouté de potimarron');
    }
}
