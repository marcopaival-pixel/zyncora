<?php

namespace App\Services;

use App\Models\SystemErrorLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schema;

class SystemHealthMonitoringService
{
    /**
     * @return array<int, array{label: string, status: string, message: string, critical: bool}>
     */
    public function run(): array
    {
        return [
            $this->checkDatabase(),
            $this->checkRedisWhenRequired(),
            $this->checkStorageWritable(),
            $this->checkQueueDepth(),
            $this->checkFailedJobs(),
            $this->checkRecentApplicationErrors(),
            $this->checkBackupFreshness(),
            $this->checkDiskSpace(),
            $this->checkSentryConfigured(),
        ];
    }

    /**
     * @param  array<int, array{status: string, critical: bool}>  $checks
     */
    public function overallStatus(array $checks): string
    {
        $hasCriticalFailure = false;
        $hasWarning = false;

        foreach ($checks as $check) {
            if ($check['status'] === 'fail' && $check['critical']) {
                $hasCriticalFailure = true;
            }

            if ($check['status'] === 'warn' || ($check['status'] === 'fail' && ! $check['critical'])) {
                $hasWarning = true;
            }
        }

        if ($hasCriticalFailure) {
            return 'critical';
        }

        if ($hasWarning) {
            return 'degraded';
        }

        return 'ok';
    }

    /**
     * @param  array<int, array{status: string, critical: bool}>  $checks
     */
    public function hasCriticalFailures(array $checks): bool
    {
        foreach ($checks as $check) {
            if ($check['status'] === 'fail' && $check['critical']) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, array{label: string, status: string, message: string}>  $checks
     * @return array<int, string>
     */
    public function failureMessages(array $checks): array
    {
        $messages = [];

        foreach ($checks as $check) {
            if (in_array($check['status'], ['fail', 'warn'], true)) {
                $messages[] = $check['label'].': '.$check['message'];
            }
        }

        return $messages;
    }

    protected function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();

            return $this->result('Base de dados', 'pass', 'OK', true);
        } catch (\Throwable $e) {
            return $this->result(
                'Base de dados',
                'fail',
                'Falha: '.$e->getMessage(),
                true
            );
        }
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

            return $this->result('Redis', 'pass', 'PONG', true);
        } catch (\Throwable $e) {
            return $this->result(
                'Redis',
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
            true
        );
    }

    protected function checkQueueDepth(): array
    {
        if (config('queue.default') === 'sync') {
            return $this->result('Fila de jobs', 'skip', 'QUEUE_CONNECTION=sync', false);
        }

        if (! Schema::hasTable('jobs')) {
            return $this->result('Fila de jobs', 'skip', 'Tabela jobs inexistente', false);
        }

        $pending = (int) DB::table('jobs')->count();
        $criticalThreshold = (int) config('chatbox.monitoring.queue_pending_critical', 200);
        $warningThreshold = (int) config('chatbox.monitoring.queue_pending_warning', 50);

        if ($pending >= $criticalThreshold) {
            return $this->result(
                'Fila de jobs',
                'fail',
                "{$pending} pendentes (crítico >= {$criticalThreshold})",
                true
            );
        }

        if ($pending >= $warningThreshold) {
            return $this->result(
                'Fila de jobs',
                'warn',
                "{$pending} pendentes (aviso >= {$warningThreshold})",
                false
            );
        }

        return $this->result('Fila de jobs', 'pass', "{$pending} pendentes", false);
    }

    protected function checkFailedJobs(): array
    {
        if (! Schema::hasTable('failed_jobs')) {
            return $this->result('Jobs falhados', 'skip', 'Tabela failed_jobs inexistente', false);
        }

        $failed = (int) DB::table('failed_jobs')->count();
        $warningThreshold = (int) config('chatbox.monitoring.failed_jobs_warning', 1);

        if ($failed >= $warningThreshold) {
            return $this->result(
                'Jobs falhados',
                'warn',
                "{$failed} registo(s) em failed_jobs",
                false
            );
        }

        return $this->result('Jobs falhados', 'pass', "{$failed} registo(s)", false);
    }

    protected function checkRecentApplicationErrors(): array
    {
        if (! Schema::hasTable('system_error_logs')) {
            return $this->result('Erros recentes', 'skip', 'Tabela system_error_logs inexistente', false);
        }

        $count = SystemErrorLog::query()
            ->where('created_at', '>=', now()->subDay())
            ->count();

        $warningThreshold = (int) config('chatbox.monitoring.error_log_warning_24h', 10);

        if ($count >= $warningThreshold) {
            return $this->result(
                'Erros recentes (24h)',
                'warn',
                "{$count} erros (aviso >= {$warningThreshold})",
                false
            );
        }

        return $this->result('Erros recentes (24h)', 'pass', "{$count} erros", false);
    }

    protected function checkBackupFreshness(): array
    {
        $backupPath = storage_path('app/backups');

        if (! File::isDirectory($backupPath)) {
            return $this->result('Backup recente', 'skip', 'Pasta backups inexistente', false);
        }

        $newest = collect(File::allFiles($backupPath))
            ->map(fn ($file) => $file->getMTime())
            ->max();

        if (! $newest) {
            return $this->result('Backup recente', 'warn', 'Nenhum ficheiro de backup encontrado', false);
        }

        $lastBackupAt = \Carbon\Carbon::createFromTimestamp($newest);
        $ageHours = (int) $lastBackupAt->diffInHours(now());
        $maxAgeHours = (int) config('chatbox.monitoring.backup_max_age_hours', 26);

        if ($ageHours >= $maxAgeHours) {
            return $this->result(
                'Backup recente',
                'warn',
                "Último backup há {$ageHours}h (limite {$maxAgeHours}h)",
                false
            );
        }

        return $this->result(
            'Backup recente',
            'pass',
            "Último backup há {$ageHours}h",
            false
        );
    }

    protected function checkDiskSpace(): array
    {
        $path = storage_path();
        $freeBytes = @disk_free_space($path);

        if ($freeBytes === false) {
            return $this->result('Espaço em disco', 'skip', 'Não foi possível ler espaço livre', false);
        }

        $freeMb = (int) floor($freeBytes / 1024 / 1024);
        $minMb = (int) config('chatbox.monitoring.disk_free_min_mb', 500);

        if ($freeMb < $minMb) {
            return $this->result(
                'Espaço em disco',
                'fail',
                "{$freeMb} MB livres (mínimo {$minMb} MB)",
                true
            );
        }

        return $this->result('Espaço em disco', 'pass', "{$freeMb} MB livres", false);
    }

    protected function checkSentryConfigured(): array
    {
        if (! app()->environment('production')) {
            return $this->result('Sentry', 'skip', 'Não production', false);
        }

        $dsn = config('sentry.dsn') ?: env('SENTRY_LARAVEL_DSN');
        $ok = filled($dsn);

        return $this->result(
            'Sentry configurado',
            $ok ? 'pass' : 'warn',
            $ok ? 'DSN definido' : 'SENTRY_LARAVEL_DSN em falta',
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
