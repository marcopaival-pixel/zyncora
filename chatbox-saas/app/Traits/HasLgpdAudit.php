<?php

namespace App\Traits;

use App\Services\LgpdService;

trait HasLgpdAudit
{
    /**
     * Registra auditoria de acesso a dados sensíveis
     */
    protected function auditLgpdAccess(string $action, $resource = null, array $payload = [])
    {
        app(LgpdService::class)->log($action, $resource, $payload);
    }
}
