<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('company')->nullable();
            $table->string('role')->nullable();
            $table->string('whatsapp')->nullable();
            $table->integer('attendants_qty')->nullable();
            $table->string('segment')->nullable();

            // Tracking fields
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('utm_term')->nullable();
            $table->text('referer')->nullable();
            $table->string('browser')->nullable();
            $table->string('device')->nullable();
            $table->string('os')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('ip')->nullable();
            $table->string('origin')->nullable()->default('website');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'company', 'role', 'whatsapp', 'attendants_qty', 'segment',
                'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term',
                'referer', 'browser', 'device', 'os', 'country', 'city', 'ip', 'origin',
            ]);
        });
    }
};
