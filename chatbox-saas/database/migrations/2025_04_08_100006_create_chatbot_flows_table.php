<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_flows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('trigger')->nullable();
            $table->text('question')->nullable();
            $table->text('answer')->nullable();
            $table->string('next_flow_key')->nullable();
            $table->string('action', 64)->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_flows');
    }
};
