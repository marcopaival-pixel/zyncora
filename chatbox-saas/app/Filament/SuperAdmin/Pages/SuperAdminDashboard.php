<?php

namespace App\Filament\SuperAdmin\Pages;

use App\Filament\Widgets\AttendantMetricsWidget;
use App\Filament\Widgets\ChannelMetricsWidget;
use App\Filament\Widgets\SuperAdminAiConsumptionWidget;
use App\Filament\Widgets\SuperAdminFinancialWidget;
use App\Filament\Widgets\SupportTicketsWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class SuperAdminDashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationGroup = 'Plataforma';

    protected static ?string $title = 'Visão Executiva (SaaS)';

    protected static ?string $slug = 'dashboard';

    protected static string $routePath = '/';

    protected static ?string $navigationLabel = 'Visão Executiva';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isPlatformAdmin() ?? false;
    }

    public function getWidgets(): array
    {
        return [
            SuperAdminFinancialWidget::class,
            SuperAdminAiConsumptionWidget::class,
            ChannelMetricsWidget::class,
            AttendantMetricsWidget::class,
            SupportTicketsWidget::class,
        ];
    }

    public function getColumns(): int|string|array
    {
        return 1;
    }
}
