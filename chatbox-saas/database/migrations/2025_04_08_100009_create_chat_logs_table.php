<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('log_type', 64);
            $table->text('description')->nullable();
            $table->json('context')->nullable();
            $table->timestamp('logged_at')->useCurrent();
            $table->timestamps();

            $table->index(['company_id', 'logged_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_logs');
    }
};
