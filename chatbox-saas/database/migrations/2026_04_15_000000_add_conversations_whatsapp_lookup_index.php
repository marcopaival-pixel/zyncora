<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Otimiza o lookup de conversa WhatsApp (company + canal + telefone).
     */
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->index(
                ['company_id', 'channel_id', 'client_phone'],
                'conversations_company_channel_client_phone_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropIndex('conversations_company_channel_client_phone_index');
        });
    }
};
