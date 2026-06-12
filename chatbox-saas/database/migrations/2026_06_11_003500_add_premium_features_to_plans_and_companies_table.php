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
        Schema::table('plans', function (Blueprint $table) {
            $table->boolean('has_advanced_customization')->default(false);
            $table->boolean('has_quick_replies')->default(false);
            $table->boolean('has_contextual_ai')->default(false);
            $table->boolean('has_chatbot_faq')->default(false);
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->boolean('has_advanced_customization')->default(false);
            $table->boolean('has_quick_replies')->default(false);
            $table->boolean('has_contextual_ai')->default(false);
            $table->boolean('has_chatbot_faq')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'has_advanced_customization',
                'has_quick_replies',
                'has_contextual_ai',
                'has_chatbot_faq',
            ]);
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'has_advanced_customization',
                'has_quick_replies',
                'has_contextual_ai',
                'has_chatbot_faq',
            ]);
        });
    }
};
