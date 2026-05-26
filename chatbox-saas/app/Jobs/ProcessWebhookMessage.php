<?php

namespace App\Jobs;

use App\Models\Conversation;
use App\Services\ChatbotReplyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessWebhookMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Conversation $conversation,
        protected string $text
    ) {}

    /**
     * Execute the job.
     */
    public function handle(ChatbotReplyService $chatbotService): void
    {
        // Inicializa o contexto do inquilino para que o escopo global do BelongsToCompany funcione neste Job
        if ($this->conversation->company_id) {
            app(\App\Services\TenantService::class)->setCompany($this->conversation->company);
        }

        try {
            Log::info("Processing async message feedback", [
                'conversation_id' => $this->conversation->id,
                'message' => substr($this->text, 0, 50)
            ]);

            $chatbotService->maybeAutoReply($this->conversation, $this->text);
            
        } catch (\Exception $e) {
            Log::error("Failed to process webhook message: " . $e->getMessage(), [
                'conversation' => $this->conversation->id,
                'exception' => $e
            ]);
            
            throw $e;
        }
    }
}
