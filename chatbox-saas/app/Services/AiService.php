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
     * Gera uma resposta automática baseada na instrução do bot, base de conhecimento e histórico.
     */
    public function generateResponse(Chatbot $chatbot, Conversation $conversation, string $userMessage): string
    {
        if (! $chatbot->use_ai) {
            return 'Desculpe, o assistente de IA não está ativo.';
        }

        $apiKey = $this->apiKey();
        if (! $apiKey) {
            return 'Sistema em modo de simulação (Chave Gemini ausente).';
        }

        $company = $conversation->company;
        if ($company) {
            $limit = $company->plan?->max_ai_conversations ?? 0;
            $used = $company->ai_conversations_used ?? 0;
            $extraBalance = $company->ai_credits_balance ?? 0;

            if ($used >= $limit && $extraBalance <= 0) {
                $action = $company->ai_limit_action ?? 'block';
                if ($action === 'block' || $action === 'human_only') {
                    // TODO: notificar admin sobre limite atingido (se ainda não notificado)
                    return 'Desculpe, o limite de atendimento automático desta empresa foi atingido. Em breve um atendente humano continuará o atendimento.';
                }
            }
        }

        // Busca contexto na base de conhecimento (True AI RAG - Vector Search)
        $userEmbedding = $this->generateEmbeddings($userMessage);
        $contextSnippets = collect();

        $query = KnowledgeBase::where('company_id', $chatbot->company_id)
            ->where('is_active', true);

        if ($userEmbedding) {
            $allKb = (clone $query)->whereNotNull('embedding')->get();
            $scoredKb = [];

            foreach ($allKb as $kb) {
                if (is_array($kb->embedding)) {
                    $similarity = $this->cosineSimilarity($userEmbedding, $kb->embedding);
                    // Apenas snippets com similaridade razoável (>0.50)
                    if ($similarity > 0.50) {
                        $scoredKb[] = ['kb' => $kb, 'score' => $similarity];
                    }
                }
            }

            usort($scoredKb, fn($a, $b) => $b['score'] <=> $a['score']);
            
            // Pega os top 5 mais relevantes
            $topMatches = array_slice($scoredKb, 0, 5);
            foreach ($topMatches as $match) {
                $contextSnippets->push($match['kb']);
            }
        }

        // Fallback genérico se a busca vetorial falhar ou não achar nada
        if ($contextSnippets->isEmpty()) {
            $contextSnippets = $query->latest()->limit(3)->get();
        }

        $context = $contextSnippets->map(fn ($kb) => "Tópico: {$kb->title}\nConteúdo: {$kb->content}")
            ->implode("\n---\n");

        $mainPrompt = $chatbot->ai_instruction ?? 'Você é um assistente virtual prestativo.';
        $systemInstruction = "Instrução Principal: {$mainPrompt}\n\nContexto de Conhecimento Obrigatório:\n{$context}\n\nREGRA CRÍTICA (ANTI-ALUCINAÇÃO): Use ESTRITAMENTE o contexto de conhecimento fornecido acima para responder. Caso a informação não exista no contexto ou você não saiba responder, diga educadamente que não tem essa informação no momento e que um humano continuará o atendimento. NÃO INVENTE informações.";

        // Histórico da conversa (memória de curto prazo)
        $historyMessages = $conversation->messages()->latest()->limit(6)->get()->reverse();
        
        $contents = [];
        foreach ($historyMessages as $msg) {
            $contents[] = [
                'role' => $msg->sender_type === 'bot' ? 'model' : 'user',
                'parts' => [['text' => $msg->body]],
            ];
        }
        
        // Mensagem atual
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $userMessage]],
        ];

        // Roteamento Inteligente (Fase 3 da Auditoria)
        // Se a intenção de compra é alta (>60) ou se o cliente está frustrado ('negative'),
        // enviamos para o modelo 'Pro' que tem melhor raciocínio. Senão, usamos 'Flash' (mais rápido e barato).
        $isComplex = $conversation->ai_score >= 60 || $conversation->ai_sentiment === 'negative';
        $model = $isComplex ? 'gemini-1.5-pro' : 'gemini-1.5-flash';

        try {
            $response = Http::timeout($this->timeout())
                ->retry($this->retryTimes(), $this->retrySleep())
                ->post($this->endpoint($apiKey, $model), [
                    'system_instruction' => [
                        'parts' => [
                            ['text' => $systemInstruction],
                        ],
                    ],
                    'contents' => $contents,
                ]);

            if ($response->failed()) {
                Log::warning('gemini_api_failed', ['status' => $response->status(), 'body' => $response->body()]);

                return 'Não consegui gerar uma resposta no momento.';
            }

            // Débito de créditos após sucesso da IA
            if (isset($company) && $company) {
                $limit = $company->plan?->max_ai_conversations ?? 0;
                $used = $company->ai_conversations_used ?? 0;
                
                if ($used < $limit) {
                    $company->increment('ai_conversations_used');
                } else {
                    if ($company->ai_credits_balance > 0) {
                        $company->decrement('ai_credits_balance');
                        $company->increment('ai_credits_used'); // Histórico de extras usados
                    } else {
                        // Limite estourou e não há extras
                        $company->increment('ai_conversations_used'); // Incrementa para registrar o estouro
                    }
                }
            }

            $this->logUsage($conversation, $response, $model);

            $textAnswer = $response->json('candidates.0.content.parts.0.text') ?? 'Não consegui gerar uma resposta no momento.';

            \App\Models\AiAnswerAuditLog::create([
                'conversation_id' => $conversation->id,
                'user_message' => $userMessage,
                'source_used' => 'llm_generative',
                'tokens_saved_estimated' => 0
            ]);

            return $textAnswer;

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
        $messages = $conversation->messages()->latest()->limit(8)->get();
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

            $model = 'gemini-1.5-flash';
            $response = Http::timeout($this->timeout())
                ->retry($this->retryTimes(), $this->retrySleep())
                ->post($this->endpoint($apiKey, $model), [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                ]);

            if ($response->failed()) {
                Log::warning('gemini_analysis_failed', ['status' => $response->status()]);
                $this->fallbackSimulation($conversation, $textToAnalyze);

                return;
            }

            $this->logUsage($conversation, $response, $model);

            $jsonText = $response->json('candidates.0.content.parts.0.text');
            $data = json_decode(trim(str_replace(['```json', '```'], '', $jsonText)), true);

            if ($data) {
                $score = $data['score'] ?? 0;
                $summary = $data['summary'] ?? 'Resumo indisponível.';
                
                $conversation->update([
                    'ai_score' => $score,
                    'ai_sentiment' => $data['sentiment'] ?? 'neutral',
                    'ai_summary' => $summary,
                ]);

                if ($score >= 70) {
                    $this->createOpportunityIfMissing($conversation, $score, $summary);
                }
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

            $model = 'gemini-1.5-pro';
            $response = Http::timeout($this->timeout())
                ->retry($this->retryTimes(), $this->retrySleep())
                ->post($this->endpoint($apiKey, $model), [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                ]);

            if ($response->failed()) {
                return "Não foi possível gerar a sugestão.";
            }

            $this->logUsage($conversation, $response, $model);

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

        if ($score >= 70) {
            $this->createOpportunityIfMissing($conversation, $score, $summary);
        }
    }

    protected function createOpportunityIfMissing(Conversation $conversation, int $score, string $summary): void
    {
        // Verifica se já existe oportunidade para esta conversa (ou do mesmo lead se identificável)
        // Por hora, apenas 1 oportunidade por conversa.
        $exists = \App\Models\Opportunity::where('conversation_id', $conversation->id)->exists();

        if (!$exists) {
            \App\Models\Opportunity::create([
                'company_id' => $conversation->company_id,
                'conversation_id' => $conversation->id,
                'lead_name' => $conversation->customer_name ?? 'Lead Anônimo',
                'status' => 'new',
                'ai_score' => $score,
                'summary' => "Identificado via IA ({$score}% Intenção): {$summary}",
            ]);
        }
    }

    private function endpoint(string $apiKey, string $model = 'gemini-1.5-flash'): string
    {
        return "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
    }

    /**
     * Gera embeddings via Google Gemini API para Vector Search.
     */
    public function generateEmbeddings(string $text): ?array
    {
        $apiKey = $this->apiKey();
        if (!$apiKey) {
            // Em modo simulado, geramos um vetor randômico falso de 768 dimensões para testes
            $fakeVector = [];
            for ($i = 0; $i < 768; $i++) {
                $fakeVector[] = mt_rand(-100, 100) / 1000;
            }
            return $fakeVector;
        }

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/text-embedding-004:embedContent?key={$apiKey}";
            
            $response = Http::timeout($this->timeout())
                ->retry($this->retryTimes(), $this->retrySleep())
                ->post($url, [
                    'model' => 'models/text-embedding-004',
                    'content' => [
                        'parts' => [['text' => $text]]
                    ]
                ]);

            if ($response->successful()) {
                return $response->json('embedding.values');
            }
            
            Log::warning('gemini_embedding_failed', ['status' => $response->status()]);
            return null;
        } catch (\Exception $e) {
            Log::error('gemini_embedding_error', ['message' => $e->getMessage()]);
            return null;
        }
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

    protected function logUsage(Conversation $conversation, \Illuminate\Http\Client\Response $response, string $model): void
    {
        $usage = $response->json('usageMetadata');
        if (!$usage) return;

        $promptTokens = $usage['promptTokenCount'] ?? 0;
        $completionTokens = $usage['candidatesTokenCount'] ?? 0;
        $totalTokens = $usage['totalTokenCount'] ?? 0;

        // Estimativa de custo Gemini: 
        // Flash: $0.075 / 1M prompt, $0.30 / 1M completion
        // Pro: $3.50 / 1M prompt, $10.50 / 1M completion
        if (str_contains($model, 'pro')) {
            $cost = ($promptTokens / 1000000) * 3.50 + ($completionTokens / 1000000) * 10.50;
        } else {
            $cost = ($promptTokens / 1000000) * 0.075 + ($completionTokens / 1000000) * 0.30;
        }

        \App\Models\AiUsageLog::create([
            'company_id' => $conversation->company_id,
            'model_used' => $model,
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
            'total_tokens' => $totalTokens,
            'estimated_cost' => $cost,
        ]);
    }

    /**
     * Calcula a similaridade de cosseno entre dois vetores.
     */
    public function cosineSimilarity(array $vec1, array $vec2): float
    {
        $dotProduct = 0;
        $normA = 0;
        $normB = 0;

        $count = min(count($vec1), count($vec2));
        for ($i = 0; $i < $count; $i++) {
            $dotProduct += $vec1[$i] * $vec2[$i];
            $normA += pow($vec1[$i], 2);
            $normB += pow($vec2[$i], 2);
        }

        if ($normA == 0 || $normB == 0) return 0;

        return $dotProduct / (sqrt($normA) * sqrt($normB));
    }
}
