<?php

namespace App\Http\Controllers;

use App\Enums\SettingKey;
use App\Models\Announcement;
use App\Models\Dish;
use App\Models\Setting;
use App\Services\MenuService;
use App\Services\WordPressPost;
use App\Support\MenuCalendar;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(MenuService $menus): View
    {
        $menu = $menus->current();
        $announcements = Announcement::query()->visible()->get();

        return view('home', [
            'menu' => $menu,
            'canDisplayForm' => MenuCalendar::canDisplayOrderForm($menu->creation_date),
            'dateFormatee' => MenuCalendar::formatMenuMonday($menu->creation_date),
            'dishesWithCategory' => $this->dishesWithCategory($menu->dishes->all()),
            'announcements' => $announcements,
            'showTicker' => $announcements->isNotEmpty() && ! Setting::isEnabled(SettingKey::TickerDisabled),
        ]);
    }

    /**
     * Associe à chaque plat ses informations de catégorie et un flag showDivider.
     *
     * @return array<int, array{dish: Dish, cat: array, showDivider: bool}>
     */
    private function dishesWithCategory(array $dishes): array
    {
        $returnValue = [];
        $total = count($dishes);
        $prevLabel = null;

        foreach (array_values($dishes) as $index => $dish) {
            $cat = $this->categoryInfo($index, $total);
            $returnValue[] = [
                'dish' => $dish,
                'cat' => $cat,
                'showDivider' => $cat['label'] !== $prevLabel,
            ];
            $prevLabel = $cat['label'];
        }

        return $returnValue;
    }

    /**
     * Classes CSS et label de catégorie selon la position du plat.
     *
     * @return array{label: string, icon: string, accent: string, badge: string, divider: string, dividerLine: string}
     */
    private function categoryInfo(int $index, int $total): array
    {
        $plat = [
            'label' => 'Plat',
            'icon' => $total <= 2 ? '🍽️' : '🍲',
            'accent' => 'border-indigo-400 dark:border-indigo-500',
            'badge' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
            'divider' => 'text-indigo-500 dark:text-indigo-400',
            'dividerLine' => 'border-indigo-200 dark:border-indigo-800',
        ];

        return match (WordPressPost::categoryForPosition($index, $total)) {
            'entree' => [
                'label' => 'Entrée',
                'icon' => '🥗',
                'accent' => 'border-emerald-400 dark:border-emerald-600',
                'badge' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
                'divider' => 'text-emerald-600 dark:text-emerald-400',
                'dividerLine' => 'border-emerald-200 dark:border-emerald-800',
            ],
            'dessert' => [
                'label' => 'Dessert',
                'icon' => '🍮',
                'accent' => 'border-amber-400 dark:border-amber-600',
                'badge' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                'divider' => 'text-amber-600 dark:text-amber-400',
                'dividerLine' => 'border-amber-200 dark:border-amber-800',
            ],
            default => $plat,
        };
    }
}
