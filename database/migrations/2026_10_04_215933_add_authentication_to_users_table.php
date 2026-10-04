<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Authentification par email + mot de passe : remplace l'identification par cookie d'appareil.
 * Les utilisateurs existants gardent leur id (commandes et votes intacts) et reçoivent une invitation.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('password_hash', 'password');
            $table->dropColumn('cookie_hash');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->unique()->after('name');
            $table->rememberToken();
        });

        if (! Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->dropColumn(['email', 'remember_token']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('password', 'password_hash');
            $table->string('cookie_hash', 64)->nullable();
        });
    }
};
