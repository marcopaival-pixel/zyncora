<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\RequiresCompanyOrPlatformAdmin;
use App\Models\ChatLog;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestLogs extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected static ?int $sort = 50;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Atividades Recentes do Sistema';

    public function table(Table $table): Table
    {
        return $table
            ->description('Registo de eventos relevantes para auditoria.')
            ->emptyStateHeading('Nenhuma atividade')
            ->emptyStateDescription('Ainda não há registos para o seu contexto.')
            ->query(function () {
                return ChatLog::query()
                    ->when(!auth()->user()->isPlatformAdmin(), fn($q) => $q->where('company_id', auth()->user()->company_id))
                    ->latest('logged_at');
            })
            ->columns([
                Tables\Columns\TextColumn::make('log_type')
                    ->badge()
                    ->label('Tipo'),
                Tables\Columns\TextColumn::make('description')
                    ->label('Evento')
                    ->limit(100),
                Tables\Columns\TextColumn::make('logged_at')
                    ->label('Horário')
                    ->since()
                    ->color('gray'),
            ])
            ->defaultPaginationPageOption(5)
            ->paginated([5]);
    }
}

