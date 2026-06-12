<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyIntegration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class WebhookIngestionService
{
    /**
     * Valida o desafio de assinatura da Meta (hub.challenge) para webhooks universais.
     */
    public function universalVerify(Request $request, string $expectedToken): Response
    {
        $mode = $request->query('hub_mode') ?? $request->query('hub.mode');
        $token = $request->query('hub_verify_token') ?? $request->query('hub.verify_token');
        $challenge = $request->query('hub_challenge') ?? $request->query('hub.challenge');

        if ($mode === 'subscribe' && hash_equals($expectedToken, (string) $token)) {
            return response((string) $challenge, 200);
        }

        abort(403, 'Verification failed.');
    }

    /**
     * Valida o desafio de assinatura da Meta (hub.challenge) para um webhook específico de uma empresa.
     */
    public function companyVerify(Request $request, Company $company, string $driver): Response
    {
        $mode = $request->query('hub_mode') ?? $request->query('hub.mode');
        $token = $request->query('hub_verify_token') ?? $request->query('hub.verify_token');
        $challenge = $request->query('hub_challenge') ?? $request->query('hub.challenge');

        $integration = CompanyIntegration::query()
            ->where('company_id', $company->id)
            ->where('driver', $driver)
            ->first();

        $expected = $integration?->webhook_verify_token ?? '';

        if ($mode === 'subscribe' && $expected !== '' && hash_equals($expected, (string) $token)) {
            return response((string) $challenge, 200);
        }

        abort(403, 'Verification failed.');
    }

    /**
     * Valida o payload de webhooks universais (assinatura HMAC-SHA256).
     */
    public function validateUniversalSignature(Request $request, ?string $appSecret, string $channelName): bool
    {
        if (!$appSecret) {
            return true; // Se não tem segredo configurado, assume válido (depende da política de segurança)
        }

        $signature = $request->header('X-Hub-Signature-256');
        if (!$signature) {
            return false;
        }

        $expected = 'sha256=' . hash_hmac('sha256', $request->getContent(), $appSecret);
        
        $isValid = hash_equals($expected, (string) $signature);
        
        if (!$isValid) {
            Log::warning("{$channelName}_universal_webhook_invalid_signature", ['ip' => $request->ip()]);
        }

        return $isValid;
    }

    /**
     * Valida o payload de webhooks por empresa (assinatura HMAC-SHA256).
     */
    public function validateCompanySignature(Request $request, Company $company, string $driver): bool
    {
        $signature = $request->header('X-Hub-Signature-256');
        if (!$signature) {
            return false;
        }

        $integration = CompanyIntegration::query()
            ->where('company_id', $company->id)
            ->where('driver', $driver)
            ->first();

        $appSecret = data_get($integration?->credentials, 'app_secret');
        if (!$appSecret) {
            Log::warning("{$driver}_webhook_missing_app_secret", [
                'company_id' => $company->id,
            ]);
            return false;
        }

        $expected = 'sha256=' . hash_hmac('sha256', $request->getContent(), (string) $appSecret);

        return hash_equals($expected, (string) $signature);
    }
}
