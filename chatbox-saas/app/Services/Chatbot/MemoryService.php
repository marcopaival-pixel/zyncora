<?php

namespace App\Services\Chatbot;

use App\Models\Conversation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MemoryService
{
    /**
     * Retorna a memória de curto prazo (últimas mensagens).
     */
    public function getShortMemory(Conversation $conversation, int $limit = 6): array
    {
        $historyMessages = $conversation->messages()->latest()->limit($limit)->get()->reverse();
        
        $contents = [];
        if (!empty($conversation->ai_summary)) {
            // Injeta o resumo da memória longa se existir
            $contents[] = [
                'role' => 'user',
                'parts' => [['text' => "RESUMO DO HISTÓRICO ANTERIOR: " . $conversation->ai_summary]],
            ];
            // O modelo entende que isso é contexto
        }

        foreach ($historyMessages as $msg) {
            $contents[] = [
                'role' => $msg->sender_type === 'bot' ? 'model' : 'user',
                'parts' => [['text' => $msg->body]],
            ];
        }

        return $contents;
    }

    /**
     * Sumariza conversas longas para economizar tokens.
     * Deve ser chamado via Job em background a cada N mensagens.
     */
    public function summarizeMemory(Conversation $conversation): void
    {
        $messagesCount = $conversation->messages()->count();
        
        // Só sumariza se houver mais de 10 mensagens
        if ($messagesCount < 10) return;

        $messagesToSummarize = $conversation->messages()->oldest()->limit($messagesCount - 5)->get();
        $textToAnalyze = $messagesToSummarize->map(fn ($m) => "{$m->sender_type}: {$m->body}")->implode("\n");
        
        $apiKey = config('chatbox.ai.gemini.api_key');
        if (!$apiKey) return;

        try {
            $prompt = "Resuma o seguinte histórico de atendimento em no máximo 2 frases curtas, mantendo as informações mais críticas (nome, problema, serviços solicitados, estado da negociação):\n\n{$textToAnalyze}";

            $response = Http::timeout(10)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                ]);

            if ($response->successful()) {
                $summary = trim($response->json('candidates.0.content.parts.0.text'));
                $conversation->update(['ai_summary' => $summary]);
            }
        } catch (\Exception $e) {
            Log::error('MemorySummarizationError', ['message' => $e->getMessage()]);
        }
    }
}
