<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $name
 * @property int $total Nombre de menus dans lesquels le plat est apparu
 * @property ?int $menu_id Dernier menu dans lequel le plat est apparu
 * @property int $position Position dans ce menu
 * @property ?string $category entree | plat | dessert
 */
#[Table('dishes', timestamps: false)]
#[Fillable(['name', 'total', 'menu_id', 'position', 'category', 'creation_date', 'modification_date'])]
class Dish extends Model
{
    protected function casts(): array
    {
        return [
            'total' => 'integer',
            'position' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Menu, $this>
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * @return BelongsToMany<Order, $this>
     */
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_dishes')->withPivot('quantity');
    }

    /**
     * Premier mot du nom du plat (en-têtes de tableau, SMS)
     */
    public function shortName(): string
    {
        return strtok(trim($this->name), ' ') ?: $this->name;
    }
}
