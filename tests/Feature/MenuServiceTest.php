<?php

namespace Tests\Feature;

use App\Models\Dish;
use App\Models\Menu;
use App\Services\MenuService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MenuServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-05 10:00');

        Http::preventStrayRequests();
        Http::fake([
            '*/wp-json/wp/v2/posts*' => Http::response([[
                'date' => '2026-10-04T18:00:00',
                'title' => ['rendered' => 'Le menu de la semaine'],
                'content' => ['rendered' => '<figure><img src="https://example.test/menu.jpg"><figcaption>Bon appétit</figcaption></figure>'
                    .'<ul><li>Velouté</li><li>Curry</li><li>Gratin</li><li>Tarte</li><li>Mousse</li></ul>'],
            ]]),
            'https://example.test/*' => Http::response('', 404),
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_menu_is_built_from_last_wordpress_post(): void
    {
        $menu = app(MenuService::class)->build();

        $this->assertTrue($menu->is_open);
        $this->assertSame('2026-10-04', $menu->creation_date);
        $this->assertSame('Bon appétit', $menu->img_figcaption);
        $this->assertSame(
            ['Velouté' => 'entree', 'Curry' => 'plat', 'Gratin' => 'plat', 'Tarte' => 'dessert', 'Mousse' => 'dessert'],
            Dish::query()->orderBy('position')->pluck('category', 'name')->all(),
        );
    }

    public function test_rebuilding_existing_menu_does_not_increment_dish_totals(): void
    {
        app(MenuService::class)->build();
        app(MenuService::class)->build();

        $this->assertSame(1, Menu::query()->count());
        $this->assertSame([1], Dish::query()->distinct()->pluck('total')->all());
    }

    public function test_recent_menu_is_reused_without_calling_wordpress(): void
    {
        OrderTest::createMenu();
        Http::fake(fn () => throw new \RuntimeException('WordPress ne doit pas être appelé'));

        $this->assertSame('2026-10-04', app(MenuService::class)->current()->creation_date);
    }
}
