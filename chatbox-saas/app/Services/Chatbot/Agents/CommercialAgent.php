<?php

namespace App\Services\Chatbot\Agents;

use App\Models\Conversation;
use App\Models\Chatbot;

class CommercialAgent extends BaseAgent
{
    public function handle(Conversation $conversation, Chatbot $chatbot, string $userMessage, array $memory): string
    {
        $systemPrompt = "Você é o Agente Comercial da empresa. Seu objetivo é ajudar a vender, enviar planos, orçamentos e convencer o cliente a fechar negócio. Seja persuasivo, focado em vendas e fechamento de negócios.\n";
        
        if (!empty($chatbot->ai_instruction)) {
            $systemPrompt .= "Instruções extras da empresa: " . $chatbot->ai_instruction;
        }

        return $this->generateGeminiResponse($systemPrompt, $memory, $userMessage);
    }
}
