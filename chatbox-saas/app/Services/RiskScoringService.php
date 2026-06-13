<?php

namespace App\Services;

use App\Models\Chatbot;
use App\Models\WidgetAccessLog;
use App\Models\WidgetFraudAlert;
use App\Services\Fraud\Contracts\FraudDetectorInterface;

class RiskScoringService
{
    protected FraudDetectorInterface $fraudDetector;

    public function __construct(FraudDetectorInterface $fraudDetector)
    {
        $this->fraudDetector = $fraudDetector;
    }

    /**
     * Analisa um log de acesso e gera uma pontuação de risco e dispara alertas se necessário.
     */
    public function evaluateAccess(WidgetAccessLog $log, Chatbot $chatbot): int
    {
        $result = $this->fraudDetector->analyze($log, $chatbot);

        $score = $result['score'] ?? 0;
        $reasons = $result['reasons'] ?? [];

        $log->risk_score = $score;
        $log->save();

        // Se o score ultrapassar o limite, gera um alerta
        if ($score >= 70) {
            $this->triggerFraudAlert($chatbot, $score, $reasons, $log);
        }

        return $score;
    }

    protected function triggerFraudAlert(Chatbot $chatbot, int $score, array $reasons, WidgetAccessLog $log)
    {
        $level = 'low';
        if ($score >= 90) {
            $level = 'critical';
        } elseif ($score >= 70) {
            $level = 'high';
        } elseif ($score >= 40) {
            $level = 'medium';
        }

        WidgetFraudAlert::create([
            'chatbot_id' => $chatbot->id,
            'company_id' => $chatbot->company_id,
            'risk_level' => $level,
            'trigger_reason' => implode(' | ', $reasons),
            'fingerprint_data' => [
                'log_id' => $log->id,
                'ip' => $log->ip_address,
                'domain' => $log->domain,
                'fingerprint_hash' => $log->fingerprint_hash,
            ],
        ]);

        // Aqui podemos injetar o disparo da Notificação Corporativa
        // event(new FraudAlertTriggered($chatbot, $level, $reasons));
    }
}
