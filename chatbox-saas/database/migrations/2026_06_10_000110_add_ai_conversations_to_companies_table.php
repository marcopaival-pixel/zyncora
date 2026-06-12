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
        Schema::table('companies', function (Blueprint $table) {
            $table->integer('ai_conversations_used')->default(0)->after('ai_credits_used');
            $table->string('ai_limit_action')->default('block')->after('ai_conversations_used'); // block, human_only, auto_buy, auto_upgrade
            $table->string('auto_buy_package')->nullable()->after('ai_limit_action'); // bronze, silver, gold, platinum
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['ai_conversations_used', 'ai_limit_action', 'auto_buy_package']);
        });
    }
};
