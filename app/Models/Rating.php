<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @property int $user_id
 * @property int $dish_id
 * @property int $menu_id
 * @property int $rating
 */
#[Table('ratings', timestamps: false)]
#[Fillable(['user_id', 'dish_id', 'menu_id', 'rating'])]
class Rating extends Model
{
    /**
     * Note moyenne et nombre de votes par plat pour un menu donné.
     *
     * @return array<int, array{avg: float, count: int}>
     */
    public static function rankingForMenu(int $menuId): array
    {
        return static::query()
            ->where('menu_id', $menuId)
            ->groupBy('dish_id')
            ->selectRaw('dish_id, ROUND(AVG(rating), 1) as avg_rating, COUNT(*) as vote_count')
            ->toBase()
            ->get()
            ->mapWithKeys(fn ($row) => [(int) $row->dish_id => [
                'avg' => (float) $row->avg_rating,
                'count' => (int) $row->vote_count,
            ]])
            ->all();
    }

    /**
     * Votes d'un utilisateur pour un menu donné.
     *
     * @return array<int, int> [dish_id => rating]
     */
    public static function userRatings(int $userId, int $menuId): array
    {
        return static::query()
            ->where('user_id', $userId)
            ->where('menu_id', $menuId)
            ->pluck('rating', 'dish_id')
            ->mapWithKeys(fn ($rating, $dishId) => [(int) $dishId => (int) $rating])
            ->all();
    }

    /**
     * Classement global de tous les plats ayant au moins un vote, toutes semaines confondues.
     *
     * @return array<int, array{dish_id: int, dish_name: string, category: ?string, avg: float, count: int}>
     */
    public static function globalRanking(): array
    {
        return DB::table('ratings as r')
            ->join('dishes as d', 'd.id', '=', 'r.dish_id')
            ->groupBy('d.id', 'd.name', 'd.category')
            ->orderByDesc('avg_rating')
            ->orderByDesc('vote_count')
            ->selectRaw('d.id as dish_id, d.name as dish_name, d.category as category,
                         ROUND(AVG(r.rating), 1) as avg_rating, COUNT(*) as vote_count')
            ->get()
            ->map(fn ($row) => [
                'dish_id' => (int) $row->dish_id,
                'dish_name' => $row->dish_name,
                'category' => $row->category,
                'avg' => (float) $row->avg_rating,
                'count' => (int) $row->vote_count,
            ])
            ->all();
    }
}
