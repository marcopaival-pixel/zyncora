<?php

namespace App\Filament\Widgets;

use App\Models\SupportTicket;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SupportTicketsWidget extends BaseWidget
{
    protected static ?int $sort = 6;

    protected function getStats(): array
    {
        $openTickets = SupportTicket::where('status', 'open')->count();
        $inProgressTickets = SupportTicket::where('status', 'in_progress')->count();
        $criticalTickets = SupportTicket::whereIn('status', ['open', 'in_progress'])
            ->where('priority', 'critical')
            ->count();

        return [
            Stat::make('Chamados Abertos', $openTickets)
                ->description('Aguardando atendimento')
                ->descriptionIcon('heroicon-m-inbox')
                ->color($openTickets > 0 ? 'warning' : 'success'),
            Stat::make('Em Andamento', $inProgressTickets)
                ->description('Sendo tratados agora')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('info'),
            Stat::make('Chamados Críticos', $criticalTickets)
                ->description('Prioridade Máxima')
                ->descriptionIcon('heroicon-m-fire')
                ->color($criticalTickets > 0 ? 'danger' : 'success'),
        ];
    }
}
