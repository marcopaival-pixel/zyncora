<?php

namespace App\Services;

use App\Models\Chatbot;
use App\Models\ChatbotSecurityToken;
use App\Models\ChatbotLicense;
use App\Models\ChatbotDomain;
use Illuminate\Support\Str;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class WidgetSecurityService
{
    /**
     * Gera tokens de segurança para um chatbot recém-criado.
     */
    public function generateTokens(Chatbot $chatbot): ChatbotSecurityToken
    {
        return ChatbotSecurityToken::create([
            'chatbot_id' => $chatbot->id,
            'public_token' => Str::random(40),
            'secret_key' => Str::random(64),
            'rotated_at' => now(),
        ]);
    }

    /**
     * Rotaciona as chaves de um chatbot.
     */
    public function rotateTokens(Chatbot $chatbot): ChatbotSecurityToken
    {
        $token = $chatbot->securityToken ?? new ChatbotSecurityToken(['chatbot_id' => $chatbot->id]);
        $token->public_token = Str::random(40);
        $token->secret_key = Str::random(64);
        $token->rotated_at = now();
        $token->save();

        return $token;
    }

    /**
     * Gera um JWT de curta duração para comunicação via socket/API após bootstrap.
     */
    public function generateSessionJwt(Chatbot $chatbot, string $domain, string $sessionId): string
    {
        $tokenModel = $chatbot->securityToken;
        if (!$tokenModel) {
            throw new \Exception('Security tokens not found for chatbot.');
        }

        $payload = [
            'iss' => config('app.url'),
            'aud' => $domain,
            'iat' => time(),
            'exp' => time() + (60 * 60 * 2), // 2 horas
            'chatbot_id' => $chatbot->id,
            'session_id' => $sessionId,
        ];

        return JWT::encode($payload, $tokenModel->secret_key, 'HS256');
    }

    /**
     * Valida um JWT vindo do widget.
     */
    public function validateSessionJwt(Chatbot $chatbot, string $jwt): ?object
    {
        $tokenModel = $chatbot->securityToken;
        if (!$tokenModel) {
            return null;
        }

        try {
            $decoded = JWT::decode($jwt, new Key($tokenModel->secret_key, 'HS256'));
            return $decoded;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Valida se o domínio tem permissão.
     */
    public function validateDomainAccess(Chatbot $chatbot, string $domain): bool
    {
        $domainRecord = ChatbotDomain::where('chatbot_id', $chatbot->id)
            ->where('domain', $domain)
            ->first();

        return $domainRecord && $domainRecord->status === 'approved';
    }

    /**
     * Valida se a licença está ativa.
     */
    public function validateLicense(Chatbot $chatbot): bool
    {
        $license = ChatbotLicense::where('chatbot_id', $chatbot->id)->first();
        if (!$license || $license->status !== 'active') {
            return false;
        }

        if ($license->expires_at && $license->expires_at->isPast()) {
            return false;
        }

        return true;
    }
}
