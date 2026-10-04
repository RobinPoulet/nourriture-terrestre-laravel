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
        if (Schema::hasTable('menus')) {
            return;
        }

        Schema::create('menus', function (Blueprint $table) {
            $table->increments('id');
            $table->string('img_src')->nullable();
            $table->string('img_figcaption')->nullable();
            $table->boolean('is_open');
            $table->date('creation_date')->useCurrent();
            $table->dateTime('modification_date')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
