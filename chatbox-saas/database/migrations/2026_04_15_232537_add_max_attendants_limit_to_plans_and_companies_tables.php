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
            $table->integer('max_attendants')->default(1)->after('max_users');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->integer('max_attendants')->default(1)->after('max_users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('max_attendants');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('max_attendants');
        });
    }
};
