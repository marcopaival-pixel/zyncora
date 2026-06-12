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
        Schema::create('chatbot_flow_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chatbot_id')->constrained()->cascadeOnDelete();
            $table->string('suggested_intent');
            $table->unsignedInteger('message_count')->default(1);
            $table->json('examples')->nullable();
            $table->string('status')->default('pending'); // pending, accepted, ignored
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_flow_suggestions');
    }
};
