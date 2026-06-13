<?php

namespace App\Services\Chatbot\Agents;

use App\Models\Chatbot;
use App\Models\Conversation;

class LeadRecoveryAgent extends BaseAgent
{
    public function handle(Conversation $conversation, Chatbot $chatbot, string $userMessage, array $memory): string
    {
        $systemPrompt = "Você é o Agente de Recuperação de Leads. Seu objetivo é reengajar um cliente que parou de responder, oferecendo alguma ajuda ou perguntando se ele ainda tem interesse.\n";

        return $this->generateGeminiResponse($systemPrompt, $memory, $userMessage);
    }
}
