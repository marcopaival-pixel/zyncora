<?php

namespace App\Services;

use App\Models\Chatbot;
use Illuminate\Http\Request;

class WidgetFingerprintService
{
    /**
     * Gera um hash único (Fingerprint) baseado nos dados da requisição.
     */
    public function generateFingerprint(Request $request, Chatbot $chatbot): string
    {
        $domain = $request->header('Origin') ?? $request->header('Referer') ?? 'unknown';
        $host = $request->getHost();
        $userAgent = $request->header('User-Agent');
        $ip = $request->ip();

        $data = [
            'chatbot_id' => $chatbot->id,
            'domain' => $domain,
            'host' => $host,
            'user_agent' => $userAgent,
            'ip' => $ip,
        ];

        return hash('sha256', json_encode($data));
    }

    /**
     * Extrai dados puros do fingerprint para análise no motor de segurança.
     */
    public function extractFingerprintData(Request $request, Chatbot $chatbot): array
    {
        return [
            'origin' => $request->header('Origin'),
            'referer' => $request->header('Referer'),
            'host' => $request->getHost(),
            'user_agent' => $request->header('User-Agent'),
            'ip' => $request->ip(),
            'accept_language' => $request->header('Accept-Language'),
        ];
    }
}
