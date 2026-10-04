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
        if (Schema::hasTable('announcements')) {
            return;
        }

        Schema::create('announcements', function (Blueprint $table) {
            $table->increments('id');
            $table->text('message');
            $table->tinyInteger('is_visible')->default(1);
            $table->dateTime('created_at')->nullable()->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
