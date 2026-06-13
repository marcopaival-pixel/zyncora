<?php

namespace App\Filament\SuperAdmin\Pages;

use App\Filament\SuperAdmin\Widgets\FinanceStatsWidget;
use App\Filament\SuperAdmin\Widgets\RevenueChartWidget;
use Filament\Pages\Page;

class FinanceDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Gestão do Sistema';

    protected static ?string $title = 'Dashboard Financeiro';

    protected static string $view = 'filament.pages.finance-dashboard-empty';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isPlatformAdmin() ?? false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isPlatformAdmin() ?? false;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            FinanceStatsWidget::class,
            RevenueChartWidget::class,
        ];
    }
}
