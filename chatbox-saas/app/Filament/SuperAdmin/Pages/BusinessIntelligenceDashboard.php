<?php

namespace App\Filament\SuperAdmin\Pages;

use App\Models\Company;
use App\Notifications\UpgradeSuggestionNotification;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Notification;

class BusinessIntelligenceDashboard extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';

    protected static ?string $navigationGroup = 'Inteligência Financeira';

    protected static ?string $title = 'Inteligência de Negócios (BI)';

    protected static string $view = 'filament.pages.business-intelligence-dashboard';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isPlatformAdmin() ?? false;
    }

    public function table(Table $table): Table
    {
        // Companies near AI limit (e.g. usage > 80% of balance or 80% of monthly quota)
        return $table
            ->query(
                Company::query()
                    ->where('status', 'active')
                    ->whereRaw('(ai_credits_used / NULLIF(ai_credits_balance, 0)) > 0.8')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ai_credits_used')
                    ->label('Uso IA (Tokens)')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('ai_credits_balance')
                    ->label('Saldo IA (Tokens)')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('percentage')
                    ->label('Uso (%)')
                    ->getStateUsing(function (Company $record) {
                        if (! $record->ai_credits_balance) {
                            return '100%';
                        }
                        $pct = ($record->ai_credits_used / $record->ai_credits_balance) * 100;

                        return number_format($pct, 1).'%';
                    })
                    ->badge()
                    ->color(function (Company $record) {
                        if (! $record->ai_credits_balance) {
                            return 'danger';
                        }
                        $pct = ($record->ai_credits_used / $record->ai_credits_balance);
                        if ($pct >= 0.95) {
                            return 'danger';
                        }
                        if ($pct >= 0.8) {
                            return 'warning';
                        }

                        return 'success';
                    }),
            ])
            ->actions([
                Action::make('suggest_upgrade')
                    ->label('Sugerir Upgrade')
                    ->icon('heroicon-o-arrow-up-circle')
                    ->action(fn (Company $record) => Notification::route('mail', $record->email)->notify(new UpgradeSuggestionNotification($record)))
                    ->requiresConfirmation(),
            ]);
    }
}
