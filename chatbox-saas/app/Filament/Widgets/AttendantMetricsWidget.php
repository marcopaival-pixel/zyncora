<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AttendantMetricsWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected function getStats(): array
    {
        $totalAttendants = User::whereIn('role', [User::ROLE_AGENT, User::ROLE_SUPERVISOR])->count();
        $onlineAttendants = User::whereIn('role', [User::ROLE_AGENT, User::ROLE_SUPERVISOR])
            ->where('presence_status', 'online')
            ->count();
            
        $activeAttendants = User::whereIn('role', [User::ROLE_AGENT, User::ROLE_SUPERVISOR])
            ->where('status', 'active')
            ->count();

        return [
            Stat::make('Total de Atendentes', $totalAttendants)
                ->description('Agentes e supervisores')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
            Stat::make('Atendentes Ativos (Conta)', $activeAttendants)
                ->description('Contas não bloqueadas')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
            Stat::make('Atendentes Online', $onlineAttendants)
                ->description('Logados no momento')
                ->descriptionIcon('heroicon-m-signal')
                ->color('success'),
        ];
    }
}
