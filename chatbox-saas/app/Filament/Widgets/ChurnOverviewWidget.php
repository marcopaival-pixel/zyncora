<?php

namespace App\Filament\Widgets;

use App\Models\Company;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ChurnOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalCanceled = Company::where('status', 'canceled')->count();
        
        $recentCancellations = Company::where('status', 'canceled')
            ->where('updated_at', '>=', now()->subDays(30))
            ->count();
            
        $totalExpired = Company::where('status', 'expired')->count();

        return [
            Stat::make('Total Canceladas', $totalCanceled)
                ->description('Empresas que cancelaram a assinatura')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
            Stat::make('Canceladas (Últimos 30 dias)', $recentCancellations)
                ->description('Churn recente')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
            Stat::make('Assinaturas Expiradas', $totalExpired)
                ->description('Contas pendentes de renovação/bloqueadas')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),
        ];
    }
}
