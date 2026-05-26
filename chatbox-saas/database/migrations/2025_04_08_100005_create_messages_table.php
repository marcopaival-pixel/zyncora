<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->string('sender_type', 32);
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->text('body');
            $table->string('message_type', 32)->default('text');
            $table->string('attachment_path')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamps();

            $table->index(['conversation_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
