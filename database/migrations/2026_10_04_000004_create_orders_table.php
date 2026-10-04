<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reprend le schéma de la base historique : ignorée si les tables existent déjà.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->increments('id');
                $table->text('perso')->nullable();
                $table->integer('user_id')->index();
                $table->date('creation_date')->useCurrent();
                $table->date('modification_date')->useCurrent();
            });
        }

        if (! Schema::hasTable('order_dishes')) {
            Schema::create('order_dishes', function (Blueprint $table) {
                $table->unsignedInteger('order_id')->index();
                $table->unsignedInteger('dish_id');
                $table->unsignedInteger('quantity');
                $table->index(['order_id', 'dish_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_dishes');
        Schema::dropIfExists('orders');
    }
};
