<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reprend le schéma de la base historique : ignorée si la table existe déjà.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ratings')) {
            return;
        }

        Schema::create('ratings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('dish_id');
            $table->unsignedInteger('menu_id');
            $table->unsignedTinyInteger('rating');
            $table->dateTime('created_at')->useCurrent();
            $table->unique(['user_id', 'dish_id', 'menu_id'], 'uq_user_dish_menu');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
