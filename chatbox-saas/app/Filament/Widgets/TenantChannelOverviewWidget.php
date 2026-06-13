<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TenantChannelOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $company = filament()->auth()->user()->company;

        if (! $company) {
            return [];
        }

        $totalChannels = $company->channels()->count();
        $maxChannels = $company->max_channels ?? ($company->plan ? $company->plan->max_channels : 0);
        $connectedChannels = $company->channels()->connected()->count();
        $failedChannels = $company->channels()->failed()->count();

        return [
            Stat::make('Canais Usados', "{$totalChannels} / {$maxChannels}")
                ->description('Limite do plano atual')
                ->descriptionIcon('heroicon-m-signal')
                ->color($totalChannels >= $maxChannels ? 'warning' : 'success'),

            Stat::make('Canais Online', $connectedChannels)
                ->description('Conectados e operantes')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Falhas de Conexão', $failedChannels)
                ->description($failedChannels > 0 ? 'Requer atenção imediata' : 'Tudo funcionando perfeitamente')
                ->descriptionIcon($failedChannels > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-shield-check')
                ->color($failedChannels > 0 ? 'danger' : 'success'),
        ];
    }
}
