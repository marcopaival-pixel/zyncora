<?php

namespace App\Console\Commands;

use App\Services\GoLiveSmokeService;
use Illuminate\Console\Command;

class GoLiveSmokeCommand extends Command
{
    protected $signature = 'go-live:smoke
                            {--url= : URL base (ex.: https://seudominio.com). Omitir para testar via kernel interno}
                            {--company-slug= : Slug da empresa para testar widget e API}';

    protected $description = 'Smoke tests HTTP pós-deploy (health, Filament, OpenAPI, rotas sensíveis)';

    public function handle(GoLiveSmokeService $smoke): int
    {
        $baseUrl = $this->option('url');
        $companySlug = $this->option('company-slug');

        if ($baseUrl) {
            $this->line('URL base: '.$baseUrl);
        } else {
            $this->line('Modo interno (kernel Laravel, sem servidor HTTP externo).');
        }

        $checks = $smoke->run($baseUrl ?: null, $companySlug ?: null);

        $rows = array_map(function (array $check) {
            $icon = $check['result'] === 'pass' ? '<fg=green>✓</>' : '<fg=red>✗</>';

            return [
                $check['label'],
                $check['path'],
                $check['status'] ?: 'erro',
                $check['expected'],
                $icon.' '.$check['result'],
            ];
        }, $checks);

        $this->table(['Teste', 'Path', 'HTTP', 'Esperado', 'Resultado'], $rows);

        if ($smoke->hasFailures($checks)) {
            $this->error('Smoke tests falharam.');

            return self::FAILURE;
        }

        $this->info('Smoke tests OK.');

        return self::SUCCESS;
    }
}
