<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property ?string $img_src
 * @property ?string $img_figcaption
 * @property bool $is_open
 * @property string $creation_date Date de publication du menu (Y-m-d)
 * @property string $modification_date Dernière reconstruction depuis WordPress (sert de cache)
 */
#[Table('menus', timestamps: false)]
#[Fillable(['img_src', 'img_figcaption', 'is_open', 'creation_date', 'modification_date'])]
class Menu extends Model
{
    protected function casts(): array
    {
        return [
            'is_open' => 'boolean',
            'modification_date' => 'datetime',
        ];
    }

    /**
     * @return HasMany<Dish, $this>
     */
    public function dishes(): HasMany
    {
        return $this->hasMany(Dish::class)->orderBy('position');
    }

    /**
     * @return HasOne<SmsResponse, $this>
     */
    public function smsResponse(): HasOne
    {
        return $this->hasOne(SmsResponse::class);
    }
}
