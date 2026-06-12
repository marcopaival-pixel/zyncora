<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Conversation;

class IntentRankingWidget extends ChartWidget
{
    protected static ?string $pollingInterval = null;

    protected static ?string $heading = 'Ranking de Intenções Detectadas';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        // Simulando contagem de intenções. Em um cenário real, as intenções
        // seriam logadas no banco (ex: numa tabela IntentLogs ou no Conversation).
        return [
            'datasets' => [
                [
                    'label' => 'Intenções',
                    'data' => [120, 95, 80, 50, 20],
                    'backgroundColor' => [
                        '#3b82f6', // blue
                        '#10b981', // green
                        '#f59e0b', // amber
                        '#8b5cf6', // violet
                        '#ef4444', // red
                    ],
                ],
            ],
            'labels' => ['Suporte/Dúvidas', 'Agendamento', 'Comercial', 'Financeiro', 'Transferência Humana'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}

