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
        // 1. chatbot_licenses
        Schema::create('chatbot_licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chatbot_id')->constrained('chatbots')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('license_key')->unique(); // UUID ou Hash
            $table->string('status', 32)->default('active'); // active, revoked, expired, suspended
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->index(['company_id', 'status']);
        });

        // 2. chatbot_domains
        Schema::create('chatbot_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chatbot_id')->constrained('chatbots')->cascadeOnDelete();
            $table->string('domain'); // ex: site.com
            $table->string('status', 32)->default('pending'); // pending, approved, blocked
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->unique(['chatbot_id', 'domain']);
        });

        // 3. chatbot_security_tokens
        Schema::create('chatbot_security_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chatbot_id')->constrained('chatbots')->cascadeOnDelete();
            $table->string('public_token', 64)->unique(); // Exposto no JS
            $table->string('secret_key', 64); // Apenas backend para JWT/Assinatura
            $table->timestamp('rotated_at')->nullable();
            $table->timestamps();
        });

        // 4. widget_access_logs
        Schema::create('widget_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chatbot_id')->constrained('chatbots')->cascadeOnDelete();
            $table->string('domain')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('session_id')->nullable();
            $table->string('fingerprint_hash')->nullable();
            $table->string('status', 32)->default('allowed'); // allowed, blocked
            $table->string('block_reason')->nullable();
            $table->integer('risk_score')->default(0);
            $table->timestamps();
            
            $table->index(['chatbot_id', 'created_at']);
            $table->index(['ip_address', 'created_at']);
        });

        // 5. widget_fraud_alerts
        Schema::create('widget_fraud_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chatbot_id')->constrained('chatbots')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('risk_level', 32); // low, medium, high, critical
            $table->string('trigger_reason');
            $table->json('fingerprint_data')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('widget_fraud_alerts');
        Schema::dropIfExists('widget_access_logs');
        Schema::dropIfExists('chatbot_security_tokens');
        Schema::dropIfExists('chatbot_domains');
        Schema::dropIfExists('chatbot_licenses');
    }
};
