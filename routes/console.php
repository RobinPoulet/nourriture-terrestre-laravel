<?php

use App\Enums\SettingKey;
use App\Models\Setting;
use Illuminate\Support\Facades\Schedule;

// Récapitulatif SMS le lundi, à l'heure réglée dans l'admin (nécessite `php artisan schedule:run` chaque minute)
Schedule::command('sms:send-orders')
    ->mondays()
    ->everyMinute()
    ->when(fn () => now()->format('H:i') === Setting::getValue(SettingKey::SmsSendTime))
    ->withoutOverlapping();
