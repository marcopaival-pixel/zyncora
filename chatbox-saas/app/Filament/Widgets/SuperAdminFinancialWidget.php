<?php

namespace App\Filament\Widgets;

use App\Models\Company;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SuperAdminFinancialWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Totais Gerais
        $totalCompanies = Company::count();
        $totalUsers = User::count();
        
        // Status do SaaS
        $activeCompanies = Company::where('status', 'active')->count();
        $trialCompanies = Company::where('status', 'trial')->count();
        $suspendedCompanies = Company::whereIn('status', ['suspended', 'expired', 'blocked', 'canceled'])->count();

        // MRR Calculation
        // Para fins do widget, calculamos o MRR somando o preço dos planos das contas ATIVAS.
        $mrr = Company::where('status', 'active')
            ->join('plans', 'companies.plan_id', '=', 'plans.id')
            ->where('plans.interval', 'month')
            ->sum('plans.price');

        // Adiciona ao MRR o valor proporcional dos planos anuais (se existirem)
        $mrrFromYearly = Company::where('status', 'active')
            ->join('plans', 'companies.plan_id', '=', 'plans.id')
            ->where('plans.interval', 'year')
            ->sum('plans.price') / 12;

        $totalMrr = $mrr + $mrrFromYearly;
        $arr = $totalMrr * 12;

        return [
            Stat::make('MRR (Mensal Recorrente)', 'R$ ' . number_format($totalMrr, 2, ',', '.'))
                ->description('R$ ' . number_format($arr, 2, ',', '.') . ' de ARR (Estimado)')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Total de Organizações', $totalCompanies)
                ->description("{$activeCompanies} Ativas · {$trialCompanies} em Trial · {$suspendedCompanies} Inativas")
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('primary'),

            Stat::make('Total de Usuários', $totalUsers)
                ->description('Soma de todos os membros e agentes')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
        ];
    }
}
