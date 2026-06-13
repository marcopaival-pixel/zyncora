<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIGeneratorService
{
    /**
     * Generates initial setup using OpenAI (or other configured LLM)
     * Returns an array with 'initial_message', 'knowledge' array and 'flows' array.
     */
    public function generateInitialSetup(Company $company, string $segment, string $objective, array $channels): ?array
    {
        $apiKey = config('services.openai.key');
        if (empty($apiKey)) {
            Log::info('AI Generator skipped: API Key not configured.');

            return null; // Triggers fallback
        }

        $prompt = $this->buildPrompt($company, $segment, $objective, $channels);

        try {
            $response = Http::withToken($apiKey)
                ->timeout(30)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        ['role' => 'system', 'content' => 'Você é um especialista em configuração de Chatbots para empresas. Retorne apenas JSON válido.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'response_format' => ['type' => 'json_object'],
                    'temperature' => 0.7,
                ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');

                return json_decode($content, true);
            }

            Log::error('AI Generator API Error', ['response' => $response->body()]);
        } catch (\Exception $e) {
            Log::error('AI Generator Exception', ['message' => $e->getMessage()]);
        }

        return null;
    }

    private function buildPrompt(Company $company, string $segment, string $objective, array $channels): string
    {
        $channelsStr = implode(', ', $channels);

        return <<<PROMPT
Gere a configuração inicial para o chatbot da empresa "{$company->name}", que atua no segmento de "{$segment}".
O objetivo principal do chatbot é: "{$objective}".
Ele irá atuar nos canais: {$channelsStr}.

Baseado nessas informações, crie um JSON com a seguinte estrutura:
{
    "initial_message": "A mensagem de boas vindas otimizada para os canais",
    "knowledge": [
        { "title": "Título do artigo de FAQ (ex: Horários, Endereço)", "content": "Conteúdo do artigo imaginando o segmento" }
    ],
    "flows": [
        { "trigger": "Gatilho/Menu curto (ex: Agendar)", "question": "Mensagem que o bot envia ao acionar o gatilho", "answer": "Resposta subsequente ou continuação" }
    ]
}

Regras UX/UI do Chatbot:
- Seja intuitivo para usuários leigos.
- Respostas curtas e objetivas (especialmente para WhatsApp).
- Forneça no máximo 4 itens no FAQ (knowledge).
- Forneça no máximo 3 opções de fluxos (flows) alinhados com o objetivo.
PROMPT;
    }
}
