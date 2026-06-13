<?php

namespace App\Filament\SuperAdmin\Pages;

use App\Filament\Widgets\ApiLatencyChartWidget;
use App\Filament\Widgets\RecentErrorsTableWidget;
use App\Filament\Widgets\SystemHealthOverviewWidget;
use Filament\Pages\Page;

class SystemPerformanceDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-server-stack';

    protected static ?string $navigationGroup = 'Plataforma';

    protected static ?string $title = 'Performance & Erros';

    protected static ?string $slug = 'system-performance';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.system-performance-dashboard';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isPlatformAdmin() ?? false;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SystemHealthOverviewWidget::class,
            ApiLatencyChartWidget::class,
            RecentErrorsTableWidget::class,
        ];
    }
}
