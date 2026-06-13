<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\RequiresCompanyOrPlatformAdmin;
use App\Models\Conversation;
use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AgentPerformanceChart extends ChartWidget
{
    protected static ?string $pollingInterval = null;

    use RequiresCompanyOrPlatformAdmin;

    protected static bool $isLazy = true;

    protected static ?string $heading = 'Performance por atendente';

    protected static ?string $description = 'Conversas encerradas no mês corrente, por responsável.';

    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '340px';

    protected function getData(): array
    {
        $user = auth()->user();

        if (! $user) {
            return $this->emptyChartData();
        }

        if (! $user->isPlatformAdmin() && ! $user->company_id) {
            return $this->emptyChartData();
        }

        $query = Conversation::query()
            ->whereNotNull('assignee_id')
            ->whereNotNull('closed_at')
            ->where('status', 'closed')
            ->whereMonth('closed_at', now()->month)
            ->whereYear('closed_at', now()->year);

        if (! $user->isPlatformAdmin()) {
            $query->where('company_id', $user->company_id);
        }

        $rows = $query
            ->select('assignee_id', DB::raw('count(*) as total'))
            ->groupBy('assignee_id')
            ->orderByDesc('total')
            ->limit(12)
            ->get();

        if ($rows->isEmpty()) {
            return $this->emptyChartData();
        }

        $ids = $rows->pluck('assignee_id')->filter()->values();
        $names = User::query()
            ->whereIn('id', $ids)
            ->pluck('name', 'id');

        $labels = $rows->map(function ($row) use ($names) {
            $name = $names[$row->assignee_id] ?? null;

            return Str::limit($name ?? ('Utilizador #'.$row->assignee_id), 32);
        })->all();

        $data = $rows->pluck('total')->map(fn ($v) => (int) $v)->all();

        return [
            'datasets' => [
                [
                    'label' => 'Encerradas no mês',
                    'data' => $data,
                    'backgroundColor' => 'rgba(139, 92, 246, 0.7)',
                    'borderColor' => '#7c3aed',
                    'borderWidth' => 1,
                    'borderRadius' => 6,
                    'maxBarThickness' => 22,
                ],
            ],
            'labels' => $labels,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyChartData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Encerradas no mês',
                    'data' => [0],
                    'backgroundColor' => 'rgba(148, 163, 184, 0.35)',
                    'borderColor' => 'rgba(100, 116, 139, 0.6)',
                    'borderWidth' => 1,
                    'borderRadius' => 6,
                ],
            ],
            'labels' => ['Sem conversas encerradas neste mês'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    /**
     * Barras horizontais: título curto na secção e nomes dos atendentes legíveis (eixo Y).
     *
     * @return array<string, mixed>
     */
    protected function getOptions(): ?array
    {
        return [
            'indexAxis' => 'y',
            'responsive' => true,
            'maintainAspectRatio' => false,
            'layout' => [
                'padding' => [
                    'left' => 4,
                    'right' => 16,
                    'top' => 4,
                    'bottom' => 4,
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                        'stepSize' => 1,
                    ],
                    'grid' => [
                        'display' => true,
                    ],
                ],
                'y' => [
                    'grid' => [
                        'display' => false,
                    ],
                    'ticks' => [
                        'autoSkip' => false,
                    ],
                ],
            ],
        ];
    }
}
