<?php

namespace App\Filament\SuperAdmin\Pages;

use Filament\Pages\Page;
use App\Models\PaymentHistory;
use App\Models\Invoice;

class FinanceDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Gestão do Sistema';

    protected static ?string $title = 'Dashboard Financeiro';

    protected static string $view = 'filament.pages.finance-dashboard';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isPlatformAdmin() ?? false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isPlatformAdmin() ?? false;
    }

    protected function getViewData(): array
    {
        return [
            'totalRevenue' => PaymentHistory::where('status', 'paid')->sum('amount'),
            'mrr' => PaymentHistory::where('status', 'paid')->where('type', 'subscription')->where('paid_at', '>=', now()->subDays(30))->sum('amount'),
            'invoicesGenerated' => Invoice::count(),
            'invoicesFailed' => Invoice::where('status', 'failed')->count(),
            'latestPayments' => PaymentHistory::with('company')->latest('paid_at')->take(5)->get(),
        ];
    }
}
