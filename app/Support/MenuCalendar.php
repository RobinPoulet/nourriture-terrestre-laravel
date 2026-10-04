<?php

namespace App\Support;

use DateTimeImmutable;
use Illuminate\Support\Carbon;

/**
 * Règles de calendrier : un nouveau menu est publié chaque dimanche,
 * on commande le lundi jusqu'à 11h15 et on vote du lundi 12h45 au samedi.
 */
class MenuCalendar
{
    /** @var int Heure limite de commande le lundi, en minutes depuis minuit (11h15) */
    public const int ORDER_DEADLINE_MINUTES = 11 * 60 + 15;

    /** @var int Ouverture du vote le lundi, en minutes depuis minuit (12h45) */
    public const int VOTE_OPENING_MINUTES = 12 * 60 + 45;

    /**
     * On peut commander le lundi de 00h00 à 11h15 inclus, s'il y a bien un menu publié cette semaine
     * (menu publié le dimanche : au plus 2 jours d'écart avec aujourd'hui)
     *
     * @param  string  $dateMenu  Date de publication du menu (Y-m-d)
     */
    public static function canDisplayOrderForm(string $dateMenu): bool
    {
        $now = Carbon::now();

        return $now->isMonday()
            && self::minutesSinceMidnight($now) <= self::ORDER_DEADLINE_MINUTES
            && self::daysBetween($now->toDateString(), $dateMenu) <= 2;
    }

    /**
     * Timestamp de fermeture du formulaire aujourd'hui (11h15)
     */
    public static function orderDeadlineTimestamp(): int
    {
        return Carbon::today()->addMinutes(self::ORDER_DEADLINE_MINUTES)->getTimestamp();
    }

    /**
     * Date du lundi de la semaine courante (Y-m-d)
     */
    public static function mondayDate(): string
    {
        return Carbon::now()->startOfWeek(Carbon::MONDAY)->toDateString();
    }

    /**
     * Vote possible à partir du lundi 12h45 (1h30 après la fermeture des commandes)
     * jusqu'au samedi inclus. Le dimanche est exclu (nouvelle semaine).
     *
     * @param  string  $dateMenu  Date de publication du menu (Y-m-d)
     */
    public static function canVote(string $dateMenu): bool
    {
        $now = Carbon::now();

        if (! self::isNewMenuAvailable($dateMenu) || $now->isSunday()) {
            return false;
        }

        if ($now->isMonday()) {
            return self::minutesSinceMidnight($now) >= self::VOTE_OPENING_MINUTES;
        }

        return true;
    }

    /**
     * Le menu a-t-il été publié il y a moins d'une semaine ?
     *
     * @param  string  $dateMenu  Date de publication du menu (Y-m-d)
     */
    public static function isNewMenuAvailable(string $dateMenu): bool
    {
        $now = Carbon::now();
        $daysDifference = $now->toDateTimeImmutable()->diff(new DateTimeImmutable($dateMenu))->days;

        return ($now->isSunday() || $now->isMonday())
            ? $daysDifference <= 8
            : $daysDifference < 8;
    }

    /**
     * Date du menu au format long, ramenée au lundi qui suit (ex. "lundi 6 octobre 2026")
     *
     * @param  string  $dateMenu  Date de publication du menu (Y-m-d)
     */
    public static function formatMenuMonday(string $dateMenu): string
    {
        $date = Carbon::parse($dateMenu);
        if (! $date->isMonday()) {
            $date = $date->next(Carbon::MONDAY);
        }

        return $date->locale('fr')->isoFormat('dddd D MMMM YYYY');
    }

    /**
     * Nombre de jours (absolu) entre deux dates
     */
    private static function daysBetween(string $date1, string $date2): int
    {
        return (new DateTimeImmutable($date1))->diff(new DateTimeImmutable($date2))->days;
    }

    private static function minutesSinceMidnight(Carbon $date): int
    {
        return $date->hour * 60 + $date->minute;
    }
}
