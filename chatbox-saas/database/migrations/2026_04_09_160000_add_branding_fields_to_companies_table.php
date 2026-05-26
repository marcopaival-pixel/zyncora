<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('brand_color', 7)->nullable()->after('chat_color');
            $table->string('favicon_path')->nullable()->after('logo_path');
            $table->string('panel_logo_path')->nullable()->after('logo_path');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['brand_color', 'favicon_path', 'panel_logo_path']);
        });
    }
};
