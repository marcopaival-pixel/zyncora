<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Setores (Sectors) - Substituindo ou complementando attendance_queues se necessário
        if (! Schema::hasTable('sectors')) {
            Schema::create('sectors', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('color')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. Contatos (Contacts) para o CRM
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('phone')->index();
            $table->string('email')->nullable();
            $table->string('avatar_url')->nullable();
            $table->json('custom_fields')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'phone']);
        });

        // 3. Etiquetas (Tags)
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('color')->default('#cbd5e1');
            $table->timestamps();
        });

        Schema::create('contact_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
        });

        // 4. Respostas Rápidas (Quick Replies)
        Schema::create('quick_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('shortcut'); // Ex: /olá
            $table->text('message');
            $table->timestamps();
        });

        // 5. CRM: Funis (Pipelines) e Etapas
        Schema::create('pipelines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('pipeline_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pipeline_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pipeline_stage_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->decimal('value', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 6. Avaliação de Atendimento (Ratings/CSAT)
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('score'); // 1-5
            $table->text('comment')->nullable();
            $table->timestamps();
        });

        // 7. Notas Internas
        Schema::create('internal_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Atendente que criou a nota
            $table->text('content');
            $table->timestamps();
        });

        // 8. Agendamento de Mensagens
        Schema::create('message_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('message');
            $table->timestamp('send_at');
            $table->string('status')->default('pending'); // pending, sent, failed
            $table->timestamps();
        });

        // Atualizar Conversations para usar contact_id e sector_id
        Schema::table('conversations', function (Blueprint $table) {
            $table->foreignId('contact_id')->nullable()->after('company_id')->constrained()->nullOnDelete();
            $table->foreignId('sector_id')->nullable()->after('attendance_queue_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropForeign(['contact_id']);
            $table->dropColumn('contact_id');
            $table->dropForeign(['sector_id']);
            $table->dropColumn('sector_id');
        });

        Schema::dropIfExists('message_schedules');
        Schema::dropIfExists('internal_notes');
        Schema::dropIfExists('ratings');
        Schema::dropIfExists('deals');
        Schema::dropIfExists('pipeline_stages');
        Schema::dropIfExists('pipelines');
        Schema::dropIfExists('quick_replies');
        Schema::dropIfExists('contact_tag');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('sectors');
    }
};
