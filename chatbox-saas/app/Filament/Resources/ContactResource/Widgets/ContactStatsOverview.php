<?php

namespace App\Filament\Resources\ContactResource\Widgets;

use App\Models\Contact;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class ContactStatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total de Contactos', Contact::count())
                ->description('Todos os contactos registados')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('Novos (7 dias)', Contact::where('created_at', '>=', Carbon::now()->subDays(7))->count())
                ->description('Registados recentemente')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Com Interações', Contact::whereHas('conversations')->count())
                ->description('Contactos que já conversaram')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color('info'),
        ];
    }
}
