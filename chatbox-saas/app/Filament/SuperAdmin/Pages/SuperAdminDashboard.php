<?php

namespace App\Filament\SuperAdmin\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;

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
            \App\Filament\Widgets\SuperAdminFinancialWidget::class,
            \App\Filament\Widgets\SuperAdminAiConsumptionWidget::class,
            \App\Filament\Widgets\ChannelMetricsWidget::class,
            \App\Filament\Widgets\AttendantMetricsWidget::class,
            \App\Filament\Widgets\SupportTicketsWidget::class,
        ];
    }
    
    public function getColumns(): int | string | array
    {
        return 1;
    }
}
