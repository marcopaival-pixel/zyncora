<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('driver', 64);
            $table->text('credentials')->nullable();
            $table->string('webhook_verify_token')->nullable();
            $table->string('status', 32)->default('pending');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'driver']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_integrations');
    }
};
