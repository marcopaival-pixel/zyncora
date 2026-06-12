<?php

namespace App\Traits;

trait HasHealthScore
{
    /**
     * Calcula o score de saúde da empresa (0 a 100).
     */
    public function getHealthScoreAttribute(): int
    {
        $score = 100;

        // Se está suspensa ou cancelada, a saúde é 0
        if (in_array($this->status, ['suspended', 'canceled'])) {
            return 0;
        }

        // Penalidade por não ter usuários ativos além do admin
        if ($this->users()->count() <= 1) {
            $score -= 20;
        }

        // Penalidade por não ter chatbots
        if ($this->chatbots()->count() === 0) {
            $score -= 30;
        }

        // Penalidade se o trial está quase acabando e não tem consumo de IA
        if ($this->status === 'trial' && $this->calcularDiasRestantes() <= 3 && $this->ai_credits_used == 0) {
            $score -= 40;
        }

        // Bônus/Penalidade por conversas recentes (simulado aqui, ideal checar última conversa)
        $latestConversation = $this->conversations()->latest()->first();
        if ($latestConversation && $latestConversation->created_at->diffInDays(now()) > 7) {
            $score -= 30;
        }

        return max(0, $score);
    }

    /**
     * Retorna o status textual da saúde.
     */
    public function getHealthStatusAttribute(): string
    {
        $score = $this->health_score;

        if ($score >= 80) {
            return 'saudável';
        } elseif ($score >= 50) {
            return 'atenção';
        } else {
            return 'risco';
        }
    }
}
