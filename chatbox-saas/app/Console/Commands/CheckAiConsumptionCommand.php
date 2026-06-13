<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\SystemErrorLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckAiConsumptionCommand extends Command
{
    protected $signature = 'saas:check-ai-consumption';

    protected $description = 'Verifica empresas que ultrapassaram 90% do consumo de IA e emite um alerta';

    public function handle()
    {
        $companies = Company::where('status', 'active')
            ->where('ai_credits_balance', '>', 0)
            ->get();

        $alerts = 0;

        foreach ($companies as $company) {
            $percentage = ($company->ai_credits_used / $company->ai_credits_balance) * 100;

            if ($percentage >= 90) {
                Log::warning("ALERTA DE CONSUMO DE IA: A empresa {$company->name} ({$company->id}) consumiu ".number_format($percentage, 2).'% de seus tokens.');

                SystemErrorLog::create([
                    'level' => 'warning',
                    'message' => "Alerta de Consumo de IA: Empresa {$company->name} atingiu ".number_format($percentage, 2).'% do limite de tokens.',
                    'context' => json_encode([
                        'company_id' => $company->id,
                        'ai_credits_balance' => $company->ai_credits_balance,
                        'ai_credits_used' => $company->ai_credits_used,
                    ]),
                ]);

                $alerts++;
            }

            if ($percentage >= 100) {
                Log::error("CONSUMO DE IA ESGOTADO: A empresa {$company->name} excedeu o limite.");
            }
        }

        $this->info("Verificação concluída. $alerts alertas de consumo gerados.");
    }
}
