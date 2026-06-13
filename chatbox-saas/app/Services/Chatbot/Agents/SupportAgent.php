<?php

namespace App\Services\Chatbot\Agents;

use App\Models\Chatbot;
use App\Models\Conversation;
use App\Services\KnowledgeBaseService;

class SupportAgent extends BaseAgent
{
    protected KnowledgeBaseService $kbService;

    public function __construct(KnowledgeBaseService $kbService)
    {
        $this->kbService = $kbService;
    }

    public function handle(Conversation $conversation, Chatbot $chatbot, string $userMessage, array $memory): string
    {
        // RAG específico para suporte (FAQ e serviços)
        $context = $this->kbService->searchRelevantContext($conversation->company_id, $userMessage);

        $systemPrompt = "Você é o Agente de Suporte e Atendimento. Seu objetivo é tirar dúvidas gerais do cliente.\n\n";
        $systemPrompt .= "BASE DE CONHECIMENTO:\n".$context."\n\n";
        $systemPrompt .= 'REGRA CRÍTICA: Use estritamente o contexto acima. Se não souber, diga que não sabe.';

        return $this->generateGeminiResponse($systemPrompt, $memory, $userMessage);
    }
}
