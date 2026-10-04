<?php

namespace App\Providers;

use App\Services\MenuService;
use App\Services\SmsSender;
use App\Services\WordPressClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(WordPressClient::class, fn () => new WordPressClient(config('services.wordpress.url')));

        $this->app->singleton(SmsSender::class, fn () => new SmsSender(
            config('services.textbee.api_key'),
            config('services.textbee.device_id'),
            config('services.textbee.recipient'),
        ));

        // Résolus une fois par requête
        $this->app->scoped(MenuService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
