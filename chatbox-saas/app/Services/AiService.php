<?php

namespace App\Services;

use App\Models\Chatbot;
use App\Models\Conversation;
use App\Models\KnowledgeBase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    /**
     * Gera uma resposta automática baseada na instrução do bot e na base de conhecimento.
     */
    public function generateResponse(Chatbot $chatbot, string $userMessage): string
    {
        if (! $chatbot->use_ai) {
            return 'Desculpe, o assistente de IA não está ativo.';
        }

        $apiKey = $this->apiKey();
        if (! $apiKey) {
            return 'Sistema em modo de simulação (Chave Gemini ausente).';
        }

        // Busca contexto na base de conhecimento (Otimizado: RAG Lite)
        // Em vez de carregar TUDO, buscamos apenas trechos relevantes ou os mais recentes
        $query = KnowledgeBase::where('company_id', $chatbot->company_id)
            ->where('is_active', true);

        $contextSnippets = (clone $query)
            ->where(function ($q) use ($userMessage) {
                // Normalização básica: remove pontuação e extrai palavras significativas
                $cleanMessage = preg_replace('/[^\p{L}\p{N}\s]/u', '', mb_strtolower($userMessage));
                $words = array_filter(explode(' ', $cleanMessage), fn($w) => mb_strlen($w) > 3);
                
                foreach (array_slice($words, 0, 8) as $word) {
                    $q->orWhere('content', 'like', "%{$word}%")
                      ->orWhere('title', 'like', "%{$word}%");
                }
            })
            ->limit(5)
            ->get();

        // Se não encontrar nada específico, pega os 3 mais genéricos/recentes como fallback
        if ($contextSnippets->isEmpty()) {
            $contextSnippets = $query->latest()->limit(3)->get();
        }

        $context = $contextSnippets->map(fn ($kb) => "Tópico: {$kb->title}\nConteúdo: {$kb->content}")
            ->implode("\n---\n");

        $mainPrompt = $chatbot->ai_instruction ?? 'Você é um assistente virtual prestativo.';

        try {
            $response = Http::timeout($this->timeout())
                ->retry($this->retryTimes(), $this->retrySleep())
                ->post($this->endpoint($apiKey), [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => "Instrução: {$mainPrompt}\nContexto de Conhecimento: {$context}\nPergunta do Usuário: {$userMessage}\nResponda de forma natural e útil."],
                            ],
                        ],
                    ],
                ]);

            if ($response->failed()) {
                Log::warning('gemini_api_failed', ['status' => $response->status()]);

                return 'Não consegui gerar uma resposta no momento.';
            }

            return $response->json('candidates.0.content.parts.0.text') ?? 'Não consegui gerar uma resposta no momento.';

        } catch (\Exception $e) {
            Log::error('gemini_api_error', ['message' => $e->getMessage()]);

            return 'Ocorreu um erro ao processar sua solicitação com a IA.';
        }
    }

    /**
     * Analisa uma conversa para determinar score de lead, sentimento e resumo via IA Real.
     */
    public function analyzeConversation(Conversation $conversation): void
    {
        $messages = $conversation->messages()->latest()->limit(15)->get();
        if ($messages->isEmpty()) {
            return;
        }

        $textToAnalyze = $messages->reverse()->map(fn ($m) => "{$m->sender_type}: {$m->body}")->implode("\n");
        $apiKey = $this->apiKey();

        if (! $apiKey) {
            $this->fallbackSimulation($conversation, $textToAnalyze);

            return;
        }

        try {
            $prompt = "Analise o seguinte histórico de chat de atendimento:\n\n{$textToAnalyze}\n\n".
                      "Retorne APENAS um JSON puro (sem markdown) no seguinte formato:\n".
                      "{\"score\": 0-100, \"sentiment\": \"positive|negative|neutral\", \"summary\": \"string curto\"}\n".
                      'O score deve representar a intenção de compra ou urgência.';

            $response = Http::timeout($this->timeout())
                ->retry($this->retryTimes(), $this->retrySleep())
                ->post($this->endpoint($apiKey), [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                ]);

            if ($response->failed()) {
                Log::warning('gemini_analysis_failed', ['status' => $response->status()]);
                $this->fallbackSimulation($conversation, $textToAnalyze);

                return;
            }

            $jsonText = $response->json('candidates.0.content.parts.0.text');
            $data = json_decode(trim(str_replace(['```json', '```'], '', $jsonText)), true);

            if ($data) {
                $conversation->update([
                    'ai_score' => $data['score'] ?? 0,
                    'ai_sentiment' => $data['sentiment'] ?? 'neutral',
                    'ai_summary' => $data['summary'] ?? 'Resumo indisponível.',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('gemini_analysis_error', ['message' => $e->getMessage()]);
            $this->fallbackSimulation($conversation, $textToAnalyze);
        }
    }

    /**
     * Gera um rascunho de sugestão de resposta para auxiliar o agente humano.
     */
    public function generateAgentSuggestion(Conversation $conversation): string
    {
        $messages = $conversation->messages()->latest()->limit(10)->get();
        if ($messages->isEmpty()) {
            return '';
        }

        $textToAnalyze = $messages->reverse()->map(fn ($m) => "{$m->sender_type}: {$m->body}")->implode("\n");
        $apiKey = $this->apiKey();

        if (! $apiKey) {
            return "Simulação: Olá, entendo a sua dúvida. Estamos analisando os detalhes e já te respondo com a solução correta.";
        }

        $query = KnowledgeBase::where('company_id', $conversation->company_id)->where('is_active', true);
        $contextSnippets = $query->latest()->limit(3)->get();
        $context = $contextSnippets->map(fn ($kb) => "{$kb->title}: {$kb->content}")->implode("\n");

        try {
            $prompt = "Atue como um copiloto para um atendente humano. Leia o histórico do chat abaixo e crie UMA sugestão de resposta educada, útil e direta para o atendente enviar ao cliente.\n" .
                      "Não use aspas, apenas o texto da resposta.\n\n" .
                      "Base de conhecimento:\n{$context}\n\n" .
                      "Histórico:\n{$textToAnalyze}\n\nSugestão:";

            $response = Http::timeout($this->timeout())
                ->retry($this->retryTimes(), $this->retrySleep())
                ->post($this->endpoint($apiKey), [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                ]);

            if ($response->failed()) {
                return "Não foi possível gerar a sugestão.";
            }

            return trim((string) $response->json('candidates.0.content.parts.0.text')) ?? "Não foi possível gerar a sugestão.";
        } catch (\Exception $e) {
            Log::error('gemini_agent_assist_error', ['message' => $e->getMessage()]);
            return "Erro ao processar sugestão com a IA.";
        }
    }

    protected function fallbackSimulation($conversation, $textToAnalyze)
    {
        // Lógica de simulação mantida como fallback seguro
        $score = 0;
        $sentiment = 'neutral';
        if (stripos($textToAnalyze, 'preço') !== false || stripos($textToAnalyze, 'comprar') !== false) {
            $score = 75;
            $sentiment = 'positive';
            $summary = 'Simulação: Forte intenção de compra detectada.';
        } else {
            $summary = 'Simulação: Cliente em fase de sondagem.';
        }

        $conversation->update([
            'ai_score' => $score,
            'ai_sentiment' => $sentiment,
            'ai_summary' => $summary,
        ]);
    }

    private function endpoint(string $apiKey): string
    {
        $model = (string) config('chatbox.ai.gemini.model', 'gemini-1.5-flash');

        return "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
    }

    private function apiKey(): ?string
    {
        $apiKey = config('chatbox.ai.gemini.api_key');

        return is_string($apiKey) && $apiKey !== '' ? $apiKey : null;
    }

    private function timeout(): int
    {
        return max(1, (int) config('chatbox.ai.gemini.timeout', 15));
    }

    private function retryTimes(): int
    {
        return max(0, (int) config('chatbox.ai.gemini.retry_times', 2));
    }

    private function retrySleep(): int
    {
        return max(0, (int) config('chatbox.ai.gemini.retry_sleep', 250));
    }
}
