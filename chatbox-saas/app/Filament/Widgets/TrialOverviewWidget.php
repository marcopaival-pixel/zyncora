<?php

namespace App\Filament\Widgets;

use App\Models\Company;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TrialOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalTrials = Company::where('status', 'trial')->count();
        $expiringSoon = Company::where('status', 'trial')
            ->whereNotNull('trial_end_at')
            ->whereBetween('trial_end_at', [now(), now()->addDays(3)])
            ->count();

        $highEngagementTrials = Company::where('status', 'trial')
            ->where('ai_credits_used', '>', 100)
            ->count();

        return [
            Stat::make('Total em Trial', $totalTrials)
                ->description('Empresas no período de teste')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('info'),
            Stat::make('Vencendo em 3 dias', $expiringSoon)
                ->description('Trials prestes a expirar')
                ->descriptionIcon('heroicon-m-clock')
                ->color($expiringSoon > 0 ? 'warning' : 'success'),
            Stat::make('Alto Engajamento', $highEngagementTrials)
                ->description('Trials que testaram a IA intensamente')
                ->descriptionIcon('heroicon-m-fire')
                ->color('success'),
        ];
    }
}
