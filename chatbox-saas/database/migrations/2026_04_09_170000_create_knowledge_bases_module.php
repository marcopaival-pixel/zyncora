<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_bases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('content');
            $table->string('source_type', 32)->default('text'); // text, url, file
            $table->string('source_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('chatbots', function (Blueprint $table) {
            $table->text('ai_instruction')->nullable()->after('initial_message');
            $table->boolean('use_ai')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_bases');
        Schema::table('chatbots', function (Blueprint $table) {
            $table->dropColumn(['ai_instruction', 'use_ai']);
        });
    }
};
