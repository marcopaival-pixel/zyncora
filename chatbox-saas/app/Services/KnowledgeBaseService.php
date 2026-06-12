<?php

namespace App\Services;

use App\Models\KnowledgeBase;
use App\Models\Chatbot;
use Illuminate\Support\Facades\Log;

class KnowledgeBaseService
{
    /**
     * Busca contexto relevante garantindo isolamento por tenant.
     */
    public function searchRelevantContext(int $companyId, string $query): string
    {
        // Simple search for now. In a real scenario, use Vector Search (Pinecone, pgvector, etc.)
        $cleanMessage = preg_replace('/[^\p{L}\p{N}\s]/u', '', mb_strtolower($query));
        $words = array_filter(explode(' ', $cleanMessage), fn($w) => mb_strlen($w) > 3);

        $snippetsQuery = KnowledgeBase::where('company_id', $companyId)->where('is_active', true);

        if (!empty($words)) {
            $snippetsQuery->where(function ($q) use ($words) {
                foreach (array_slice($words, 0, 8) as $word) {
                    $q->orWhere('content', 'like', "%{$word}%")
                      ->orWhere('title', 'like', "%{$word}%");
                }
            });
        }

        $snippets = $snippetsQuery->limit(5)->get();

        if ($snippets->isEmpty()) {
            return "Nenhuma informação extra disponível.";
        }

        $context = "";
        foreach ($snippets as $snippet) {
            $context .= "### " . $snippet->title . "\n" . strip_tags($snippet->content) . "\n\n";
        }

        return $context;
    }

    /**
     * Search for context in the knowledge base and return a formatted string.
     */
    public function getContextForChatbot(Chatbot $chatbot, string $query): string
    {
        // Simple search for now. In a real scenario, use Vector Search (Pinecone, pgvector, etc.)
        $snippets = KnowledgeBase::where('company_id', $chatbot->company_id)
            ->where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%");
            })
            ->limit(3)
            ->get();

        if ($snippets->isEmpty()) {
            return "Não foram encontradas informações específicas na base de conhecimento.";
        }

        $context = "Informações relevantes da Base de Conhecimento:\n\n";
        foreach ($snippets as $snippet) {
            $context .= "### " . $snippet->title . "\n";
            $context .= strip_tags($snippet->content) . "\n\n";
        }

        return $context;
    }

    /**
     * Generate a response using AI (Mock for now, ready for OpenAI/Gemini integration).
     */
    public function generateAiResponse(Chatbot $chatbot, string $userMessage): string
    {
        if (!$chatbot->use_ai) {
            return "AI is disabled for this chatbot.";
        }

        $context = $this->getContextForChatbot($chatbot, $userMessage);
        $systemInstruction = $chatbot->ai_instruction ?? "Você é um assistente prestativo.";

        Log::info("AI Chatbot Request", [
            'chatbot_id' => $chatbot->id,
            'message' => $userMessage,
            'context_length' => strlen($context)
        ]);

        // MOCK LOGIC: In production, call OpenAI/Gemini API here.
        // For demonstration, we'll return a simulation.
        
        if (str_contains(strtolower($userMessage), 'preço') || str_contains(strtolower($userMessage), 'valor')) {
             return "Com base na nossa base de conhecimento, temos diversas opções. " . 
                    (str_contains($context, 'Preços') ? "Vi aqui que: " . substr($context, 0, 100) . "..." : "Poderia ser mais específico sobre qual produto deseja saber o preço?");
        }

        return "Olá! Sou o assistente inteligente da " . $chatbot->company->name . ". " . 
               "Recebi sua mensagem: '{$userMessage}'. Estou analisando nossa base de dados para te dar a melhor resposta.";
    }
}
