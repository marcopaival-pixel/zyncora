<?php

namespace App\Console\Commands;

use App\Services\GoLiveVerificationService;
use Illuminate\Console\Command;

class GoLiveVerifyCommand extends Command
{
    protected $signature = 'go-live:verify {--strict : Tratar avisos como falhas}';

    protected $description = 'Verifica pré-requisitos de go-live (config, BD, Redis, segurança)';

    public function handle(GoLiveVerificationService $verification): int
    {
        $strict = (bool) $this->option('strict');
        $checks = $verification->run($strict);

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

        if ($verification->hasFailures($checks)) {
            $this->error('Go-live: verificações críticas falharam.');

            return self::FAILURE;
        }

        $this->info('Go-live: verificações críticas OK.');

        return self::SUCCESS;
    }
}
