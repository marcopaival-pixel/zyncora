<?php

namespace App\Filament\Widgets;

use App\Models\AiUsageLog;
use App\Models\Conversation;
use App\Models\Message;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrchestratorMetricsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $totalConversations = Conversation::count();
        $totalMessages = Message::count();

        // Simulação de cálculo de economia:
        // Mensagens recebidas - chamadas feitas ao modelo
        $aiCalls = AiUsageLog::count();
        $savedCalls = max(0, $totalMessages - $aiCalls);

        $costSaved = number_format($savedCalls * 0.005, 2, ',', '.'); // Exemplo $0.005 por call economizada

        return [
            Stat::make('Conversas Totais', $totalConversations)
                ->description('Conversas gerenciadas pelo orquestrador')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('primary'),

            Stat::make('Chamadas IA Evitadas', $savedCalls)
                ->description('Resolvido via regras ou FAQ')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('success'),

            Stat::make('Economia Estimada', 'R$ '.$costSaved)
                ->description('Tokens poupados pela orquestração')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}
