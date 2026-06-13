<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Contact;

class PlanUsageService
{
    /**
     * Calcula o uso e limites de todos os recursos do plano da empresa.
     */
    public function getUsageData(Company $company): array
    {
        // Limites contratados
        $maxUsers = $company->max_users ?? $company->plan?->max_users ?? 0;
        $maxAttendants = $company->max_attendants ?? $company->plan?->max_attendants ?? 0;
        $maxChannels = $company->max_channels ?? $company->plan?->max_channels ?? 0;
        $maxChatbots = $company->max_chatbots ?? $company->plan?->max_chatbots ?? 0;
        $aiConversationsLimit = $company->plan?->max_ai_conversations ?? 0;

        // Uso atual
        // Conta usuários ativos da empresa
        $usedUsers = $company->users()->count();

        // Conta atendentes e roles que atuam no chat (ex: agent, supervisor)
        $usedAttendants = $company->users()->whereIn('role', ['agent', 'supervisor', 'manager'])->count();

        // Canais integrados
        $usedChannels = $company->channels()->count();

        // Chatbots criados
        $usedChatbots = $company->chatbots()->count();

        // AI Conversations (Franquia do Plano)
        $usedAiConversations = $company->ai_conversations_used ?? 0;

        // AI Credits (Pacotes Avulsos)
        $aiCreditsBalance = $company->ai_credits_balance ?? 0;

        return [
            'users' => $this->formatResource('Usuários da Plataforma', $usedUsers, $maxUsers),
            'attendants' => $this->formatResource('Atendentes e Supervisores', $usedAttendants, $maxAttendants),
            'channels' => $this->formatResource('Canais (WhatsApp, Instagram, etc)', $usedChannels, $maxChannels),
            'chatbots' => $this->formatResource('Chatbots / Fluxos IA', $usedChatbots, $maxChatbots),
            'ai_conversations' => $this->formatResource('Conversas IA (Franquia Mensal)', $usedAiConversations, $aiConversationsLimit),
            'ai_credits' => [
                'name' => 'Créditos Avulsos de IA (Não expiram)',
                'used' => 0,
                'limit' => $aiCreditsBalance,
                'remaining' => $aiCreditsBalance,
                'percentage' => $aiCreditsBalance > 0 ? 0 : 100,
                'color' => 'success',
                'unlimited' => false,
            ],
        ];
    }

    /**
     * Retorna métricas de resultado do sistema (ex: conversas, leads)
     */
    public function getResultsMetrics(Company $company): array
    {
        // Estatísticas básicas para o painel de resultados
        $totalConversations = $company->conversations()->count();
        $totalLeads = Contact::where('company_id', $company->id)->count();

        // Apenas como exemplo simples de horas economizadas (cada conversa = 5 min)
        $hoursSaved = round(($totalConversations * 5) / 60);

        return [
            'conversations' => $totalConversations,
            'leads' => $totalLeads,
            'hours_saved' => $hoursSaved,
        ];
    }

    private function formatResource(string $name, int $used, int $limit): array
    {
        $percentage = $limit > 0 ? min(100, round(($used / $limit) * 100)) : 0;
        $remaining = max(0, $limit - $used);

        // Regras de cor: Verde < 70%, Amarelo 70% a 89%, Vermelho >= 90%
        $color = 'success';
        if ($percentage >= 90) {
            $color = 'danger';
        } elseif ($percentage >= 70) {
            $color = 'warning';
        }

        return [
            'name' => $name,
            'used' => $used,
            'limit' => $limit,
            'remaining' => $remaining,
            'percentage' => $percentage,
            'color' => $color,
            'unlimited' => false, // O conceito de ilimitado foi removido na nova estratégia comercial
        ];
    }
}
