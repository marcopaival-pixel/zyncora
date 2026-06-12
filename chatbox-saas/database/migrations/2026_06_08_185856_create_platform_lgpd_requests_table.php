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
        Schema::create('platform_lgpd_requests', function (Blueprint $table) {
            $table->id();
            $table->string('protocol')->unique();
            $table->string('name');
            $table->string('email');
            $table->enum('request_type', ['access', 'correction', 'deletion', 'portability', 'revoke']);
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->text('details')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_lgpd_requests');
    }
};
