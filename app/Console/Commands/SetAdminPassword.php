<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

/**
 * Définit le mot de passe d'un utilisateur et le passe administrateur
 * (remplace scripts/set_admin_password.php)
 */
#[Signature('admin:password {name : Nom de l\'utilisateur}')]
#[Description('Définit le mot de passe d\'un utilisateur et le passe administrateur')]
class SetAdminPassword extends Command
{
    private const int MIN_LENGTH = 12;

    public function handle(): int
    {
        $user = User::query()->where('name', $this->argument('name'))->first();
        if (! $user) {
            $this->error("Utilisateur « {$this->argument('name')} » introuvable");

            return self::FAILURE;
        }

        $password = (string) $this->secret('Mot de passe ('.self::MIN_LENGTH.' caractères minimum)');
        if (mb_strlen($password) < self::MIN_LENGTH) {
            $this->error('Mot de passe trop court');

            return self::FAILURE;
        }

        $user->update(['password_hash' => Hash::make($password), 'is_admin' => true]);
        $this->info("Mot de passe défini, $user->name est administrateur.");

        return self::SUCCESS;
    }
}
