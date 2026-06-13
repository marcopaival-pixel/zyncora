<?php

namespace App\Filament\Widgets;

use App\Models\AiConsumptionHistory;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AiConsumptionStats extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();
        if (! $user) {
            return [];
        }

        $company = $user->company;
        if (! $company) {
            return [];
        }

        $monthlyTokens = AiConsumptionHistory::where('company_id', $company->id)
            ->whereMonth('created_at', now()->month)
            ->sum('conversations_used');

        return [
            Stat::make('Mensagens IA Consumidas (Mês)', $monthlyTokens)
                ->description('As suas interações via IA deste mês')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),
            Stat::make('Limite do Plano Atual', $company->plan?->ai_conversations ?? 'Ilimitado')
                ->description('Tokens disponíveis')
                ->color('success'),
        ];
    }
}
