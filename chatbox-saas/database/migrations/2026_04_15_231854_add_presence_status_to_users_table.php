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
        Schema::table('users', function (Blueprint $table) {
            $table->string('presence_status', 32)->default('offline')->after('status'); // online, offline, busy
            $table->unsignedInteger('max_simultaneous_chats')->default(10)->after('presence_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['presence_status', 'max_simultaneous_chats']);
        });
    }
};
