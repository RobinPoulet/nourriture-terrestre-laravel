<?php

namespace App\Enums;

/**
 * Clés des paramètres du site modifiables depuis l'admin (table settings)
 */
enum SettingKey: string
{
    /** Désactiver la bande d'annonces */
    case TickerDisabled = 'ticker_disabled';

    /** Forcer l'ouverture du formulaire de commande */
    case ForceOpenForm = 'force_open_form';

    /** Forcer l'ouverture des votes */
    case ForceOpenVote = 'force_open_vote';

    /** Heure d'envoi du SMS récapitulatif */
    case SmsSendTime = 'sms_send_time';

    public function default(): string
    {
        return match ($this) {
            self::SmsSendTime => '11:00',
            default => '0',
        };
    }
}
