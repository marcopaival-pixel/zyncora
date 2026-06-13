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
            $table->decimal('price_yearly', 10, 2)->nullable()->after('price');
            $table->integer('sort_order')->default(0)->after('is_active');
            $table->string('update_behavior')->default('keep_old')->after('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['price_yearly', 'sort_order', 'update_behavior']);
        });
    }
};
