<?php

namespace App\Console\Commands;

use App\Services\PlanSubscriptionService;
use Illuminate\Console\Command;

class ExpireOverdueSubscriptionsCommand extends Command
{
    protected $signature = 'subscriptions:expire-overdue';

    protected $description = 'Marca assinaturas vencidas e degrada limites ao plano básico';

    public function handle(PlanSubscriptionService $subscriptions): int
    {
        $count = $subscriptions->expireOverdueCompanies();

        $this->info("Assinaturas expiradas processadas: {$count} empresa(s).");

        return self::SUCCESS;
    }
}
