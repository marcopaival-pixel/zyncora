<?php

namespace App\Services\Chatbot\Agents;

use App\Models\Conversation;
use App\Models\Chatbot;

class HumanTransferAgent extends BaseAgent
{
    public function handle(Conversation $conversation, Chatbot $chatbot, string $userMessage, array $memory): string
    {
        $conversation->update([
            'status' => 'open_human',
        ]);
        
        return "Estou transferindo o seu atendimento para um humano. Por favor, aguarde na linha.";
    }
}
