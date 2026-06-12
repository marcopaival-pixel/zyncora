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
        Schema::create('chatbot_flow_node_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chatbot_id')->constrained()->cascadeOnDelete();
            $table->string('node_id')->index();
            $table->date('date')->index();
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('transfers')->default(0);
            $table->unsignedInteger('dropoffs')->default(0);
            $table->timestamps();

            $table->unique(['chatbot_id', 'node_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_flow_node_stats');
    }
};
