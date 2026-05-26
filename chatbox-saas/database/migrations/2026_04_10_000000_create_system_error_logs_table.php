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
        Schema::create('system_error_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('set null');
            $table->string('type')->index(); // SQL, Exception, API, Validation
            $table->text('message');
            $table->string('url')->nullable();
            $table->string('method')->nullable();
            $table->string('ip_address')->nullable();
            $table->json('payload')->nullable();
            $table->longText('stack_trace')->nullable();
            $table->string('file')->nullable();
            $table->integer('line')->nullable();
            $table->integer('status_code')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_error_logs');
    }
};
