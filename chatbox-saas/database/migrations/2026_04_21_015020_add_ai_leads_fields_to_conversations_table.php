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
        Schema::table('conversations', function (Blueprint $table) {
            $table->integer('ai_score')->default(0)->after('status'); // 0-100 (Intenção de compra)
            $table->string('ai_sentiment', 32)->nullable()->after('ai_score'); // positive, negative, neutral
            $table->text('ai_summary')->nullable()->after('ai_sentiment'); // Resumo automático da conversa
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn(['ai_score', 'ai_sentiment', 'ai_summary']);
        });
    }
};
