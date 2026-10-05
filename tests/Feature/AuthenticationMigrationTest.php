<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuthenticationMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_resumes_on_a_half_migrated_users_table(): void
    {
        // État laissé par un premier passage interrompu : password_hash déjà renommé, le reste non fait
        Schema::table('users', fn (Blueprint $table) => $table->dropUnique(['email']));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['email', 'remember_token']));
        Schema::table('users', fn (Blueprint $table) => $table->string('cookie_hash', 64)->nullable());

        $migration = require database_path('migrations/2026_10_04_215933_add_authentication_to_users_table.php');
        $migration->up();
        $migration->up(); // relancer sur une table déjà migrée ne casse rien

        $this->assertTrue(Schema::hasColumns('users', ['email', 'password', 'remember_token']));
        $this->assertFalse(Schema::hasColumn('users', 'cookie_hash'));
        $this->assertFalse(Schema::hasColumn('users', 'password_hash'));
    }
}
