<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\RequiresCompanyOrPlatformAdmin;
use App\Models\AiUsageLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AiCostsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    use RequiresCompanyOrPlatformAdmin;

    protected static ?int $sort = 1;

    protected function getHeading(): ?string
    {
        return 'Monitoramento de Custos de IA';
    }

    protected function getStats(): array
    {
        $user = auth()->user();

        // Apenas Administradores da Plataforma podem ver os custos reais agregados
        if (! $user?->isPlatformAdmin()) {
            return [];
        }

        // Estatísticas Globais
        $totalTokens = AiUsageLog::sum('total_tokens') ?? 0;
        $totalCost = AiUsageLog::sum('estimated_cost') ?? 0;

        // Empresa que mais consumiu
        $topCompanyLog = AiUsageLog::selectRaw('company_id, sum(estimated_cost) as total_cost, sum(total_tokens) as tokens')
            ->groupBy('company_id')
            ->orderByDesc('total_cost')
            ->first();

        $topCompanyName = $topCompanyLog && $topCompanyLog->company
            ? $topCompanyLog->company->name
            : 'Nenhuma';

        return [
            Stat::make('Conversas IA Consumidas (Total)', number_format($totalTokens, 0, ',', '.'))
                ->description('Todas as requisições Gemini')
                ->descriptionIcon('heroicon-m-cpu-chip')
                ->color('info'),

            Stat::make('Custo Estimado (Global)', '$ '.number_format((float) $totalCost, 4, ',', '.'))
                ->description('Custo da API do Google Cloud')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('danger'),

            Stat::make('Maior Consumidor', $topCompanyName)
                ->description($topCompanyLog ? number_format($topCompanyLog->tokens, 0, ',', '.').' conversas utilizadas' : 'Sem dados')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('warning'),
        ];
    }
}
