<?php

namespace App\Filament\Widgets;

use App\Models\Company;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ExpiringTrialsTableWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    
    protected static ?string $heading = 'Empresas em Trial Vencendo em Breve';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Company::query()
                    ->where('status', 'trial')
                    ->whereNotNull('trial_end_at')
                    ->whereBetween('trial_end_at', [now()->subDays(1), now()->addDays(7)])
                    ->orderBy('trial_end_at', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Empresa')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('responsible_name')
                    ->label('Responsável'),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefone'),
                Tables\Columns\TextColumn::make('ai_credits_used')
                    ->label('Consumo de IA')
                    ->numeric()
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('trial_end_at')
                    ->label('Vencimento')
                    ->date('d/m/Y')
                    ->color(fn ($record) => $record->trial_end_at?->isPast() ? 'danger' : 'warning'),
            ])
            ->actions([
                Tables\Actions\Action::make('Ver Detalhes')
                    ->url(fn (Company $record): string => route('filament.admin.resources.companies.view', $record))
                    ->button(),
            ]);
    }
}
