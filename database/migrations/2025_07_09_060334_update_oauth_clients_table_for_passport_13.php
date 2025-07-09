<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For MySQL, we need to recreate the table to change from auto-increment to UUID
        // Drop and recreate oauth_clients table with UUID primary key
        Schema::dropIfExists('oauth_clients');

        Schema::create('oauth_clients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('name');
            $table->string('secret', 100)->nullable();
            $table->string('provider')->nullable();
            $table->text('redirect');
            $table->boolean('personal_access_client');
            $table->boolean('password_client');
            $table->boolean('revoked');
            $table->timestamps();
        });

        // Update related tables that reference oauth_clients
        Schema::table('oauth_personal_access_clients', function (Blueprint $table) {
            $table->uuid('client_id')->change();
        });

        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            $table->uuid('client_id')->change();
        });

        Schema::table('oauth_auth_codes', function (Blueprint $table) {
            $table->uuid('client_id')->change();
        });

        // oauth_refresh_tokens table doesn't directly reference client_id, so no changes needed
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert oauth_clients table back to integer IDs
        Schema::table('oauth_clients', function (Blueprint $table) {
            $table->dropPrimary(['id']);
            $table->bigIncrements('id')->change();
        });

        // Revert related tables
        Schema::table('oauth_personal_access_clients', function (Blueprint $table) {
            $table->unsignedBigInteger('client_id')->change();
        });

        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            $table->unsignedBigInteger('client_id')->change();
        });

        Schema::table('oauth_auth_codes', function (Blueprint $table) {
            $table->unsignedBigInteger('client_id')->change();
        });
    }
};
