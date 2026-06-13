<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;

class CheckTrialStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-trial-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica empresas em período de trial e envia notificações de encerramento';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando verificação de Trial...');

        // Pegar todas as empresas que estão em trial
        $companies = Company::where('subscription_status', 'trial')->get();

        foreach ($companies as $company) {
            $diasRestantes = $company->calcularDiasRestantes();

            if ($diasRestantes <= 0) {
                // Irá atualizar o status e registrar o log dentro deste método
                $company->verificarStatusAssinatura();
                $this->info("Empresa {$company->name} trial expirado.");
                // TODO: Enviar notificação de Expiração
            } elseif (in_array($diasRestantes, [1, 3, 7])) {
                // TODO: Enviar notificação de aviso (7, 3, 1 dias restantes)
                $this->info("Empresa {$company->name} tem {$diasRestantes} dias restantes.");
            }
        }

        $this->info('Verificação concluída.');
    }
}
