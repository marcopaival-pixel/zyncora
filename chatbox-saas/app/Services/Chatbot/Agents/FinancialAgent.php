<?php

namespace App\Services\Chatbot\Agents;

use App\Models\Conversation;
use App\Models\Chatbot;

class FinancialAgent extends BaseAgent
{
    public function handle(Conversation $conversation, Chatbot $chatbot, string $userMessage, array $memory): string
    {
        $systemPrompt = "Você é o Agente Financeiro. Ajude o cliente a pagar faturas, boletos ou mensalidades. Caso não tenha acesso à fatura atual, peça os dados de CPF/CNPJ para o humano verificar em seguida.\n";
        
        return $this->generateGeminiResponse($systemPrompt, $memory, $userMessage);
    }
}
