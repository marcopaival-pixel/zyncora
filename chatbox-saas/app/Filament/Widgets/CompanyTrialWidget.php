<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class CompanyTrialWidget extends Widget
{
    protected static ?string $pollingInterval = null;

    protected static string $view = 'filament.widgets.company-trial-widget';

    protected static ?int $sort = -3; // Top of the dashboard

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = Auth::user();
        if (! $user || ! $user->company) {
            return false;
        }

        return $user->company->subscription_status === 'trial';
    }

    protected function getViewData(): array
    {
        $company = Auth::user()->company;
        $diasRestantes = $company->calcularDiasRestantes();
        $totalDias = 14;
        $progress = $totalDias > 0 ? (($totalDias - $diasRestantes) / $totalDias) * 100 : 100;
        $trialEndAt = $company->trial_end_at ? $company->trial_end_at->format('d/m/Y') : '';

        return [
            'diasRestantes' => $diasRestantes,
            'totalDias' => $totalDias,
            'progress' => $progress,
            'trialEndAt' => $trialEndAt,
        ];
    }
}
