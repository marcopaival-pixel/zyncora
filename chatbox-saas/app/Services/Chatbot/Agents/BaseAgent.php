<?php

namespace App\Services\Chatbot\Agents;

use App\Models\Chatbot;
use App\Models\Conversation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class BaseAgent
{
    /**
     * Processa a mensagem e retorna a resposta do agente.
     */
    abstract public function handle(Conversation $conversation, Chatbot $chatbot, string $userMessage, array $memory): string;

    /**
     * Roda o modelo do Gemini.
     */
    protected function generateGeminiResponse(string $systemPrompt, array $memory, string $userMessage): string
    {
        $apiKey = config('chatbox.ai.gemini.api_key');
        if (! $apiKey) {
            return 'Modo de simulação ativado: '.static::class;
        }

        $contents = $memory; // Histórico
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $userMessage]],
        ];

        try {
            $response = Http::timeout(15)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                    'system_instruction' => [
                        'parts' => [
                            ['text' => $systemPrompt],
                        ],
                    ],
                    'contents' => $contents,
                ]);

            if ($response->successful()) {
                return $response->json('candidates.0.content.parts.0.text') ?? 'Não consegui processar a resposta.';
            }

            Log::warning('GeminiAgentFailed', ['body' => $response->body()]);
        } catch (\Exception $e) {
            Log::error('GeminiAgentException', ['msg' => $e->getMessage()]);
        }

        return 'Desculpe, ocorreu um erro ao gerar a resposta no momento.';
    }
}
