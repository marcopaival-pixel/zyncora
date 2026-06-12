<?php

namespace App\Services;

use App\Models\Chatbot;
use App\Models\Conversation;
use App\Models\KnowledgeSource;
use App\Models\KnowledgeBase;
use App\Models\Company;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\AiAnswerAuditLog;

class KnowledgeOrchestratorService
{

    /**
     * Resolve a mensagem do usuário tentando usar as Fontes de Conhecimento ativas
     * na ordem correta, antes de recorrer ao LLM puro.
     */
    public function resolveAnswer(Chatbot $chatbot, Conversation $conversation, string $userMessage): string
    {
        $companyId = $chatbot->company_id;
        $company = $chatbot->company;

        // 1. Dados da Empresa (Nível 1)
        if ($this->isSourceActive($companyId, 'company_data')) {
            $companyAnswer = $this->tryCompanyData($company, $userMessage);
            if ($companyAnswer) {
                $this->logAudit($conversation->id, $userMessage, 'company_data', 50);
                return $companyAnswer;
            }
        }

        // 2. FAQ (Nível 2)
        if ($this->isSourceActive($companyId, 'faq')) {
            $faqAnswer = $this->tryFaq($companyId, $userMessage);
            if ($faqAnswer) {
                $this->logAudit($conversation->id, $userMessage, 'faq', 100);
                return $faqAnswer;
            }
        }

        // 3. API Externa (Nível 6)
        if ($this->isSourceActive($companyId, 'external_api')) {
            $apiAnswer = $this->tryExternalApi($companyId, $userMessage);
            if ($apiAnswer) {
                $this->logAudit($conversation->id, $userMessage, 'external_api', 200);
                return $apiAnswer;
            }
        }

        // 4. Se não resolveu diretamente, retorna null para que os Agentes assumam.
        return null;
    }

    private function isSourceActive(int $companyId, string $sourceType): bool
    {
        $source = KnowledgeSource::where('company_id', $companyId)
            ->where('source_type', $sourceType)
            ->first();

        return $source ? $source->is_active : false;
    }

    private function tryCompanyData(Company $company, string $userMessage): ?string
    {
        $msg = mb_strtolower($userMessage);

        // Simple Keyword Matching for Company Data (Zero tokens)
        if (str_contains($msg, 'horário') || str_contains($msg, 'funcionamento')) {
            if ($company->business_hours) {
                $hoursText = "Nosso horário de funcionamento é:\n";
                foreach ($company->business_hours as $day => $hours) {
                    if ($hours['active'] ?? false) {
                        $hoursText .= ucfirst($day) . ": " . ($hours['start'] ?? '') . " às " . ($hours['end'] ?? '') . "\n";
                    }
                }
                return rtrim($hoursText);
            }
        }

        if (str_contains($msg, 'telefone') || str_contains($msg, 'contato')) {
            if ($company->phone) {
                return "Você pode entrar em contato conosco pelo telefone/WhatsApp: " . $company->phone;
            }
        }

        if (str_contains($msg, 'onde') || str_contains($msg, 'endereço') || str_contains($msg, 'local')) {
            // Assuming we might add address to company later, or if there's a welcome_message
            if (str_contains(mb_strtolower($company->welcome_message ?? ''), 'endereço')) {
                return $company->welcome_message;
            }
        }

        return null;
    }

    private function tryFaq(int $companyId, string $userMessage): ?string
    {
        $cleanMessage = mb_strtolower(trim($userMessage));
        if (empty($cleanMessage)) return null;

        // Tenta Match exato de 100% primeiro (Custo zero de IA)
        $exactFaq = KnowledgeBase::where('company_id', $companyId)
            ->where('is_active', true)
            ->where('category', 'FAQ')
            ->where('title', 'like', $cleanMessage)
            ->first();

        if ($exactFaq) {
            return strip_tags($exactFaq->content);
        }

        // True AI RAG (Vector Search)
        try {
            $aiService = app(\App\Services\AiService::class);
            $userEmbedding = $aiService->generateEmbeddings($cleanMessage);

            if (!$userEmbedding) return null;

            $faqs = KnowledgeBase::where('company_id', $companyId)
                ->where('is_active', true)
                ->where('category', 'FAQ')
                ->whereNotNull('embedding')
                ->get();

            $bestMatch = null;
            $highestSimilarity = -1;

            foreach ($faqs as $faq) {
                if (is_array($faq->embedding)) {
                    $similarity = $this->cosineSimilarity($userEmbedding, $faq->embedding);
                    if ($similarity > $highestSimilarity) {
                        $highestSimilarity = $similarity;
                        $bestMatch = $faq;
                    }
                }
            }

            // Limiar de Confiança: 0.75 (75% de similaridade semântica)
            if ($bestMatch && $highestSimilarity >= 0.75) {
                \Illuminate\Support\Facades\Log::info("Vector Search FAQ: Encontrou match com similaridade {$highestSimilarity} para a pergunta: {$cleanMessage}");
                return strip_tags($bestMatch->content);
            }

            // Não encontrou ou confiança muito baixa: Registrar na tabela de Dúvidas Sem Resposta
            $this->logUnresolvedQuestion($companyId, $userMessage);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Erro no Vector Search RAG: " . $e->getMessage());
        }

        return null;
    }

    private function logUnresolvedQuestion(int $companyId, string $question): void
    {
        $cleanQuestion = trim($question);
        if (empty($cleanQuestion) || mb_strlen($cleanQuestion) < 10) return; // Ignora saudações curtas

        // Busca se existe uma pergunta muito parecida (Neste caso, faremos match simples para performance)
        // Para escalar, poderíamos usar vector search aqui também, mas LIKE resolve repetições exatas.
        $existing = \App\Models\UnresolvedQuestion::where('company_id', $companyId)
            ->where('question', $cleanQuestion)
            ->first();

        if ($existing) {
            $existing->increment('frequency');
            $existing->touch();
        } else {
            \App\Models\UnresolvedQuestion::create([
                'company_id' => $companyId,
                'question' => $cleanQuestion,
                'frequency' => 1,
                'status' => 'pending'
            ]);
        }
    }

    /**
     * Calcula a similaridade de cosseno entre dois vetores.
     */
    private function cosineSimilarity(array $vec1, array $vec2): float
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

    private function tryExternalApi(int $companyId, string $userMessage): ?string
    {
        $source = KnowledgeSource::where('company_id', $companyId)
            ->where('source_type', 'external_api')
            ->first();

        if (!$source || !$source->config || empty($source->config['url'])) {
            return null;
        }

        // Isso é um Action (Function Calling lite).
        // Se a mensagem contém palavras chaves de rastreio/status de pedido.
        if (str_contains(mb_strtolower($userMessage), 'pedido') || str_contains(mb_strtolower($userMessage), 'status')) {
            try {
                $response = Http::withHeaders($source->config['headers'] ?? [])
                    ->timeout(5)
                    ->get($source->config['url'], [
                        'query' => $userMessage
                    ]);

                if ($response->successful() && $response->json('answer')) {
                    return $response->json('answer');
                }
            } catch (\Exception $e) {
                Log::error("External API Knowledge Source failed", ['error' => $e->getMessage()]);
            }
        }

        return null;
    }

    private function logAudit(int $conversationId, string $userMessage, string $sourceUsed, int $tokensSaved)
    {
        AiAnswerAuditLog::create([
            'conversation_id' => $conversationId,
            'user_message' => $userMessage,
            'source_used' => $sourceUsed,
            'tokens_saved_estimated' => $tokensSaved
        ]);
    }
}
