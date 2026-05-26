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
        // 1. LGPD Settings
        Schema::create('lgpd_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->longText('privacy_policy')->nullable();
            $table->text('consent_term')->nullable();
            $table->integer('retention_days')->default(0); // 0 = indefinite
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. LGPD Consents
        Schema::create('lgpd_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('customer_id')->nullable(); // External ID or session ID
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('consent_given')->default(false);
            $table->timestamp('consent_at')->nullable();
            $table->timestamps();
        });

        // 3. LGPD Requests (Access/Deletion)
        Schema::create('lgpd_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('customer_id')->nullable();
            $table->enum('type', ['export', 'delete', 'anonymize'])->default('export');
            $table->enum('status', ['pending', 'processing', 'completed', 'canceled'])->default('pending');
            $table->json('request_details')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // 4. LGPD Audit Logs
        Schema::create('lgpd_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action'); // e.g., 'view_customer_data', 'export_data', 'delete_data'
            $table->string('resource_type')->nullable();
            $table->string('resource_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lgpd_audit_logs');
        Schema::dropIfExists('lgpd_requests');
        Schema::dropIfExists('lgpd_consents');
        Schema::dropIfExists('lgpd_settings');
    }
};
