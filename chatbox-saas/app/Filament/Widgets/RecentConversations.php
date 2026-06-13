<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ConversationResource;
use App\Filament\Widgets\Concerns\RequiresCompanyOrPlatformAdmin;
use App\Models\Conversation;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentConversations extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    use RequiresCompanyOrPlatformAdmin;

    protected static bool $isLazy = true;

    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Últimas conversas';

    public function table(Table $table): Table
    {
        return $table
            ->description('Atalho para acompanhar o que mudou por último na fila.')
            ->emptyStateHeading('Nenhuma conversa')
            ->emptyStateDescription('Quando existirem tickets, os mais recentes aparecem aqui.')
            ->query(function (): Builder {
                $query = Conversation::query()
                    ->with(['channel', 'assignee', 'company']);

                $auth = auth()->user();
                if ($auth && ! $auth->isPlatformAdmin()) {
                    $query->where('company_id', $auth->company_id);
                }

                return $query->latest('updated_at');
            })
            ->columns([
                Tables\Columns\TextColumn::make('client_name')
                    ->label('Cliente / visitante')
                    ->searchable(false)
                    ->description(fn (Conversation $record): ?string => $record->client_phone)
                    ->limit(40),
                Tables\Columns\TextColumn::make('company.name')
                    ->label('Empresa')
                    ->visible(fn (): bool => (bool) auth()->user()?->isPlatformAdmin())
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'open' => 'Aberta',
                        'waiting' => 'Aguardando',
                        'closed' => 'Encerrada',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'success',
                        'waiting' => 'warning',
                        'closed' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('channel.type')
                    ->label('Canal')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('assignee.name')
                    ->label('Atendente')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atividade')
                    ->since(),
            ])
            ->recordUrl(fn (Conversation $record): string => ConversationResource::getUrl('view', ['record' => $record]))
            ->defaultSort('updated_at', 'desc')
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5)
            ->headerActions([
                Action::make('ver_todas')
                    ->label('Fila completa')
                    ->url(ConversationResource::getUrl('index'))
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->color('gray')
                    ->size('sm'),
            ]);
    }
}
