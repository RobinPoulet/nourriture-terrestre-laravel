<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

/**
 * Identification de l'appareil par un jeton aléatoire en cookie longue durée (son hash SHA-256 est en base) :
 * pré-sélection de l'utilisateur, blocage des commandes depuis une autre machine, attribution des votes.
 *
 * Le cookie est exclu du chiffrement Laravel (bootstrap/app.php) pour rester compatible
 * avec les cookies posés par l'ancienne application.
 */
class DeviceAuth
{
    /** @var string Nom du cookie d'identification de l'appareil */
    public const string COOKIE_NAME = 'selected_user';

    /** @var int Durée de vie du cookie en minutes (2 ans) */
    private const int COOKIE_LIFETIME_MINUTES = 60 * 24 * 730;

    /** @var string Attribut de la requête mémorisant l'utilisateur résolu */
    private const string REQUEST_ATTRIBUTE = 'device_user';

    /**
     * Utilisateur identifié par le cookie de l'appareil, null si inconnu
     */
    public function user(): ?User
    {
        $request = $this->request();
        if ($request->attributes->has(self::REQUEST_ATTRIBUTE)) {
            return $request->attributes->get(self::REQUEST_ATTRIBUTE);
        }

        $raw = $this->rawToken();
        $user = $raw !== null ? User::query()->where('cookie_hash', self::hash($raw))->first() : null;
        $request->attributes->set(self::REQUEST_ATTRIBUTE, $user);

        // Ancien format (base64 JSON prévisible) → remplacé par un jeton aléatoire
        if ($user && ! preg_match('/^[a-f0-9]{64}$/', $raw)) {
            $this->remember($user);
        }

        return $user;
    }

    /**
     * Associe l'appareil courant à l'utilisateur : nouveau jeton aléatoire en cookie, son hash en base
     */
    public function remember(User $user): void
    {
        $token = bin2hex(random_bytes(32));
        $this->queueCookie($token);
        $user->update(['cookie_hash' => self::hash($token)]);

        $this->request()->attributes->set(self::REQUEST_ATTRIBUTE, $user);
    }

    /**
     * Prolonge la durée de vie du cookie de l'appareil (même jeton)
     */
    public function refresh(): void
    {
        $raw = $this->rawToken();
        if ($raw !== null) {
            $this->queueCookie($raw);
        }
    }

    private function request(): Request
    {
        return request();
    }

    private function rawToken(): ?string
    {
        $value = $this->request()->cookie(self::COOKIE_NAME);

        return (is_string($value) && $value !== '') ? $value : null;
    }

    private function queueCookie(string $value): void
    {
        Cookie::queue(Cookie::make(
            self::COOKIE_NAME,
            $value,
            self::COOKIE_LIFETIME_MINUTES,
            '/',
            null,
            $this->request()->isSecure(),
            true,
            false,
            'lax',
        ));
    }

    private static function hash(string $raw): string
    {
        return hash('sha256', $raw);
    }
}
