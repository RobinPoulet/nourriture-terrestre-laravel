<?php

namespace App\Models;

use App\Notifications\SetPasswordNotification;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Connexion par email + mot de passe (session, « se souvenir de moi »).
 * Pas d'inscription libre : l'admin crée le compte et envoie une invitation pour choisir le mot de passe.
 *
 * @property int $id
 * @property string $name
 * @property ?string $email
 * @property bool $is_admin
 * @property string $creation_date
 * @property ?string $password
 */
#[Table('users', timestamps: false)]
#[Fillable(['name', 'email', 'is_admin', 'creation_date', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use Notifiable;

    protected function casts(): array
    {
        return [
            'is_admin' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * Compte prêt à l'emploi : email renseigné et mot de passe choisi
     */
    public function hasActivatedAccount(): bool
    {
        return $this->email !== null && $this->password !== null;
    }

    /**
     * Lien « mot de passe oublié »
     */
    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $this->notify(new SetPasswordNotification($token));
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
