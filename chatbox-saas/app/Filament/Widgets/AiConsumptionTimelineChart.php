<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\AiConsumptionHistory;
use App\Models\Company;

class AiConsumptionTimelineChart extends ChartWidget
{
    protected static ?string $heading = 'Consumo de IA ao Longo do Tempo';
    
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $company = auth()->user()->company;
        
        if (!$company) {
            return [];
        }

        // Busca o histórico dos últimos 6 meses
        $histories = AiConsumptionHistory::where('company_id', $company->id)
            ->orderBy('period_start', 'asc')
            ->limit(6)
            ->get();

        $labels = [];
        $data = [];

        foreach ($histories as $history) {
            $labels[] = $history->period_start->format('M/Y');
            $data[] = $history->conversations_used;
        }

        // Incluir o mês atual (em andamento)
        $labels[] = now()->format('M/Y') . ' (Atual)';
        $data[] = $company->ai_conversations_used;

        return [
            'datasets' => [
                [
                    'label' => 'Conversas Consumidas',
                    'data' => $data,
                    'borderColor' => '#0ea5e9', // primary color
                    'backgroundColor' => 'rgba(14, 165, 233, 0.2)',
                    'fill' => true,
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
