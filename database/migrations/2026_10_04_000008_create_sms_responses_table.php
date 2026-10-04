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
        if (Schema::hasTable('sms_responses')) {
            return;
        }

        Schema::create('sms_responses', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyText('message');
            $table->string('destination', 20);
            $table->integer('sms_batch_id')->nullable();
            $table->string('status', 100)->nullable();
            $table->integer('menu_id')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_responses');
    }
};
