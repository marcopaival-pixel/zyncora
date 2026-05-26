<?php

namespace App\Filament\Widgets;

use App\Models\Conversation;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class TopLeadsWidget extends BaseWidget
{
    protected static bool $isLazy = true;

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Oportunidades de Ouro (IA Score)';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Conversation::query()
                    ->where('status', '!=', 'closed')
                    ->where('ai_score', '>', 50)
                    ->orderByDesc('ai_score')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('client_name')
                    ->label('Lead')
                    ->description(fn (Conversation $record) => $record->client_phone ?? 'Sem telefone')
                    ->default('Visitante Anônimo'),
                Tables\Columns\TextColumn::make('ai_score')
                    ->label('Score')
                    ->numeric()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 80 => 'success',
                        $state >= 60 => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('ai_sentiment')
                    ->label('Sentimento')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'positive' => '🙂 Positivo',
                        'negative' => '😡 Negativo',
                        'neutral' => '😐 Neutro',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('ai_summary')
                    ->label('Resumo IA')
                    ->limit(50),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última Mensagem')
                    ->since(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Atender Agora')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->url(fn (Conversation $record): string => "/admin/conversations/{$record->id}"),
            ]);
    }
}
