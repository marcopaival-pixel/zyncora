<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConversationResource\Pages;
use App\Filament\Resources\ConversationResource\RelationManagers;
use App\Models\Conversation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use App\Services\AiService;
use Illuminate\Database\Eloquent\Builder;

class ConversationResource extends Resource
{
    protected static ?string $model = Conversation::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Atendimento';

    protected static ?string $modelLabel = 'Atendimento / Conversa';

    protected static ?string $pluralModelLabel = 'Fila de Atendimento';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'id';

    public static function getGloballySearchableAttributes(): array
    {
        return ['client_name', 'client_phone', 'id'];
    }

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        if (!$user) return null;

        return cache()->remember("nav_badge_waiting_{$user->company_id}", 30, function () use ($user) {
            $query = static::getModel()::where('status', 'waiting');
            if ($user && ! $user->isPlatformAdmin()) {
                $query->where('company_id', $user->company_id);
            }
            $count = $query->count();
            return $count > 0 ? (string) $count : null;
        });
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasPermission('view_conversas') ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission('view_conversas') ?? false;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['company', 'channel', 'assignee']);
        $user = auth()->user();
        if ($user && ! $user->isPlatformAdmin()) {
            $query->where('company_id', $user->company_id);

            // Se for agente, vê apenas as suas ou as pendentes (sem dono)
            if ($user->isAgent()) {
                $query->where(function ($q) use ($user) {
                    $q->where('assignee_id', $user->id)
                      ->orWhereNull('assignee_id');
                });
            }
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('status')
                    ->options([
                        'open' => 'Aberta',
                        'waiting' => 'Aguardando',
                        'closed' => 'Encerrada',
                    ])
                    ->required(),
                Forms\Components\Select::make('assignee_id')
                    ->label('Atendente')
                    ->searchable()
                    ->options(function () {
                        $record = request()->route('record');
                        $companyId = $record instanceof Conversation
                            ? $record->company_id
                            : auth()->user()?->company_id;
                        if (! $companyId) {
                            return [];
                        }

                        return \App\Models\User::query()
                            ->where('company_id', $companyId)
                            ->where('status', 'active')
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all();
                    })
                    ->nullable(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Painel de Controlo do Atendimento')
                    ->description('Detalhes críticos para a resolução do ticket.')
                    ->icon('heroicon-m-chat-bubble-bottom-center-text')
                    ->schema([
                        Infolists\Components\TextEntry::make('company.name')
                            ->label('Organização / Conta'),
                        Infolists\Components\TextEntry::make('client_name')
                            ->label('Nome do Contacto'),
                        Infolists\Components\TextEntry::make('client_phone')
                            ->label('Telefone / WhatsApp'),
                        Infolists\Components\TextEntry::make('channel.type')
                            ->label('Canal de Origem')
                            ->badge(),
                        Infolists\Components\TextEntry::make('status')
                            ->label('Estado Atual')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'open' => 'success',
                                'waiting' => 'warning',
                                'closed' => 'gray',
                                default => 'info',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'open' => 'Em Atendimento',
                                'waiting' => 'Aguardando Resposta',
                                'closed' => 'Resolvido / Fechado',
                                default => $state,
                            }),
                        Infolists\Components\TextEntry::make('assignee.name')
                            ->label('Responsável Atual'),
                    ])->columns(3),

                Infolists\Components\Section::make('NexIntelligence (IA)')
                    ->description('Análise proativa de intenção e sentimento do cliente.')
                    ->icon('heroicon-m-sparkles')
                    ->aside()
                    ->schema([
                        Infolists\Components\TextEntry::make('ai_score')
                            ->label('Score de Lead (Intenção)')
                            ->suffix('%')
                            ->weight('bold')
                            ->color(fn (int $state): string => match (true) {
                                $state >= 70 => 'success',
                                $state >= 40 => 'warning',
                                default => 'danger',
                            }),
                        Infolists\Components\TextEntry::make('ai_sentiment')
                            ->label('Sentimento Detetado')
                            ->badge()
                            ->color(fn (string|null $state): string => match ($state) {
                                'positive' => 'success',
                                'negative' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string|null $state): string => match ($state) {
                                'positive' => 'Positivo',
                                'negative' => 'Negativo',
                                default => 'Neutro',
                            }),
                        Infolists\Components\TextEntry::make('ai_summary')
                            ->label('Resumo Prévio da IA')
                            ->columnSpanFull()
                            ->prose(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('5s')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID Ticket')
                    ->sortable(),
                Tables\Columns\TextColumn::make('client_name')
                    ->label('Contacto')
                    ->searchable()
                    ->description(fn (Conversation $record) => $record->client_phone),
                Tables\Columns\TextColumn::make('company.name')
                    ->label('Organização')
                    ->toggleable(isToggledHiddenByDefault: fn () => ! auth()->user()?->isPlatformAdmin())
                    ->searchable(),
                Tables\Columns\TextColumn::make('channel.type')
                    ->label('Canal')
                    ->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'success',
                        'waiting' => 'warning',
                        'closed' => 'gray',
                        default => 'info',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'open' => 'Ativo',
                        'waiting' => 'Pendente',
                        'closed' => 'Fechado',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('ai_score')
                    ->label('Score IA')
                    ->numeric()
                    ->alignCenter()
                    ->suffix('%')
                    ->weight('bold')
                    ->color(fn (int $state): string => match (true) {
                        $state >= 70 => 'success',
                        $state >= 40 => 'warning',
                        default => 'danger',
                    })
                    ->description(fn (Conversation $record) => match ($record->ai_sentiment) {
                        'positive' => 'Positivo',
                        'negative' => 'Negativo',
                        default => 'Neutro',
                    }),
                Tables\Columns\TextColumn::make('assignee.name')
                    ->label('Atribuído a'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última Mensagem')
                    ->since()
                    ->sortable(),
            ])
            ->searchPlaceholder('Procurar ticket ou contacto...')
            ->defaultSort('updated_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'open' => 'Aberta',
                        'waiting' => 'Aguardando',
                        'closed' => 'Encerrada',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('spy')
                    ->label('Espiar')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->tooltip('Monitorar conversa sem intervir (Gera log de auditoria)')
                    ->action(function (Conversation $record) {
                        // Cumprindo regra de Governança do AGENTS.md: Log de Auditoria para Spy Mode
                        \App\Models\ActivityLog::create([
                            'user_id' => auth()->id(),
                            'company_id' => $record->company_id,
                            'event' => 'conversation_spy',
                            'description' => "O atendente " . auth()->user()->name . " iniciou o monitoramento da conversa #{$record->id}.",
                            'subject_type' => Conversation::class,
                            'subject_id' => $record->id,
                            'ip_address' => request()->ip(),
                            'user_agent' => request()->userAgent(),
                            'properties' => [
                                'client_name' => $record->client_name,
                                'channel' => $record->channel?->type,
                            ],
                        ]);

                        Notification::make()
                            ->title('Modo Espião Ativado')
                            ->body('Sua atividade de monitoramento foi registrada para fins de auditoria.')
                            ->info()
                            ->send();

                        return redirect(Pages\ViewConversation::getUrl(['record' => $record]));
                    }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('assume')
                    ->label('Assumir')
                    ->icon('heroicon-o-hand-raised')
                    ->requiresConfirmation()
                    ->visible(fn (Conversation $record) => $record->assignee_id === null)
                    ->action(fn (Conversation $record) => $record->update([
                        'assignee_id' => auth()->id(),
                        'status' => 'open',
                    ])),
                Tables\Actions\Action::make('close')
                    ->label('Encerrar')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->requiresConfirmation()
                    ->action(fn (Conversation $record) => $record->update([
                        'status' => 'closed',
                        'closed_at' => now(),
                    ])),
                Tables\Actions\Action::make('ai_suggest')
                    ->label('IA Sugerir')
                    ->icon('heroicon-o-sparkles')
                    ->color('info')
                    ->modalIcon('heroicon-o-magnifying-glass-circle')
                    ->modalHeading('Sugestão da Inteligência Artificial')
                    ->modalDescription('Baseado no histórico recente desta conversa.')
                    ->modalContent(function (Conversation $record, AiService $ai) {
                        $suggestion = $ai->suggestResponse($record);
                        return view('filament.components.ai-suggestion', [
                            'suggestion' => $suggestion,
                        ]);
                    })
                    ->modalSubmitActionLabel('Copiar Resposta')
                    ->action(function () {
                        Notification::make()
                            ->title('Sugestão copiada para o clipboard (Simulação)')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Nenhuma conversa')
            ->emptyStateDescription('As conversas do widget e dos canais aparecem aqui para gestão e atribuição.')
            ->emptyStateIcon('heroicon-o-chat-bubble-left-right');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\MessagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConversations::route('/'),
            'view' => Pages\ViewConversation::route('/{record}'),
            'edit' => Pages\EditConversation::route('/{record}/edit'),
        ];
    }
}
