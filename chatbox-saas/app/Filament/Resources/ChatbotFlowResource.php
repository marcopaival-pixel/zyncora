<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChatbotFlowResource\Pages;
use App\Models\ChatbotFlow;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ChatbotFlowResource extends Resource
{
    protected static ?string $model = ChatbotFlow::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationGroup = 'Automação';

    protected static ?string $modelLabel = 'Fluxo';

    protected static ?string $pluralModelLabel = 'Fluxos por palavra-chave';

    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->canManageIntegrations() ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->canManageIntegrations() ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['company']);
        $user = auth()->user();
        if ($user && ! $user->isPlatformAdmin()) {
            $query->where('company_id', $user->company_id);
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Gatilho de ativação')
                    ->description('Texto que o visitante envia para este fluxo ser escolhido. Vazio = fallback quando nada mais corresponde.')
                    ->icon('heroicon-o-bolt')
                    ->schema([
                        Forms\Components\Select::make('company_id')
                            ->relationship('company', 'name')
                            ->label('Empresa')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->visible(fn () => auth()->user()?->isPlatformAdmin() ?? false),
                        Forms\Components\TextInput::make('trigger')
                            ->label('Palavra-chave ou frase')
                            ->placeholder('Ex.: falar com atendente, comprar, suporte')
                            ->helperText('Sem valor, o fluxo pode ser usado como padrão (consoante a ordem de prioridade).')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Resposta e ação')
                    ->description('Mensagem enviada ao utilizador e comportamento opcional depois dela.')
                    ->icon('heroicon-o-chat-bubble-bottom-center-text')
                    ->schema([
                        Forms\Components\Textarea::make('answer')
                            ->label('Mensagem do bot')
                            ->placeholder('Ex.: Olá! Como posso ajudar?')
                            ->rows(4)
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('action')
                                    ->label('Ação interna (opcional)')
                                    ->options([
                                        'menu' => 'Menu principal',
                                        'transfer' => 'Transferir para humano',
                                        'end' => 'Encerrar atendimento',
                                    ])
                                    ->placeholder('Nenhuma')
                                    ->native(false)
                                    ->helperText('Passo de sistema após enviar a mensagem.'),

                                Forms\Components\TextInput::make('next_flow_key')
                                    ->label('Próximo gatilho (opcional)')
                                    ->placeholder('ID ou trigger do fluxo seguinte')
                                    ->helperText('Encadeia outro fluxo pelo mesmo identificador de gatilho.'),
                            ]),
                    ]),

                Forms\Components\Section::make('Configurações avançadas')
                    ->description('Ativação, prioridade de avaliação e ordem entre fluxos.')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->collapsed()
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\Toggle::make('active')
                                ->label('Fluxo Ativo')
                                ->default(true),
                            Forms\Components\TextInput::make('sort_order')
                                ->label('Ordem de avaliação')
                                ->numeric()
                                ->default(0)
                                ->helperText('Menor número = avaliado primeiro quando há vários fluxos.'),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company.name')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->visible(fn (): bool => auth()->user()?->isPlatformAdmin() ?? false),

                Tables\Columns\TextColumn::make('trigger')
                    ->label('Gatilho')
                    ->searchable()
                    ->placeholder('— fallback')
                    ->description(fn (ChatbotFlow $record): ?string => filled($record->trigger) ? null : 'Sem palavra-chave definida')
                    ->color(fn (ChatbotFlow $record): string => filled($record->trigger) ? 'gray' : 'warning'),

                Tables\Columns\TextColumn::make('answer')
                    ->label('Pré-visualização da resposta')
                    ->limit(50)
                    ->wrap()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('action')
                    ->label('Ação')
                    ->badge()
                    ->placeholder('—')
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('active')
                    ->label('Ativo')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-pause-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Ordem')
                    ->sortable()
                    ->alignCenter(),
            ])
            ->defaultSort('sort_order')
            ->striped()
            ->poll('60s')
            ->emptyStateHeading('Nenhum fluxo configurado')
            ->emptyStateDescription('Crie gatilhos com palavras-chave ou um fluxo de fallback para orientar o visitante.')
            ->emptyStateIcon('heroicon-o-bolt')
            ->filters([
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Estado')
                    ->placeholder('Todos')
                    ->trueLabel('Ativos')
                    ->falseLabel('Inativos'),
                Tables\Filters\SelectFilter::make('action')
                    ->label('Ação interna')
                    ->options([
                        'menu' => 'Menu principal',
                        'transfer' => 'Transferir para humano',
                        'end' => 'Encerrar atendimento',
                    ])
                    ->placeholder('Qualquer'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChatbotFlows::route('/'),
            'create' => Pages\CreateChatbotFlow::route('/create'),
            'edit' => Pages\EditChatbotFlow::route('/{record}/edit'),
        ];
    }
}
