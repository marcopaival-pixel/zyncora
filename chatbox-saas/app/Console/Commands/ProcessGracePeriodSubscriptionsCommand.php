<?php

namespace App\Console\Commands;

use App\Services\SubscriptionGracePeriodService;
use Illuminate\Console\Command;

class ProcessGracePeriodSubscriptionsCommand extends Command
{
    protected $signature = 'subscriptions:process-grace-period';

    protected $description = 'Marca empresas em período de graça e envia notificações';

    public function handle(SubscriptionGracePeriodService $gracePeriod): int
    {
        $marked = $gracePeriod->markGracePeriodCompanies();
        $notified = $gracePeriod->notifyGracePeriodCompanies();

        $this->info("Período de graça: {$marked} empresa(s) marcada(s), {$notified} aviso(s) enviado(s).");

        return self::SUCCESS;
    }
}
