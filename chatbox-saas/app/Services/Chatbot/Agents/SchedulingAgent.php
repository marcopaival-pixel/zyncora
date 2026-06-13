<?php

namespace App\Services\Chatbot\Agents;

use App\Models\Chatbot;
use App\Models\Conversation;

class SchedulingAgent extends BaseAgent
{
    public function handle(Conversation $conversation, Chatbot $chatbot, string $userMessage, array $memory): string
    {
        $systemPrompt = "Você é o Agente de Agendamento. Seu objetivo é ajudar o cliente a marcar consultas, aulas ou reservas, verificando horários e dados necessários. Seja direto e objetivo.\n";

        if (! empty($chatbot->ai_instruction)) {
            $systemPrompt .= 'Instruções extras da empresa: '.$chatbot->ai_instruction;
        }

        return $this->generateGeminiResponse($systemPrompt, $memory, $userMessage);
    }
}
