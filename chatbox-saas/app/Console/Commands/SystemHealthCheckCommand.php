<?php

namespace App\Console\Commands;

use App\Services\HealthAlertService;
use App\Services\SystemHealthMonitoringService;
use Illuminate\Console\Command;

class SystemHealthCheckCommand extends Command
{
    protected $signature = 'system:health-check
                            {--json : Saída JSON para monitorização externa}
                            {--alert : Registar alertas e enviar para Sentry quando degradado/crítico}';

    protected $description = 'Monitorização operacional (BD, Redis, filas, backups, erros, disco)';

    public function handle(SystemHealthMonitoringService $monitoring, HealthAlertService $alerts): int
    {
        $checks = $monitoring->run();
        $status = $monitoring->overallStatus($checks);

        if ($this->option('json')) {
            $this->line(json_encode([
                'status' => $status,
                'checked_at' => now()->toIso8601String(),
                'checks' => $checks,
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            return $monitoring->hasCriticalFailures($checks) ? self::FAILURE : self::SUCCESS;
        }

        $rows = array_map(function (array $check) {
            $icon = match ($check['status']) {
                'pass' => '<fg=green>✓</>',
                'fail' => '<fg=red>✗</>',
                'warn' => '<fg=yellow>!</>',
                default => '-',
            };

            return [
                $check['label'],
                $icon.' '.$check['status'],
                $check['message'],
            ];
        }, $checks);

        $this->table(['Verificação', 'Estado', 'Detalhe'], $rows);
        $this->line('Estado geral: '.$status);

        if ($this->option('alert') && $status !== 'ok') {
            $this->dispatchAlert($alerts, $monitoring, $checks, $status);
        }

        if ($monitoring->hasCriticalFailures($checks)) {
            $this->error('Monitorização: estado crítico.');

            return self::FAILURE;
        }

        if ($status === 'degraded') {
            $this->warn('Monitorização: estado degradado.');

            return self::SUCCESS;
        }

        $this->info('Monitorização: sistema saudável.');

        return self::SUCCESS;
    }

    /**
     * @param  array<int, array{label: string, status: string, message: string}>  $checks
     */
    protected function dispatchAlert(
        HealthAlertService $alerts,
        SystemHealthMonitoringService $monitoring,
        array $checks,
        string $status
    ): void {
        $messages = $monitoring->failureMessages($checks);
        $summary = '[Chatbox] Health '.$status.': '.implode(' | ', $messages);

        $alerts->dispatch($summary, $status, $checks);
    }
}
