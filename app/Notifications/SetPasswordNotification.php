<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Lien pour choisir son mot de passe : invitation envoyée par l'admin ou « mot de passe oublié »
 */
class SetPasswordNotification extends Notification
{
    public function __construct(
        #[\SensitiveParameter] private readonly string $token,
        private readonly bool $isInvitation = false,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(User $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $url = self::url($notifiable, $this->token);
        $expireHours = intdiv((int) config('auth.passwords.users.expire'), 60);

        $message = (new MailMessage)
            ->greeting("Bonjour $notifiable->name,")
            ->salutation('À lundi, Nourriture Terrestre');

        if ($this->isInvitation) {
            return $message
                ->subject('Ton compte Nourriture Terrestre')
                ->line('Les commandes se font maintenant avec un compte personnel.')
                ->line("Choisis ton mot de passe pour l'activer :")
                ->action('Choisir mon mot de passe', $url)
                ->line("Ce lien est valable $expireHours heures.");
        }

        return $message
            ->subject('Réinitialisation de ton mot de passe')
            ->line('Tu as demandé à réinitialiser ton mot de passe.')
            ->action('Choisir un nouveau mot de passe', $url)
            ->line("Ce lien est valable $expireHours heures. Si tu n'es pas à l'origine de cette demande, ignore cet email.");
    }

    /**
     * Lien vers le formulaire de choix du mot de passe
     */
    public static function url(User $user, #[\SensitiveParameter] string $token): string
    {
        return route('password.reset', ['token' => $token, 'email' => $user->email]);
    }
}
