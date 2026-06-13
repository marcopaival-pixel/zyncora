<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class ApiLatencyChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Latência da API (ms)';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'WhatsApp Webhooks',
                    'data' => [120, 150, 180, 200, 170, 160, 250, 190, 140, 130, 120, 125],
                    'borderColor' => '#10b981',
                ],
                [
                    'label' => 'Integração LLM (OpenAI)',
                    'data' => [800, 950, 1100, 850, 1200, 900, 850, 1050, 980, 920, 870, 890],
                    'borderColor' => '#3b82f6',
                ],
            ],
            'labels' => ['00:00', '02:00', '04:00', '06:00', '08:00', '10:00', '12:00', '14:00', '16:00', '18:00', '20:00', '22:00'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
