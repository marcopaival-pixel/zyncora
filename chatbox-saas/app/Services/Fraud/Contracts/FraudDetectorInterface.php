<?php

namespace App\Services\Fraud\Contracts;

use App\Models\WidgetAccessLog;
use App\Models\Chatbot;

interface FraudDetectorInterface
{
    /**
     * Analisa o log e retorna o Score de Risco (0-100) e os motivos.
     * Retorna array: ['score' => int, 'reasons' => array]
     */
    public function analyze(WidgetAccessLog $log, Chatbot $chatbot): array;
}
