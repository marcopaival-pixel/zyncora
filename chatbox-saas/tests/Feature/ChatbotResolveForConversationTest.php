<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\Chatbot;
use App\Models\Company;
use App\Models\Conversation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotResolveForConversationTest extends TestCase
{
    use RefreshDatabase;

    public function test_prefers_chatbot_bound_to_same_channel(): void
    {
        $company = Company::query()->create([
            'name' => 'Acme',
            'slug' => 'acme-'.uniqid(),
        ]);

        $channelSite = Channel::query()->create([
            'company_id' => $company->id,
            'type' => 'site',
            'status' => 'active',
        ]);

        $generic = Chatbot::query()->create([
            'company_id' => $company->id,
            'channel_id' => null,
            'name' => 'Genérico',
            'status' => 'active',
            'default_channel' => 'site',
        ]);

        $linked = Chatbot::query()->create([
            'company_id' => $company->id,
            'channel_id' => $channelSite->id,
            'name' => 'Ligado ao canal',
            'status' => 'active',
            'default_channel' => 'site',
        ]);

        $conversation = Conversation::query()->create([
            'company_id' => $company->id,
            'channel_id' => $channelSite->id,
            'visitor_token' => bin2hex(random_bytes(16)),
            'status' => 'open',
            'started_at' => now(),
        ]);

        $resolved = Chatbot::resolveForConversation($conversation);

        $this->assertNotNull($resolved);
        $this->assertSame($linked->id, $resolved->id);
        $this->assertNotSame($generic->id, $resolved->id);
    }

    public function test_falls_back_to_default_channel_when_no_explicit_binding(): void
    {
        $company = Company::query()->create([
            'name' => 'Beta',
            'slug' => 'beta-'.uniqid(),
        ]);

        $channelWa = Channel::query()->create([
            'company_id' => $company->id,
            'type' => 'whatsapp',
            'status' => 'active',
        ]);

        Chatbot::query()->create([
            'company_id' => $company->id,
            'channel_id' => null,
            'name' => 'Só site',
            'status' => 'active',
            'default_channel' => 'site',
        ]);

        $waBot = Chatbot::query()->create([
            'company_id' => $company->id,
            'channel_id' => null,
            'name' => 'WhatsApp lógico',
            'status' => 'active',
            'default_channel' => 'whatsapp',
        ]);

        $conversation = Conversation::query()->create([
            'company_id' => $company->id,
            'channel_id' => $channelWa->id,
            'visitor_token' => bin2hex(random_bytes(16)),
            'status' => 'open',
            'started_at' => now(),
        ]);

        $resolved = Chatbot::resolveForConversation($conversation);

        $this->assertNotNull($resolved);
        $this->assertSame($waBot->id, $resolved->id);
    }

    public function test_newest_active_when_multiple_match_default_channel(): void
    {
        $company = Company::query()->create([
            'name' => 'Gamma',
            'slug' => 'gamma-'.uniqid(),
        ]);

        $channelSite = Channel::query()->create([
            'company_id' => $company->id,
            'type' => 'site',
            'status' => 'active',
        ]);

        Chatbot::query()->create([
            'company_id' => $company->id,
            'channel_id' => null,
            'name' => 'Antigo',
            'status' => 'active',
            'default_channel' => 'site',
        ]);

        $newer = Chatbot::query()->create([
            'company_id' => $company->id,
            'channel_id' => null,
            'name' => 'Recente',
            'status' => 'active',
            'default_channel' => 'site',
        ]);

        $conversation = Conversation::query()->create([
            'company_id' => $company->id,
            'channel_id' => $channelSite->id,
            'visitor_token' => bin2hex(random_bytes(16)),
            'status' => 'open',
            'started_at' => now(),
        ]);

        $resolved = Chatbot::resolveForConversation($conversation);

        $this->assertNotNull($resolved);
        $this->assertSame($newer->id, $resolved->id);
    }

    public function test_query_modifier_filters_candidates(): void
    {
        $company = Company::query()->create([
            'name' => 'Delta',
            'slug' => 'delta-'.uniqid(),
        ]);

        $channelSite = Channel::query()->create([
            'company_id' => $company->id,
            'type' => 'site',
            'status' => 'active',
        ]);

        Chatbot::query()->create([
            'company_id' => $company->id,
            'channel_id' => null,
            'name' => 'Sem IA',
            'status' => 'active',
            'default_channel' => 'site',
            'use_ai' => false,
        ]);

        $withAi = Chatbot::query()->create([
            'company_id' => $company->id,
            'channel_id' => null,
            'name' => 'Com IA',
            'status' => 'active',
            'default_channel' => 'site',
            'use_ai' => true,
        ]);

        $conversation = Conversation::query()->create([
            'company_id' => $company->id,
            'channel_id' => $channelSite->id,
            'visitor_token' => bin2hex(random_bytes(16)),
            'status' => 'open',
            'started_at' => now(),
        ]);

        $resolved = Chatbot::resolveForConversation($conversation, function ($q) {
            $q->where('use_ai', true);
        });

        $this->assertNotNull($resolved);
        $this->assertSame($withAi->id, $resolved->id);
    }
}
