<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('role', 32)->default('agent')->after('password');
            $table->string('phone', 64)->nullable()->after('email');
            $table->string('status', 32)->default('active')->after('role');
            $table->timestamp('last_active_at')->nullable()->after('remember_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
            $table->dropColumn(['role', 'phone', 'status', 'last_active_at']);
        });
    }
};
