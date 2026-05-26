<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('type', 32);
            $table->string('external_ref', 191)->nullable();
            $table->text('token_api')->nullable();
            $table->string('status', 32)->default('active');
            $table->timestamps();

            $table->index(['company_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};
