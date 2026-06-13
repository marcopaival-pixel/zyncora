<?php

namespace App\Filament\Widgets;

use App\Models\Channel;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class ChannelMetricsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected function getStats(): array
    {
        // Alterado de 3600 para 60 para atualizar mais frequentemente (1 min)
        $totalChannels = Cache::remember('global_total_channels', 60, fn () => Channel::count());
        $whatsappChannels = Cache::remember('global_whatsapp_channels', 60, fn () => Channel::where('type', 'whatsapp')->count());
        $widgetChannels = Cache::remember('global_widget_channels', 60, fn () => Channel::where('type', 'widget')->count());
        $failedChannels = Cache::remember('global_failed_channels', 60, fn () => Channel::failed()->count());

        return [
            Stat::make('Total de Canais', $totalChannels)
                ->description('Integrações ativas globalmente')
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color('primary'),

            Stat::make('Canais Ativos', $whatsappChannels + $widgetChannels)
                ->description("{$whatsappChannels} WhatsApp, {$widgetChannels} Widget")
                ->descriptionIcon('heroicon-m-chat-bubble-oval-left-ellipsis')
                ->color('success'),

            Stat::make('Integrações Falhas', $failedChannels)
                ->description($failedChannels > 0 ? 'Requer atenção do suporte' : 'Todos os canais operacionais')
                ->descriptionIcon($failedChannels > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-shield-check')
                ->color($failedChannels > 0 ? 'danger' : 'success'),
        ];
    }
}
