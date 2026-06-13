<?php

namespace App\Filament\SuperAdmin\Widgets;

use App\Models\AiConsumptionHistory;
use App\Models\AiCreditPurchase;
use App\Models\Company;
use App\Models\PaymentHistory;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinanceStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $mrr = PaymentHistory::where('status', 'paid')
            ->where('type', 'subscription')
            ->where('paid_at', '>=', now()->subDays(30))
            ->sum('amount');

        // Estimate ARR from MRR
        $arr = $mrr * 12;

        $totalRevenue = PaymentHistory::where('status', 'paid')->sum('amount');

        return [
            Stat::make('Receita Recorrente Mensal (MRR)', 'R$ '.number_format($mrr, 2, ',', '.'))
                ->description('Receita dos últimos 30 dias')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Receita Anual Estimada (ARR)', 'R$ '.number_format($arr, 2, ',', '.'))
                ->description('MRR projetado em 12 meses')
                ->color('info'),

            Stat::make('Receita Total Historica', 'R$ '.number_format($totalRevenue, 2, ',', '.'))
                ->description('Todas as transações pagas')
                ->color('gray'),

            Stat::make('Total de Clientes', Company::count())
                ->description(Company::where('status', 'active')->count().' ativos')
                ->color('primary'),

            Stat::make('Clientes em Trial', Company::where('status', 'trial')->count())
                ->description('Aguardando conversão')
                ->color('warning'),

            Stat::make('Clientes Inadimplentes', Company::where('status', 'suspended')->count()) // Using suspended as a proxy or we check invoices
                ->description('Contas suspensas')
                ->color('danger'),

            Stat::make('Créditos IA Vendidos', AiCreditPurchase::where('status', 'completed')->sum('conversations_added'))
                ->description('Historico total')
                ->color('success'),

            Stat::make('Créditos IA Consumidos', AiConsumptionHistory::sum('conversations_used'))
                ->description('Conversas totais usadas')
                ->color('info'),
        ];
    }
}
