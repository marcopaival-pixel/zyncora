<?php

namespace App\Filament\SuperAdmin\Pages;

use App\Filament\Widgets\ChurnOverviewWidget;
use App\Filament\Widgets\CanceledCompaniesTableWidget;
use Filament\Pages\Page;

class ChurnAndSubscriptionDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-down';
    protected static ?string $navigationGroup = 'Gestão Executiva';
    protected static ?string $title = 'Painel de Cancelamento e Churn';
    protected static ?string $slug = 'churn-management';
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.churn-management-dashboard';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isPlatformAdmin() ?? false;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ChurnOverviewWidget::class,
        ];
    }
    
    protected function getFooterWidgets(): array
    {
        return [
            CanceledCompaniesTableWidget::class,
        ];
    }
}
