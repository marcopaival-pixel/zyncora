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
        Schema::create('platform_legal_documents', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['terms', 'privacy', 'cookies']);
            $table->string('version');
            $table->longText('content');
            $table->timestamp('published_at')->useCurrent();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_legal_documents');
    }
};
