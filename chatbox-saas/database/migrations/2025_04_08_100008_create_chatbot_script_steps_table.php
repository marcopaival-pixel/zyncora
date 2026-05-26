<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_script_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chatbot_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('step_order')->default(0);
            $table->text('prompt')->nullable();
            $table->text('response')->nullable();
            $table->string('response_type', 32)->default('text');
            $table->string('next_step_key')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_script_steps');
    }
};
