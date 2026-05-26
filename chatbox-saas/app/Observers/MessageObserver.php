<?php

namespace App\Observers;

use App\Events\MessageCreated;
use App\Jobs\AnalyzeConversationWithAi;
use App\Models\Message;

class MessageObserver
{
    public function created(Message $message): void
    {
        // 1. Sempre disparar evento para Realtime (Reverb)
        MessageCreated::dispatch($message);

        // 2. Analisar leads e sentimento se for mensagem do visitante
        if ($message->sender_type === 'visitor') {
            AnalyzeConversationWithAi::dispatch((int) $message->conversation_id);
        }
    }
}
