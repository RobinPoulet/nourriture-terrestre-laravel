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
        if (Schema::hasTable('users')) {
            return;
        }

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50);
            $table->date('creation_date')->useCurrent();
            $table->date('modification_date')->useCurrent();
            $table->tinyInteger('is_admin')->default(0);
            $table->string('cookie_hash', 64)->nullable();
            $table->string('password_hash')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
