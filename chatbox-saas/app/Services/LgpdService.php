<?php

namespace App\Services;

use App\Models\LgpdAuditLog;
use App\Models\LgpdConsent;
use App\Models\LgpdSetting;
use Illuminate\Support\Facades\Request;

class LgpdService
{
    /**
     * Registra auditoria de LGPD
     */
    public function log(string $action, $resource = null, array $payload = []): LgpdAuditLog
    {
        return LgpdAuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'resource_type' => $resource ? get_class($resource) : null,
            'resource_id' => $resource ? $resource->id : null,
            'ip_address' => Request::ip(),
            'payload' => $payload,
        ]);
    }

    /**
     * Registra consentimento do usuário
     */
    public function recordConsent(array $data): LgpdConsent
    {
        return LgpdConsent::create([
            'customer_id' => $data['customer_id'] ?? null,
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'consent_given' => true,
            'consent_at' => now(),
        ]);
    }

    /**
     * Retorna as configurações de LGPD da empresa atual
     */
    public function getSettings(): ?LgpdSetting
    {
        return LgpdSetting::firstOrCreate(
            ['company_id' => app(TenantService::class)->getCompanyId()],
            [
                'is_active' => true,
                'retention_days' => 0,
                'consent_term' => 'Ao continuar, você aceita nossos termos de uso e política de privacidade.',
            ]
        );
    }

    /**
     * Máscara dados sensíveis (PII) para logs técnicos
     */
    public function maskSensitiveData(array $data): array
    {
        $sensitiveKeys = ['email', 'phone', 'client_phone', 'name', 'client_name', 'cpf', 'cnpj', 'address', 'password'];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->maskSensitiveData($value);

                continue;
            }

            if (in_array($key, $sensitiveKeys)) {
                $data[$key] = $this->applyMask((string) $value);
            }
        }

        return $data;
    }

    private function applyMask(string $value): string
    {
        if (strlen($value) <= 4) {
            return '****';
        }

        return substr($value, 0, 2).str_repeat('*', strlen($value) - 4).substr($value, -2);
    }
}
