<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Quelques utilisateurs pour une base locale vierge (mot de passe : « password »).
     * Pour un admin : php artisan admin:password "<nom>"
     */
    public function run(): void
    {
        foreach (['Alice', 'Bob', 'Chloé'] as $name) {
            User::query()->firstOrCreate(['name' => $name], [
                'email' => Str::lower(Str::ascii($name)).'@example.com',
                'password' => 'password',
                'creation_date' => now()->toDateString(),
            ]);
        }
    }
}
