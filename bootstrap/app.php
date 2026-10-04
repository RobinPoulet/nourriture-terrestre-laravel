<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Services\DeviceAuth;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // HTTPS terminé par le reverse proxy de l'hébergeur
        $middleware->trustProxies(at: '*');

        // Jeton d'appareil en clair, compatible avec les cookies posés par l'ancienne application
        $middleware->encryptCookies(except: [DeviceAuth::COOKIE_NAME]);

        $middleware->alias(['admin' => EnsureUserIsAdmin::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
