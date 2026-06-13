<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->integer('max_messages')->default(-1)->after('max_ai_conversations'); // -1 for unlimited
            $table->integer('max_integrations')->default(-1)->after('max_messages');
            
            // Recursos Adicionais (Boleanos)
            $table->boolean('has_whatsapp')->default(true);
            $table->boolean('has_telegram')->default(true);
            $table->boolean('has_instagram')->default(true);
            $table->boolean('has_facebook')->default(true);
            $table->boolean('has_webchat')->default(true);
            $table->boolean('has_openai')->default(true);
            $table->boolean('has_rag')->default(false);
            $table->boolean('has_inbox')->default(true);
            $table->boolean('has_flow_builder')->default(true);
            $table->boolean('has_api')->default(false);
            $table->boolean('has_whitelabel')->default(false);
            $table->boolean('has_webhooks')->default(false);
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->integer('max_messages')->default(-1)->after('ai_credits_balance');
            $table->integer('max_integrations')->default(-1)->after('max_messages');
            
            // Recursos Adicionais (Boleanos)
            $table->boolean('has_whatsapp')->default(true);
            $table->boolean('has_telegram')->default(true);
            $table->boolean('has_instagram')->default(true);
            $table->boolean('has_facebook')->default(true);
            $table->boolean('has_webchat')->default(true);
            $table->boolean('has_openai')->default(true);
            $table->boolean('has_rag')->default(false);
            $table->boolean('has_inbox')->default(true);
            $table->boolean('has_flow_builder')->default(true);
            $table->boolean('has_api')->default(false);
            $table->boolean('has_whitelabel')->default(false);
            $table->boolean('has_webhooks')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'max_messages', 'max_integrations',
                'has_whatsapp', 'has_telegram', 'has_instagram', 'has_facebook', 
                'has_webchat', 'has_openai', 'has_rag', 'has_inbox', 
                'has_flow_builder', 'has_api', 'has_whitelabel', 'has_webhooks'
            ]);
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'max_messages', 'max_integrations',
                'has_whatsapp', 'has_telegram', 'has_instagram', 'has_facebook', 
                'has_webchat', 'has_openai', 'has_rag', 'has_inbox', 
                'has_flow_builder', 'has_api', 'has_whitelabel', 'has_webhooks'
            ]);
        });
    }
};
