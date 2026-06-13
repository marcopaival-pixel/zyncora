<?php

namespace App\Filament\SuperAdmin\Pages;

use App\Models\Company;
use App\Notifications\OverdueInvoiceNotification;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Notification;

class DefaultingCompaniesDashboard extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationGroup = 'Financeiro';

    protected static ?string $title = 'Inadimplência';

    protected static string $view = 'filament.pages.defaulting-companies-dashboard';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isPlatformAdmin() ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Company::query()
                    ->where('status', '!=', 'canceled')
                    ->whereHas('invoices', function (Builder $query) {
                        $query->where('status', 'overdue');
                    })
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status Conta')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'suspended' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('invoices_sum_amount')
                    ->label('Valor Devido')
                    ->sum('invoices', 'amount') // Note: requires sum aggregate
                    ->money('BRL')
                    ->getStateUsing(fn (Company $record) => $record->invoices()->where('status', 'overdue')->sum('amount')),
                TextColumn::make('invoices_count')
                    ->label('Faturas Vencidas')
                    ->counts([
                        'invoices' => fn (Builder $query) => $query->where('status', 'overdue'),
                    ]),
            ])
            ->actions([
                Action::make('notify')
                    ->label('Notificar')
                    ->icon('heroicon-o-bell')
                    ->action(fn (Company $record) => Notification::route('mail', $record->email)->notify(new OverdueInvoiceNotification($record)))
                    ->requiresConfirmation(),
                Action::make('suspend')
                    ->label('Suspender')
                    ->color('danger')
                    ->icon('heroicon-o-no-symbol')
                    ->action(fn (Company $record) => $record->update(['status' => 'suspended']))
                    ->visible(fn (Company $record) => $record->status !== 'suspended')
                    ->requiresConfirmation(),
            ]);
    }
}
