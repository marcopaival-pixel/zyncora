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
        Schema::table('chatbots', function (Blueprint $table) {
            if (! Schema::hasColumn('chatbots', 'flow_data')) {
                $table->json('flow_data')->nullable()->after('status');
            }
            if (! Schema::hasColumn('chatbots', 'published_flow_data')) {
                $table->json('published_flow_data')->nullable()->after('flow_data');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chatbots', function (Blueprint $table) {
            $table->dropColumn(['flow_data', 'published_flow_data']);
        });
    }
};
