<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Quelques utilisateurs pour une base locale vierge.
     * Pour un admin : php artisan admin:password "<nom>"
     */
    public function run(): void
    {
        foreach (['Alice', 'Bob', 'Chloé'] as $name) {
            User::query()->firstOrCreate(['name' => $name], ['creation_date' => now()->toDateString()]);
        }
    }
}
