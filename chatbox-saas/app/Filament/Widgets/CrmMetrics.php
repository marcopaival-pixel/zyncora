<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\DealResource;
use App\Filament\Widgets\Concerns\RequiresCompanyOrPlatformAdmin;
use App\Models\Deal;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CrmMetrics extends StatsOverviewWidget
{
    protected static ?string $pollingInterval = null;

    use RequiresCompanyOrPlatformAdmin;

    protected static ?int $sort = 5;

    protected function getHeading(): ?string
    {
        return 'CRM em números';
    }

    protected function getDescription(): ?string
    {
        return 'Valor total do pipeline, quantidade de negócios e ticket médio (R$).';
    }

    protected function getStats(): array
    {
        $user = auth()->user();
        $query = Deal::query();

        if ($user && ! $user->isPlatformAdmin()) {
            $query->where('company_id', $user->company_id);
        }

        $totalValue = (clone $query)->sum('value');
        $dealsCount = (clone $query)->count();
        $avgDealValue = $dealsCount > 0 ? $totalValue / $dealsCount : 0;

        $dealsUrl = DealResource::getUrl('index');

        return [
            Stat::make('Pipeline Total', 'R$ '.number_format($totalValue, 2, ',', '.'))
                ->description('Soma de todos os negócios')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart([1000, 5000, 12000, 8000, 25000, 30000, 45000])
                ->url($dealsUrl),

            Stat::make('Negócios Ativos', $dealsCount)
                ->description('Total de oportunidades no CRM')
                ->descriptionIcon('heroicon-m-presentation-chart-line')
                ->color('info')
                ->chart([2, 5, 8, 4, 12, 10, 15])
                ->url($dealsUrl),

            Stat::make('Ticket Médio', 'R$ '.number_format($avgDealValue, 2, ',', '.'))
                ->description('Valor médio por oportunidade')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('warning')
                ->url($dealsUrl),
        ];
    }
}

