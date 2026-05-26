<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('channel_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('whatsapp_phone', 64)->nullable();
            $table->text('initial_message')->nullable();
            $table->time('hours_start')->nullable();
            $table->time('hours_end')->nullable();
            $table->string('default_channel', 32)->default('site');
            $table->string('status', 32)->default('inactive');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbots');
    }
};
