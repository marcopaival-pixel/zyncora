<?php

namespace App\Services\Chatbot;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IntentClassificationService
{
    /**
     * Intenções suportadas nativamente pelo sistema.
     */
    const INTENT_COMMERCIAL = 'commercial';

    const INTENT_SCHEDULING = 'scheduling';

    const INTENT_SUPPORT = 'support';

    const INTENT_FINANCIAL = 'financial';

    const INTENT_HUMAN_TRANSFER = 'human_transfer';

    const INTENT_SIMPLE_FAQ = 'simple_faq';

    const INTENT_UNKNOWN = 'unknown';

    /**
     * Classifica a intenção de uma mensagem.
     * Primeiro tenta classificação rápida (regex/keywords), se não conseguir,
     * tenta via modelo de IA menor (Flash).
     */
    public function classifyIntent(string $message): string
    {
        $intent = $this->fastRegexClassification($message);

        if ($intent !== self::INTENT_UNKNOWN) {
            return $intent;
        }

        return $this->aiClassification($message);
    }

    /**
     * Regras de negócio determinísticas para classificação rápida sem custo de IA.
     */
    protected function fastRegexClassification(string $message): string
    {
        $message = mb_strtolower($message);

        $commercialKeywords = ['comprar', 'preço', 'orçamento', 'plano', 'valor', 'custa', 'venda', 'assinar'];
        $schedulingKeywords = ['agendar', 'marcar', 'horário', 'consulta', 'reserva', 'cancelar consulta', 'remarcar'];
        $financialKeywords = ['boleto', 'fatura', 'pagar', 'pagamento', 'cobrança', 'segunda via'];
        $humanKeywords = ['falar com humano', 'atendente', 'humano', 'suporte', 'reclamação', 'reclamar'];

        if ($this->containsAny($message, $humanKeywords)) {
            return self::INTENT_HUMAN_TRANSFER;
        }
        if ($this->containsAny($message, $financialKeywords)) {
            return self::INTENT_FINANCIAL;
        }
        if ($this->containsAny($message, $schedulingKeywords)) {
            return self::INTENT_SCHEDULING;
        }
        if ($this->containsAny($message, $commercialKeywords)) {
            return self::INTENT_COMMERCIAL;
        }

        return self::INTENT_UNKNOWN;
    }

    protected function containsAny(string $text, array $keywords): bool
    {
        foreach ($keywords as $word) {
            if (str_contains($text, $word)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Usa o Gemini Flash para classificar a intenção se o Regex falhar.
     */
    protected function aiClassification(string $message): string
    {
        $apiKey = config('chatbox.ai.gemini.api_key');

        if (! $apiKey) {
            return self::INTENT_SUPPORT; // Fallback
        }

        $prompt = "Classifique a seguinte mensagem do usuário em UMA destas categorias exatas: 'commercial', 'scheduling', 'support', 'financial', 'human_transfer', 'simple_faq', 'unknown'. Retorne APENAS a string da categoria e nada mais. Mensagem: \"{$message}\"";

        try {
            $response = Http::timeout(5)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                ]);

            if ($response->successful()) {
                $intent = trim(strtolower($response->json('candidates.0.content.parts.0.text')));

                $validIntents = [
                    self::INTENT_COMMERCIAL, self::INTENT_SCHEDULING, self::INTENT_SUPPORT,
                    self::INTENT_FINANCIAL, self::INTENT_HUMAN_TRANSFER, self::INTENT_SIMPLE_FAQ, self::INTENT_UNKNOWN,
                ];

                if (in_array($intent, $validIntents)) {
                    return $intent;
                }
            }
        } catch (\Exception $e) {
            Log::error('IntentClassificationError', ['message' => $e->getMessage()]);
        }

        return self::INTENT_SUPPORT; // Default
    }
}
