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
            $table->integer('max_domains')->default(1)->after('max_chatbots');
            $table->integer('rate_limit_per_minute')->default(60)->after('max_domains');
            $table->boolean('has_fraud_protection')->default(false)->after('rate_limit_per_minute');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['max_domains', 'rate_limit_per_minute', 'has_fraud_protection']);
        });
    }
};
