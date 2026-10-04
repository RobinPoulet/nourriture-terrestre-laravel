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
        if (Schema::hasTable('dishes')) {
            return;
        }

        Schema::create('dishes', function (Blueprint $table) {
            $table->increments('id');
            $table->text('name')->nullable();
            $table->integer('total')->nullable();
            $table->unsignedInteger('menu_id')->nullable();
            $table->date('creation_date')->useCurrent();
            $table->date('modification_date')->useCurrent();
            $table->integer('position')->default(0);
            $table->enum('category', ['entree', 'plat', 'dessert'])->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dishes');
    }
};
