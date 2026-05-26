<?php

namespace App\Jobs;

use App\Models\Conversation;
use App\Services\AIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AnalyzeConversationWithAi implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    /**
     * @var list<int>
     */
    public array $backoff = [60, 180];

    public function __construct(public int $conversationId)
    {
        $this->onQueue((string) config('chatbox.ai.queue', 'default'));
    }

    public function handle(AIService $aiService): void
    {
        $conversation = Conversation::query()
            ->withoutGlobalScopes() // Precisamos ignorar o escopo para encontrar o registo inicial e setar o tenant
            ->find($this->conversationId);

        if (! $conversation) {
            return;
        }

        // Inicializa o contexto do inquilino para este Job
        if ($conversation->company_id) {
            app(\App\Services\TenantService::class)->setCompany($conversation->company);
        }

        $aiService->analyzeConversation($conversation);
    }
}
