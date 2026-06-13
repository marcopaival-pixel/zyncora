<?php

namespace App\Filament\SuperAdmin\Pages;

use App\Filament\Widgets\ExpiringTrialsTableWidget;
use App\Filament\Widgets\TrialOverviewWidget;
use Filament\Pages\Page;

class TrialManagementDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Gestão Executiva';

    protected static ?string $title = 'Painel de Trial e Conversão';

    protected static ?string $slug = 'trial-management';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.trial-management-dashboard';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isPlatformAdmin() ?? false;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TrialOverviewWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            ExpiringTrialsTableWidget::class,
        ];
    }
}
