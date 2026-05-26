<?php

namespace App\Services;

class DemoChatService
{
    protected array $knowledgeBase = [
        'planos' => 'Temos planos que se adaptam ao seu negócio! O plano Startup começa em R$ 97/mês, o Business Pro por R$ 297/mês e o Enterprise com soluções personalizadas. Todos incluem IA e integração com WhatsApp.',
        'whatsapp' => 'A integração com WhatsApp é nativa e super simples! Você conecta seu número via QR Code ou API oficial e começa a atender seus clientes instantaneamente com a nossa IA.',
        'atendente' => 'Sim! O chatbot é inteligente o suficiente para identificar quando um cliente precisa de ajuda humana e faz a transferência imediata para sua equipe, mantendo todo o histórico da conversa.',
        'treinar' => 'Com certeza! Você pode fazer upload de PDFs, documentos do Word, ou simplesmente colar o link do seu site. Nossa IA aprende tudo sobre o seu negócio em segundos.',
        'canais' => 'Suportamos WhatsApp, Instagram, Facebook Messenger, Telegram e Widget de Chat para sites. Tudo centralizado em um único painel para sua equipe.',
        'saudacao' => 'Olá! Eu sou o assistente inteligente da Zynkora. Como posso ajudar você hoje a descobrir o poder da automação?',
        'padrao' => 'Essa é uma ótima pergunta! Nossa plataforma permite que você automatize processos, reduza custos e aumente suas vendas atendendo 24/7. Quer saber mais sobre algum ponto específico?',
    ];

    public function getResponse(string $message): array
    {
        $message = mb_strtolower($message);
        $response = $this->knowledgeBase['padrao'];

        if (str_contains($message, 'plano') || str_contains($message, 'preço') || str_contains($message, 'valor')) {
            $response = $this->knowledgeBase['planos'];
        } elseif (str_contains($message, 'whatsapp') || str_contains($message, 'whats')) {
            $response = $this->knowledgeBase['whatsapp'];
        } elseif (str_contains($message, 'humano') || str_contains($message, 'atendente') || str_contains($message, 'transferir')) {
            $response = $this->knowledgeBase['atendente'];
        } elseif (str_contains($message, 'treinar') || str_contains($message, 'documento') || str_contains($message, 'pdf')) {
            $response = $this->knowledgeBase['treinar'];
        } elseif (str_contains($message, 'canal') || str_contains($message, 'onde') || str_contains($message, 'instagram')) {
            $response = $this->knowledgeBase['canais'];
        } elseif (str_contains($message, 'oi') || str_contains($message, 'olá') || str_contains($message, 'bom dia')) {
            $response = $this->knowledgeBase['saudacao'];
        }

        return [
            'text' => $response,
            'type' => 'bot',
            'timestamp' => now()->format('H:i'),
        ];
    }
}
