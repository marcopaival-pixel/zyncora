<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_page_settings', function (Blueprint $table) {
            $table->id();
            // Hero section
            $table->string('hero_title')->default('Automatize seu atendimento com IA e aumente suas vendas');
            $table->string('hero_subtitle')->default('Crie chatbots em poucos minutos, reduzindo custos e aumentando conversões.');

            // Stats / Social Proof
            $table->json('stats')->nullable();

            // Benefits
            $table->json('benefits')->nullable();

            // CTAs
            $table->string('primary_cta_text')->default('Começar agora');
            $table->string('secondary_cta_text')->default('Ver demonstração');
            $table->integer('trial_days')->default(7);

            // Success Message
            $table->string('success_message_title')->default('Recebemos sua solicitação com sucesso!');
            $table->string('success_message_subtitle')->default('Nossa equipe entrará em contato em breve.');

            // Contacts
            $table->string('contact_email')->nullable();
            $table->string('contact_whatsapp')->nullable();
            $table->string('contact_phone')->nullable();

            // Social Networks
            $table->string('social_linkedin')->nullable();
            $table->string('social_instagram')->nullable();
            $table->string('social_facebook')->nullable();
            $table->string('social_youtube')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_page_settings');
    }
};
