<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('cnpj', 32)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 64)->nullable();
            $table->string('logo_path')->nullable();
            $table->string('chat_color', 32)->default('#0ea5e9');
            $table->text('welcome_message')->nullable();
            $table->text('offline_message')->nullable();
            $table->json('business_hours')->nullable();
            $table->boolean('auto_reply_enabled')->default(true);
            $table->string('status', 32)->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
