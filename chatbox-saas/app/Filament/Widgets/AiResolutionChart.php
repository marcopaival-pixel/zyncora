<?php

namespace App\Filament\Widgets;

use App\Models\Conversation;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class AiResolutionChart extends ChartWidget
{
    protected static ?string $pollingInterval = null;

    protected static bool $isLazy = true;

    protected static ?string $heading = 'Eficiência: IA vs Atendimento Humano';

    protected static ?string $icon = 'heroicon-o-cpu-chip';

    protected static ?string $description = 'Proporção de tickets resolvidos autonomamente pela IA versus atendimento manual.';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $user = auth()->user();
        if (!$user) return [];

        $query = Conversation::query();
        if (!$user->isPlatformAdmin()) {
            $query->where('company_id', $user->company_id);
        }

        // Resolução Humana (com atendente atribuído)
        $humanCount = (clone $query)->whereNotNull('assignee_id')->count();

        // Resolução IA (atendimento puramente por bot)
        $aiCount = (clone $query)->whereNull('assignee_id')->count();

        return [
            'datasets' => [
                [
                    'label' => 'Total de Atendimentos',
                    'data' => [$aiCount, $humanCount],
                    'backgroundColor' => [
                        '#10b981', // Verde (IA)
                        '#3b82f6', // Azul (Humano)
                    ],
                ],
            ],
            'labels' => ['Resolvidos por IA / Bot', 'Atendimento Humano'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'cutout' => '70%', // Estilo anel fino premium
            'radius' => '80%',
        ];
    }

    protected function getExtraAttributes(): array
    {
        return [
            'class' => 'fi-wi-chart-ai-resolution rounded-[2rem] border-0 shadow-2xl bg-gradient-to-br from-gray-900 via-gray-900 to-primary-950/20 backdrop-blur-xl',
        ];
    }
}

