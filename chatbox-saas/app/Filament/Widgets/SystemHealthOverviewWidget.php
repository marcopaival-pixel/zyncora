<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SystemHealthOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        // Simulação de métricas (na vida real viriam do Sentry/Pulse/Cache)
        return [
            Stat::make('Uptime do Sistema', '99.98%')
                ->description('Últimos 30 dias')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
            Stat::make('Tempo Médio de Resposta', '245ms')
                ->description('Média global (última hora)')
                ->descriptionIcon('heroicon-m-bolt')
                ->color('success'),
            Stat::make('Taxa de Erro (5xx)', '0.04%')
                ->description('Aceitável (< 0.1%)')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('success'),
        ];
    }
}
