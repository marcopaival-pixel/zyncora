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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('event'); // e.g., 'login', 'update_ticket', 'delete_user'
            $table->string('description')->nullable();
            $table->string('subject_type')->nullable();
            $table->string('subject_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->json('properties')->nullable();
            $table->timestamps();
        });

        // Add 2FA fields to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('two_factor_secret')->nullable()->after('password');
            $table->json('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at']);
        });
        Schema::dropIfExists('activity_logs');
    }
};
