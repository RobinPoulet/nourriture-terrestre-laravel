<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property ?string $perso
 * @property int $user_id
 * @property string $creation_date
 */
#[Table('orders', timestamps: false)]
#[Fillable(['perso', 'user_id', 'creation_date', 'modification_date'])]
class Order extends Model
{
    protected static function booted(): void
    {
        // Date du jour côté PHP (fuseau de l'application) plutôt que la valeur par défaut du serveur SQL
        static::creating(function (Order $order) {
            $order->creation_date ??= now()->toDateString();
            $order->modification_date ??= now()->toDateString();
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsToMany<Dish, $this>
     */
    public function dishes(): BelongsToMany
    {
        return $this->belongsToMany(Dish::class, 'order_dishes')->withPivot('quantity');
    }

    /**
     * IDs des plats réellement commandés (quantité > 0)
     *
     * @return int[]
     */
    public function orderedDishIds(): array
    {
        return $this->dishes()
            ->wherePivot('quantity', '>', 0)
            ->pluck('dishes.id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * Quantité commandée par plat, sous forme [dish_id => quantity]
     *
     * @return array<int, int>
     */
    public function quantitiesByDish(): array
    {
        return $this->dishes
            ->mapWithKeys(fn (Dish $dish) => [(int) $dish->id => (int) $dish->pivot->quantity])
            ->all();
    }

    /**
     * Nombre total de chaque plat commandé à une date (aujourd'hui par défaut)
     *
     * @return array<int, int> [dish_id => quantité totale]
     */
    public static function totalQuantityByDish(?string $date = null): array
    {
        return DB::table('order_dishes as od')
            ->join('orders as o', 'od.order_id', '=', 'o.id')
            ->where('o.creation_date', $date ?? now()->toDateString())
            ->groupBy('od.dish_id')
            ->orderBy('od.dish_id')
            ->selectRaw('od.dish_id as dish_id, SUM(od.quantity) as total_quantity')
            ->get()
            ->mapWithKeys(fn ($row) => [(int) $row->dish_id => (int) $row->total_quantity])
            ->all();
    }
}
