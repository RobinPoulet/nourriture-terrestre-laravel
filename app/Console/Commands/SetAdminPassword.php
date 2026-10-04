<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Définit l'email (si besoin) et le mot de passe d'un utilisateur et le passe administrateur
 * (remplace scripts/set_admin_password.php)
 */
#[Signature('admin:password {name : Nom de l\'utilisateur} {--email= : Email de connexion (obligatoire s\'il n\'en a pas encore)}')]
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

        $email = $this->option('email') !== null ? mb_strtolower(trim($this->option('email'))) : $user->email;
        if ($email === null || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $this->error('Email manquant ou invalide : précise-le avec --email=');

            return self::FAILURE;
        }
        if (User::query()->where('email', $email)->whereKeyNot($user->id)->exists()) {
            $this->error("L'email $email est déjà utilisé par un autre compte");

            return self::FAILURE;
        }

        $password = (string) $this->secret('Mot de passe ('.self::MIN_LENGTH.' caractères minimum)');
        if (mb_strlen($password) < self::MIN_LENGTH) {
            $this->error('Mot de passe trop court');

            return self::FAILURE;
        }

        $user->update(['email' => $email, 'password' => $password, 'is_admin' => true]);
        $this->info("Mot de passe défini, $user->name est administrateur (connexion avec $email).");

        return self::SUCCESS;
    }
}
