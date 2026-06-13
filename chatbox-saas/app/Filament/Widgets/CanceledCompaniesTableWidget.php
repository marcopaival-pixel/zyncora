<?php

namespace App\Filament\Widgets;

use App\Models\Company;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class CanceledCompaniesTableWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Últimos Cancelamentos';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Company::query()
                    ->whereIn('status', ['canceled', 'expired'])
                    ->orderBy('updated_at', 'desc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Empresa')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => $state === 'canceled' ? 'danger' : 'warning')
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                Tables\Columns\TextColumn::make('plan.name')
                    ->label('Plano Anterior'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Data do Evento')
                    ->date('d/m/Y H:i'),
                Tables\Columns\TextColumn::make('users_count')
                    ->label('Usuários Totais')
                    ->state(fn ($record) => $record->users()->count()),
            ])
            ->actions([
                Tables\Actions\Action::make('Ver Detalhes')
                    ->url(fn (Company $record): string => route('filament.admin.resources.companies.view', $record))
                    ->button(),
            ]);
    }
}
