<?php

namespace App\Services\Chatbot;

use App\Models\Conversation;
use App\Models\Chatbot;

class MessageOrchestratorService
{
    protected IntentClassificationService $intentClassifier;
    protected MemoryService $memoryService;

    public function __construct(
        IntentClassificationService $intentClassifier,
        MemoryService $memoryService
    ) {
        $this->intentClassifier = $intentClassifier;
        $this->memoryService = $memoryService;
    }

    /**
     * Ponto de entrada central para todas as mensagens de entrada do Chatbot.
     */
    public function handleIncomingMessage(Conversation $conversation, Chatbot $chatbot, string $userMessage): ?string
    {
        // 0. Bloqueio de IA: Se a conversa já foi transferida para um humano, a IA deve ser silenciada
        if ($conversation->status === 'open_human') {
            return null; // Humano está atendendo, silenciar IA.
        }

        // 1. Identificar Intenção
        $intent = $this->intentClassifier->classifyIntent($userMessage);

        // 2. Tentar resolver sem IA se for intenção simples (ex: FAQ estático, comandos)
        if ($intent === IntentClassificationService::INTENT_HUMAN_TRANSFER) {
            return $this->handleHumanTransfer($conversation);
        }

        // 3. Orquestrador de Conhecimento (Tenta Fontes Rápidas: Dados da Empresa, FAQ, API)
        $knowledgeAnswer = app(\App\Services\KnowledgeOrchestratorService::class)->resolveAnswer($chatbot, $conversation, $userMessage);
        if ($knowledgeAnswer) {
            return $knowledgeAnswer;
        }

        // 3. Obter memória sumarizada/curta
        $memory = $this->memoryService->getShortMemory($conversation);

        // 4. Encaminhar para o agente correto
        return $this->routeToAgent($intent, $conversation, $chatbot, $userMessage, $memory);
    }

    protected function routeToAgent(string $intent, Conversation $conversation, Chatbot $chatbot, string $userMessage, array $memory): string
    {
        switch ($intent) {
            case IntentClassificationService::INTENT_COMMERCIAL:
                return app(\App\Services\Chatbot\Agents\CommercialAgent::class)->handle($conversation, $chatbot, $userMessage, $memory);
            
            case IntentClassificationService::INTENT_SCHEDULING:
                return app(\App\Services\Chatbot\Agents\SchedulingAgent::class)->handle($conversation, $chatbot, $userMessage, $memory);
            
            case IntentClassificationService::INTENT_FINANCIAL:
                return app(\App\Services\Chatbot\Agents\FinancialAgent::class)->handle($conversation, $chatbot, $userMessage, $memory);
                
            case IntentClassificationService::INTENT_SUPPORT:
            default:
                return app(\App\Services\Chatbot\Agents\SupportAgent::class)->handle($conversation, $chatbot, $userMessage, $memory);
        }
    }

    protected function handleHumanTransfer(Conversation $conversation): string
    {
        $conversation->update([
            'status' => 'open_human',
            'assignee_id' => null, // Opcional: pode enviar para uma fila
        ]);
        
        return "Entendi. Estou transferindo você para um de nossos atendentes humanos. Por favor, aguarde um momento.";
    }
}
