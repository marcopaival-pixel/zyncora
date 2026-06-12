<?php

namespace App\Services;

use App\Models\HelpArticle;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HelpAssistantService
{
    public function askQuestion(string $question): string
    {
        $apiKey = config('chatbox.ai.gemini.api_key');
        
        if (! $apiKey) {
            return 'O assistente de IA está indisponível no momento (Chave não configurada).';
        }

        $cleanMessage = preg_replace('/[^\p{L}\p{N}\s]/u', '', mb_strtolower($question));
        $words = array_filter(explode(' ', $cleanMessage), fn($w) => mb_strlen($w) > 3);
        
        $query = HelpArticle::where('is_active', true);
        
        if (!empty($words)) {
            $query->where(function ($q) use ($words) {
                foreach (array_slice($words, 0, 5) as $word) {
                    $q->orWhere('title', 'like', "%{$word}%")
                      ->orWhere('content', 'like', "%{$word}%");
                }
            });
        }
        
        $articles = $query->limit(5)->get();
        
        // Fallback genérico se nada for encontrado
        if ($articles->isEmpty()) {
            $articles = HelpArticle::where('is_active', true)->limit(3)->get();
        }
        
        $context = $articles->map(function($article) {
            return "Artigo: {$article->title}\nDescrição: {$article->description}\nConteúdo: " . strip_tags($article->content);
        })->implode("\n\n");

        $systemInstruction = "Você é o Assistente de Ajuda da plataforma. Seu objetivo é ajudar o usuário a usar a plataforma. Use o contexto de artigos abaixo para responder. Se a resposta não estiver no contexto, responda de forma geral sobre sistemas ou diga que o suporte deve ser contatado. Responda em português do Brasil, de forma clara e amigável. Não invente funcionalidades que não estão no contexto.\n\nContexto dos Artigos:\n{$context}";

        try {
            $response = Http::timeout(15)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                    'system_instruction' => [
                        'parts' => [
                            ['text' => $systemInstruction],
                        ],
                    ],
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [['text' => $question]],
                        ]
                    ],
                ]);

            if ($response->failed()) {
                Log::warning('help_assistant_failed', ['status' => $response->status()]);
                return 'Desculpe, não consegui processar sua pergunta agora.';
            }

            return $response->json('candidates.0.content.parts.0.text') ?? 'Não consegui formular uma resposta.';
        } catch (\Exception $e) {
            Log::error('help_assistant_error', ['message' => $e->getMessage()]);
            return 'Ocorreu um erro no assistente de IA.';
        }
    }
}
