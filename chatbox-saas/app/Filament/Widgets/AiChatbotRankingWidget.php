<?php

namespace App\Filament\Widgets;

use App\Models\Chatbot;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class AiChatbotRankingWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Chatbot::query()
                    ->where('use_ai', true)
                    ->withCount('aiAuditLogs')
                    ->orderByDesc('ai_audit_logs_count')
                    ->limit(5)
            )
            ->heading('Ranking de Chatbots por Volume de IA (Top 5)')
            ->description('Chatbots com maior número de interações baseadas em Inteligência Artificial.')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Chatbot')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('company.name')
                    ->label('Empresa'),
                Tables\Columns\TextColumn::make('ai_audit_logs_count')
                    ->label('Interações Totais')
                    ->counts('aiAuditLogs')
                    ->sortable()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => $state === 'active' ? 'success' : 'danger')
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
            ])
            ->paginated(false);
    }
}
