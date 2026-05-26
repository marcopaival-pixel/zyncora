<?php

namespace Tests\Feature;

use App\Models\ChatbotFlow;
use App\Models\Company;
use App\Models\Conversation;
use App\Services\ChatbotReplyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotKeywordChainTest extends TestCase
{
    use RefreshDatabase;

    public function test_next_flow_key_chains_second_answer_by_trigger_match(): void
    {
        $company = Company::query()->create([
            'name' => 'Encadeado',
            'slug' => 'chain-'.uniqid(),
            'auto_reply_enabled' => true,
        ]);

        $flowA = ChatbotFlow::query()->create([
            'company_id' => $company->id,
            'trigger' => 'inicio',
            'answer' => 'Primeira resposta',
            'next_flow_key' => 'segundo_passo',
            'active' => true,
            'sort_order' => 1,
        ]);

        ChatbotFlow::query()->create([
            'company_id' => $company->id,
            'trigger' => 'segundo_passo',
            'answer' => 'Segunda resposta',
            'next_flow_key' => null,
            'active' => true,
            'sort_order' => 2,
        ]);

        $conversation = Conversation::query()->create([
            'company_id' => $company->id,
            'channel_id' => null,
            'visitor_token' => bin2hex(random_bytes(16)),
            'status' => 'open',
            'started_at' => now(),
        ]);

        /** @var ChatbotReplyService $service */
        $service = app(ChatbotReplyService::class);
        $first = $service->maybeAutoReply($conversation, 'texto com inicio aqui');

        $this->assertNotNull($first);
        $this->assertSame('Primeira resposta', $first->body);

        $bodies = $conversation->messages()->orderBy('id')->pluck('body')->all();
        $this->assertSame(['Primeira resposta', 'Segunda resposta'], $bodies);
    }

    public function test_chain_stops_when_conversation_closed_by_end_action(): void
    {
        $company = Company::query()->create([
            'name' => 'Fecho',
            'slug' => 'close-'.uniqid(),
            'auto_reply_enabled' => true,
        ]);

        ChatbotFlow::query()->create([
            'company_id' => $company->id,
            'trigger' => 'adeus',
            'answer' => 'Até logo',
            'next_flow_key' => 'nunca',
            'action' => 'end',
            'active' => true,
            'sort_order' => 1,
        ]);

        ChatbotFlow::query()->create([
            'company_id' => $company->id,
            'trigger' => 'nunca',
            'answer' => 'Não deve enviar',
            'active' => true,
            'sort_order' => 2,
        ]);

        $conversation = Conversation::query()->create([
            'company_id' => $company->id,
            'channel_id' => null,
            'visitor_token' => bin2hex(random_bytes(16)),
            'status' => 'open',
            'started_at' => now(),
        ]);

        /** @var ChatbotReplyService $service */
        $service = app(ChatbotReplyService::class);
        $service->maybeAutoReply($conversation, 'adeus');

        $conversation->refresh();
        $this->assertSame('closed', $conversation->status);

        $this->assertSame(['Até logo'], $conversation->messages()->orderBy('id')->pluck('body')->all());
    }
}
