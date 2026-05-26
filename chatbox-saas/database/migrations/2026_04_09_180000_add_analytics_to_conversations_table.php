<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->timestamp('first_response_at')->nullable()->after('started_at');
            $table->integer('response_time_seconds')->nullable()->after('first_response_at');
            $table->integer('total_messages')->default(0)->after('closed_at');
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn(['first_response_at', 'response_time_seconds', 'total_messages']);
        });
    }
};
