<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Deux niveaux distincts :
 *  - identification de l'appareil (jeton aléatoire en cookie longue durée, voir App\Services\DeviceAuth) ;
 *  - authentification admin (nom + mot de passe, guard Laravel en session).
 *
 * @property int $id
 * @property string $name
 * @property bool $is_admin
 * @property string $creation_date
 * @property ?string $cookie_hash
 * @property ?string $password_hash
 */
#[Table('users', timestamps: false)]
#[Fillable(['name', 'is_admin', 'creation_date', 'cookie_hash', 'password_hash'])]
#[Hidden(['cookie_hash', 'password_hash'])]
class User extends Authenticatable
{
    /** @var string Colonne du hash de mot de passe (utilisée par Auth::attempt) */
    protected $authPasswordName = 'password_hash';

    /** @var string Pas de jeton "se souvenir de moi" dans la table */
    protected $rememberTokenName = '';

    protected function casts(): array
    {
        return [
            'is_admin' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
