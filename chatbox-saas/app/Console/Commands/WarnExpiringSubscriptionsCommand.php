<?php

namespace App\Console\Commands;

use App\Services\SubscriptionExpiryNotificationService;
use Illuminate\Console\Command;

class WarnExpiringSubscriptionsCommand extends Command
{
    protected $signature = 'subscriptions:warn-expiring {--days= : Dias de antecedência (override de config)}';

    protected $description = 'Envia e-mail de aviso antes da expiração da assinatura';

    public function handle(SubscriptionExpiryNotificationService $notifications): int
    {
        $days = $this->option('days') !== null
            ? (int) $this->option('days')
            : null;

        $count = $notifications->warnExpiringCompanies($days);

        $this->info("Avisos de expiração enviados: {$count} empresa(s).");

        return self::SUCCESS;
    }
}
