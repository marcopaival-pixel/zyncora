<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ConversationResource;
use App\Filament\Widgets\Concerns\RequiresCompanyOrPlatformAdmin;
use App\Services\ConversationStatsService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ConversationOverview extends StatsOverviewWidget
{
    protected static ?string $pollingInterval = null;

    use RequiresCompanyOrPlatformAdmin;

    protected static ?int $sort = 4;

    protected function getHeading(): ?string
    {
        return 'Fila de atendimento';
    }

    protected function getDescription(): ?string
    {
        return 'Volumes no contexto da conta: abertas, em espera e encerradas nas últimas 24 h.';
    }

    protected function getStats(): array
    {
        $counts = app(ConversationStatsService::class)->dashboardCounts(auth()->user());

        $open = $counts['open'];
        $waiting = $counts['waiting'];
        $closedToday = $counts['closed_today'];

        $filaUrl = ConversationResource::getUrl('index');

        return [
            Stat::make('Conversas abertas', (string) $open)
                ->description('Aguardando atendimento')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color('indigo')
                ->url($filaUrl),
            Stat::make('Aguardando', (string) $waiting)
                ->description('Tickets em fila')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->url($filaUrl),
            Stat::make('Encerradas hoje', (string) $closedToday)
                ->description('Finalizadas nas últimas 24h')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->url($filaUrl),
        ];
    }
}
