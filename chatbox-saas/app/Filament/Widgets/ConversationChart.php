<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\RequiresCompanyOrPlatformAdmin;
use App\Models\Conversation;
use Filament\Widgets\ChartWidget;

class ConversationChart extends ChartWidget
{
    protected static ?string $pollingInterval = null;

    use RequiresCompanyOrPlatformAdmin;

    protected static bool $isLazy = true;

    protected static ?string $heading = 'Novas conversas (7 dias)';

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $user = auth()->user();
        $labels = [];
        $data = [];

        $driver = Conversation::query()->getConnection()->getDriverName();

        if ($driver === 'mysql') {
            $from = now()->subDays(6)->startOfDay();
            $query = Conversation::query()
                ->where('created_at', '>=', $from);
            if ($user && ! $user->isPlatformAdmin()) {
                $query->where('company_id', $user->company_id);
            }
            $byDay = $query
                ->selectRaw('DATE(created_at) as d')
                ->selectRaw('COUNT(*) as c')
                ->groupByRaw('DATE(created_at)')
                ->orderBy('d')
                ->pluck('c', 'd');

            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $labels[] = $date->format('d/m');
                $key = $date->toDateString();
                $data[] = (int) ($byDay[$key] ?? 0);
            }
        } else {
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $labels[] = $date->format('d/m');
                $q = Conversation::query()->whereDate('created_at', $date);
                if ($user && ! $user->isPlatformAdmin()) {
                    $q->where('company_id', $user->company_id);
                }
                $data[] = $q->count();
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Novas Conversas',
                    'data' => $data,
                    'fill' => 'start',
                    'tension' => 0.4,
                    'backgroundColor' => 'rgba(99, 102, 241, 0.1)',
                    'borderColor' => '#6366f1',
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
