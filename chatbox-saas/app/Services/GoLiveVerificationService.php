<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redis;

class GoLiveVerificationService
{
    /**
     * @return array<int, array{label: string, status: string, message: string, critical: bool}>
     */
    public function run(bool $strict = false): array
    {
        $checks = [
            $this->checkPhpVersion(),
            $this->checkAppKey(),
            $this->checkDatabase(),
            $this->checkStorageWritable(),
            $this->checkProductionDebug(),
            $this->checkFilamentRegistrationDisabled(),
            $this->checkDemoRoutesDisabled(),
            $this->checkBillingSimulationDisabled(),
            $this->checkApiDocsDisabled(),
            $this->checkPaymentDriver(),
            $this->checkRedisWhenRequired(),
            $this->checkCorsOrigins(),
        ];

        if ($strict) {
            foreach ($checks as &$check) {
                if ($check['status'] === 'warn') {
                    $check['status'] = 'fail';
                    $check['critical'] = true;
                }
            }
        }

        return $checks;
    }

    public function hasFailures(array $checks): bool
    {
        foreach ($checks as $check) {
            if ($check['status'] === 'fail' && $check['critical']) {
                return true;
            }
        }

        return false;
    }

    protected function checkPhpVersion(): array
    {
        $ok = version_compare(PHP_VERSION, '8.2.0', '>=');

        return $this->result(
            'PHP >= 8.2',
            $ok ? 'pass' : 'fail',
            'Versão actual: '.PHP_VERSION,
            ! $ok
        );
    }

    protected function checkAppKey(): array
    {
        $ok = filled(config('app.key'));

        return $this->result(
            'APP_KEY definida',
            $ok ? 'pass' : 'fail',
            $ok ? 'OK' : 'Execute php artisan key:generate',
            ! $ok
        );
    }

    protected function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();

            return $this->result('Ligação à base de dados', 'pass', 'OK', false);
        } catch (\Throwable $e) {
            return $this->result(
                'Ligação à base de dados',
                'fail',
                'Falha: '.$e->getMessage(),
                true
            );
        }
    }

    protected function checkStorageWritable(): array
    {
        $paths = [storage_path('logs'), storage_path('framework/cache')];
        $failed = [];

        foreach ($paths as $path) {
            if (! File::isWritable($path)) {
                $failed[] = $path;
            }
        }

        return $this->result(
            'Storage gravável',
            $failed === [] ? 'pass' : 'fail',
            $failed === [] ? 'OK' : 'Sem permissão: '.implode(', ', $failed),
            $failed !== []
        );
    }

    protected function checkProductionDebug(): array
    {
        if (! app()->environment('production')) {
            return $this->result('APP_DEBUG=false (production)', 'skip', 'Ambiente: '.app()->environment(), false);
        }

        $ok = config('app.debug') === false;

        return $this->result(
            'APP_DEBUG=false (production)',
            $ok ? 'pass' : 'fail',
            $ok ? 'OK' : 'APP_DEBUG deve ser false em production',
            ! $ok
        );
    }

    protected function checkFilamentRegistrationDisabled(): array
    {
        if (! app()->environment('production')) {
            return $this->result('Registo Filament desactivado', 'skip', 'Não production', false);
        }

        $ok = config('chatbox.filament_registration_enabled') === false;

        return $this->result(
            'Registo Filament desactivado',
            $ok ? 'pass' : 'fail',
            $ok ? 'OK' : 'FILAMENT_REGISTRATION_ENABLED deve ser false',
            ! $ok
        );
    }

    protected function checkDemoRoutesDisabled(): array
    {
        if (! app()->environment('production')) {
            return $this->result('Rotas demo desactivadas', 'skip', 'Não production', false);
        }

        $ok = config('chatbox.demo_routes_enabled') === false;

        return $this->result(
            'Rotas demo desactivadas',
            $ok ? 'pass' : 'fail',
            $ok ? 'OK' : 'DEMO_ROUTES_ENABLED deve ser false',
            ! $ok
        );
    }

    protected function checkBillingSimulationDisabled(): array
    {
        if (! app()->environment('production')) {
            return $this->result('Billing simulado desactivado', 'skip', 'Não production', false);
        }

        $ok = config('chatbox.billing_simulation_enabled') === false;

        return $this->result(
            'Billing simulado desactivado',
            $ok ? 'pass' : 'fail',
            $ok ? 'OK' : 'BILLING_SIMULATION_ENABLED deve ser false',
            ! $ok
        );
    }

    protected function checkApiDocsDisabled(): array
    {
        if (! app()->environment('production')) {
            return $this->result('API docs desactivados', 'skip', 'Não production', false);
        }

        $ok = config('chatbox.api_docs_enabled') === false;

        return $this->result(
            'API docs desactivados',
            $ok ? 'pass' : 'warn',
            $ok ? 'OK' : 'API_DOCS_ENABLED recomendado false em production',
            false
        );
    }

    protected function checkPaymentDriver(): array
    {
        if (! app()->environment('production')) {
            return $this->result('Gateway de pagamento', 'skip', 'Não production', false);
        }

        $driver = config('chatbox.payment_driver', 'none');
        $ok = in_array($driver, ['stripe', 'mercadopago'], true);

        return $this->result(
            'Gateway de pagamento configurado',
            $ok ? 'pass' : 'warn',
            $ok ? "PAYMENT_DRIVER={$driver}" : 'PAYMENT_DRIVER=none — configure stripe ou mercadopago',
            false
        );
    }

    protected function checkRedisWhenRequired(): array
    {
        $usesRedis = in_array(config('session.driver'), ['redis'], true)
            || config('cache.default') === 'redis'
            || config('queue.default') === 'redis';

        if (! $usesRedis) {
            return $this->result('Redis', 'skip', 'Drivers não usam redis', false);
        }

        try {
            Redis::connection()->ping();

            return $this->result('Redis disponível', 'pass', 'PONG', false);
        } catch (\Throwable $e) {
            return $this->result(
                'Redis disponível',
                'fail',
                'Falha: '.$e->getMessage(),
                true
            );
        }
    }

    protected function checkCorsOrigins(): array
    {
        if (! app()->environment('production')) {
            return $this->result('CORS restrito', 'skip', 'Não production', false);
        }

        $origins = config('cors.allowed_origins', []);
        $hasWildcard = in_array('*', $origins, true);
        $ok = ! $hasWildcard && $origins !== [];

        return $this->result(
            'CORS sem wildcard',
            $ok ? 'pass' : 'warn',
            $ok ? count($origins).' origem(ns)' : 'Defina CORS_ALLOWED_ORIGINS sem *',
            false
        );
    }

    /**
     * @return array{label: string, status: string, message: string, critical: bool}
     */
    protected function result(string $label, string $status, string $message, bool $critical): array
    {
        return [
            'label' => $label,
            'status' => $status,
            'message' => $message,
            'critical' => $critical,
        ];
    }
}
