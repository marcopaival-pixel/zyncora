<?php

namespace App\Filament\SuperAdmin\Widgets;

use App\Models\PaymentHistory;
use Filament\Widgets\ChartWidget;

class RevenueChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Evolução da Receita (Últimos 6 meses)';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $labels[] = $month->translatedFormat('M Y');

            $revenue = PaymentHistory::where('status', 'paid')
                ->whereYear('paid_at', $month->year)
                ->whereMonth('paid_at', $month->month)
                ->sum('amount');

            $data[] = $revenue;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Receita (R$)',
                    'data' => $data,
                    'backgroundColor' => '#36A2EB',
                    'borderColor' => '#9BD0F5',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
