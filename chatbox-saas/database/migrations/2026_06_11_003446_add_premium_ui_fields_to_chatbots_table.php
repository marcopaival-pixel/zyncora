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
            $table->string('avatar_path')->nullable();
            $table->string('avatar_type')->default('default'); // 'default', 'ai', 'company', 'custom'
            $table->string('primary_color')->nullable();
            $table->string('secondary_color')->nullable();
            $table->string('header_color')->nullable();
            $table->string('message_color')->nullable();
            $table->text('out_of_office_message')->nullable();
            $table->boolean('is_menu_enabled')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chatbots', function (Blueprint $table) {
            $table->dropColumn([
                'avatar_path',
                'avatar_type',
                'primary_color',
                'secondary_color',
                'header_color',
                'message_color',
                'out_of_office_message',
                'is_menu_enabled',
            ]);
        });
    }
};
