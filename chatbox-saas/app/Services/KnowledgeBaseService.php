<?php

namespace App\Services;

use App\Models\KnowledgeBase;
use App\Models\Chatbot;
use Illuminate\Support\Facades\Log;

class KnowledgeBaseService
{
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
