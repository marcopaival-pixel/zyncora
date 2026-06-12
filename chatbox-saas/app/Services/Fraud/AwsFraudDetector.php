<?php

namespace App\Services\Fraud;

use App\Services\Fraud\Contracts\FraudDetectorInterface;
use App\Models\WidgetAccessLog;
use App\Models\Chatbot;
use Illuminate\Support\Facades\Http;

class AwsFraudDetector implements FraudDetectorInterface
{
    public function analyze(WidgetAccessLog $log, Chatbot $chatbot): array
    {
        // Exemplo de integração com uma API de ML Externa (ex: AWS Sagemaker / Fraud Detector)
        try {
            $response = Http::timeout(2)->post('https://fraud-detector.us-east-1.amazonaws.com/model/predict', [
                'features' => [
                    'ip_address' => $log->ip_address,
                    'user_agent' => $log->user_agent,
                    'domain' => $log->domain,
                    'is_blocked' => $log->status === 'blocked',
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'score' => $data['risk_score'] ?? 0,
                    'reasons' => ['Machine Learning Inference: ' . ($data['classification'] ?? 'Unknown')],
                ];
            }
        } catch (\Exception $e) {
            // Em caso de falha da API de IA, cai para score 0 ou aciona o Heurístico por fallback
        }

        return ['score' => 0, 'reasons' => []];
    }
}
