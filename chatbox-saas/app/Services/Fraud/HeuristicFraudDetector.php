<?php

namespace App\Services\Fraud;

use App\Models\Chatbot;
use App\Models\WidgetAccessLog;
use App\Repositories\Contracts\WidgetAccessLogRepositoryInterface;
use App\Services\Fraud\Contracts\FraudDetectorInterface;

class HeuristicFraudDetector implements FraudDetectorInterface
{
    protected WidgetAccessLogRepositoryInterface $logRepository;

    public function __construct(WidgetAccessLogRepositoryInterface $logRepository)
    {
        $this->logRepository = $logRepository;
    }

    public function analyze(WidgetAccessLog $log, Chatbot $chatbot): array
    {
        $score = 0;
        $reasons = [];

        // Regra 1: Domínio não autorizado ou vazio
        if (! $log->domain || $log->domain === 'unknown') {
            $score += 30;
            $reasons[] = 'Missing or unknown Origin/Referer';
        }

        // Regra 2: Status já foi bloqueado
        if ($log->status === 'blocked') {
            $score += 50;
            $reasons[] = 'Access was explicitly blocked: '.$log->block_reason;
        }

        // Regra 3: Verificação de múltiplos IPs rápidos via repositório
        $recentIps = $this->logRepository->getRecentDistinctIpsCount($log->session_id, 5);

        if ($recentIps > 3) {
            $score += 40;
            $reasons[] = 'Multiple IP addresses for same session in short period';
        }

        return [
            'score' => $score,
            'reasons' => $reasons,
        ];
    }
}
