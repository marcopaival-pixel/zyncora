<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\PlanSubscriptionService;
use Illuminate\Console\Command;

class ResetAiFranchises extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:reset-franchises';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Zera a franquia mensal de IA das empresas e salva o histórico de consumo';

    /**
     * Execute the console command.
     */
    public function handle(PlanSubscriptionService $subscriptionService)
    {
        $this->info('Iniciando fechamento de franquias de IA...');

        // Em um cenário real, você buscaria empresas cuja data de renovação (ou expires_at) é hoje
        // Para simplificar, poderíamos buscar as empresas com expires_at < now e que estão ativas, e forçar a renovação.
        // Aqui chamaremos um scan básico.

        $companies = Company::where('subscription_status', 'active')->get();

        $count = 0;
        foreach ($companies as $company) {
            // Lógica de expiração diária baseada no dia da assinatura, adaptada ao caso de uso.
            // Aqui estamos apenas provendo o comando que poderá ser agendado.
            // Para efeitos de auditoria/MVP, a lógica exata de data depende de como o SaaS renova.

            // $subscriptionService->resetAiFranchise($company, now()->subMonth(), now());
            // $count++;
        }

        $this->info("Franquias resetadas: {$count}");
    }
}
