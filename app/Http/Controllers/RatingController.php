<?php

namespace App\Http\Controllers;

use App\Enums\SettingKey;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Rating;
use App\Models\Setting;
use App\Services\DeviceAuth;
use App\Services\MenuService;
use App\Support\MenuCalendar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class RatingController extends Controller
{
    public function __construct(private readonly MenuService $menus, private readonly DeviceAuth $device) {}

    /**
     * Classement des plats de la semaine et vote
     */
    public function index(): View
    {
        $menu = $this->menus->current();
        $user = $this->device->user();
        $ranking = Rating::rankingForMenu($menu->id);

        $userOrderedDishIds = [];
        $userRatings = [];
        if ($user) {
            $userOrderedDishIds = $this->mondayOrder($user->id)?->orderedDishIds() ?? [];
            $userRatings = Rating::userRatings($user->id, $menu->id);
        }

        // Plats notés en tête (moyenne décroissante), non notés à la fin
        $dishes = $menu->dishes
            ->sort(function ($a, $b) use ($ranking) {
                $hasA = isset($ranking[$a->id]);
                $hasB = isset($ranking[$b->id]);
                if ($hasA !== $hasB) {
                    return $hasB <=> $hasA;
                }

                return ($ranking[$b->id]['avg'] ?? 0) <=> ($ranking[$a->id]['avg'] ?? 0);
            })
            ->values();

        return view('ranking.index', [
            'menu' => $menu,
            'dishes' => $dishes,
            'ranking' => $ranking,
            'canVote' => $this->canVote($menu),
            'user' => $user,
            'userOrderedDishIds' => $userOrderedDishIds,
            'userRatings' => $userRatings,
        ]);
    }

    /**
     * Classement global : tous les plats notés, toutes semaines confondues
     */
    public function classement(): View
    {
        return view('ranking.classement', ['ranking' => Rating::globalRanking()]);
    }

    /**
     * Soumettre un vote (AJAX)
     */
    public function vote(Request $request): JsonResponse
    {
        $user = $this->device->user();
        if (! $user) {
            return $this->error('Utilisateur non identifié');
        }

        $dishId = (int) $request->input('dish_id', 0);
        $rating = (int) $request->input('rating', 0);
        if ($rating < 1 || $rating > 5) {
            return $this->error('Note invalide (1-5 attendu)');
        }

        try {
            $menu = $this->menus->current();
        } catch (Throwable) {
            return $this->error('Impossible de récupérer le menu');
        }

        if (! $this->canVote($menu)) {
            return $this->error('La fenêtre de vote est fermée');
        }
        if (! $menu->dishes->contains('id', $dishId)) {
            return $this->error('Ce plat ne fait pas partie du menu');
        }

        $order = $this->mondayOrder($user->id);
        if (! $order) {
            return $this->error("Vous n'avez pas commandé cette semaine");
        }
        if (! in_array($dishId, $order->orderedDishIds(), true)) {
            return $this->error('Ce plat ne fait pas partie de votre commande');
        }

        $alreadyVoted = Rating::query()
            ->where(['user_id' => $user->id, 'dish_id' => $dishId, 'menu_id' => $menu->id])
            ->exists();
        if ($alreadyVoted) {
            return $this->error('Vous avez déjà voté pour ce plat');
        }

        Rating::query()->upsert(
            [['user_id' => $user->id, 'dish_id' => $dishId, 'menu_id' => $menu->id, 'rating' => $rating]],
            ['user_id', 'dish_id', 'menu_id'],
            ['rating'],
        );

        $updated = Rating::rankingForMenu($menu->id)[$dishId] ?? ['avg' => $rating, 'count' => 1];

        return response()->json(['success' => true, 'avg' => $updated['avg'], 'count' => $updated['count']]);
    }

    private function canVote(Menu $menu): bool
    {
        return MenuCalendar::canVote($menu->creation_date) || Setting::isEnabled(SettingKey::ForceOpenVote);
    }

    /**
     * Commande passée par l'utilisateur le lundi de la semaine courante
     */
    private function mondayOrder(int $userId): ?Order
    {
        return Order::query()
            ->where('user_id', $userId)
            ->where('creation_date', MenuCalendar::mondayDate())
            ->first();
    }

    private function error(string $message): JsonResponse
    {
        return response()->json(['error' => $message]);
    }
}
