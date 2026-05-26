<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('channel_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('attendance_queue_id')->nullable()->constrained('attendance_queues')->nullOnDelete();
            $table->string('visitor_token', 64)->unique();
            $table->string('client_name')->nullable();
            $table->string('client_phone', 64)->nullable();
            $table->string('client_email')->nullable();
            $table->string('status', 32)->default('open');
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
