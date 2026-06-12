<?php

namespace App\Filament\SuperAdmin\Pages;

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
            \App\Filament\Widgets\SystemHealthOverviewWidget::class,
            \App\Filament\Widgets\ApiLatencyChartWidget::class,
            \App\Filament\Widgets\RecentErrorsTableWidget::class,
        ];
    }
}
