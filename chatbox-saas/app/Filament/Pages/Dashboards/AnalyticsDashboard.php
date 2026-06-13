<?php

namespace App\Filament\Pages\Dashboards;

use Filament\Pages\Dashboard as BaseDashboard;

class AnalyticsDashboard extends BaseDashboard
{
    protected static ?string $title = 'Analytics & Relatórios';

    protected static ?string $navigationLabel = 'Dashboard Analítico';

    protected static ?string $navigationGroup = 'Analytics';

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static string $routePath = 'analytics';

    protected static ?int $navigationSort = 1;

    protected ?string $subheading = 'Métricas detalhadas, volumetria e gráficos pesados.';

    public function getColumns(): int | string | array
    {
        return 12;
    }

    public function getWidgets(): array
    {
        return [
            // Widgets pesados podem ser movidos para cá no futuro
            \App\Filament\Widgets\LatestLogs::class,
        ];
    }
}
