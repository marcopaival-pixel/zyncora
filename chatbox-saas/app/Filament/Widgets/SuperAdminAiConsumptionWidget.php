<?php

namespace App\Filament\Widgets;

use App\Models\Company;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class SuperAdminAiConsumptionWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Company::query()
                    ->where('ai_conversations_used', '>', 0)
                    ->orWhere('ai_credits_used', '>', 0)
                    ->orderByRaw('(ai_conversations_used + ai_credits_used) DESC')
                    ->limit(5)
            )
            ->heading('Ranking de Consumo de IA (Top 5)')
            ->description('Empresas que mais consumiram Conversas de Inteligência Artificial.')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('plan.name')
                    ->label('Plano Atual')
                    ->badge(),
                Tables\Columns\TextColumn::make('ai_conversations_used')
                    ->label('Franquia Utilizada')
                    ->numeric()
                    ->sortable()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('ai_credits_used')
                    ->label('Extras Utilizados')
                    ->numeric()
                    ->sortable()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('ai_credits_balance')
                    ->label('Saldo Extra')
                    ->numeric()
                    ->sortable()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('usage_percent')
                    ->label('Franquia (%)')
                    ->state(function (Company $record) {
                        $limit = $record->plan?->max_ai_conversations ?? 0;
                        if ($limit == 0) {
                            return 100;
                        }

                        return min(100, round(($record->ai_conversations_used / $limit) * 100, 1));
                    })
                    ->suffix('%')
                    ->color(fn ($state) => $state >= 80 ? 'danger' : ($state >= 50 ? 'warning' : 'success')),
            ])
            ->paginated(false);
    }
}
