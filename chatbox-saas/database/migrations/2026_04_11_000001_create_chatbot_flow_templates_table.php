<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_flow_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('flow_data');
            $table->string('category')->default('general');
            $table->boolean('is_public')->default(false); // For platform-wide templates
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_flow_templates');
    }
};
