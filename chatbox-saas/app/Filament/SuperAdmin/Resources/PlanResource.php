<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\PlanResource\Pages;
use App\Models\Plan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Plataforma';

    protected static ?string $modelLabel = 'Plano de Assinatura';

    protected static ?string $pluralModelLabel = 'Planos de Assinatura';

    protected static ?int $navigationSort = 1;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make()
                    ->schema([
                        Forms\Components\Section::make('Informações do plano')
                            ->icon('heroicon-o-rectangle-group')
                            ->description('Nome visível no site, identificador técnico e texto de apresentação.')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nome do plano')
                                    ->placeholder('Ex.: Profissional')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug((string) $state))),
                                Forms\Components\TextInput::make('slug')
                                    ->label('Identificador (slug)')
                                    ->placeholder('ex.: profissional')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Usado em URLs e integrações. Evite mudar em produção sem revisar integrações.'),
                                Forms\Components\Textarea::make('description')
                                    ->label('Descrição')
                                    ->placeholder('Resumo do que o plano inclui…')
                                    ->rows(4)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),

                        Forms\Components\Section::make('Preço e cobrança')
                            ->icon('heroicon-o-banknotes')
                            ->description('Valor cobrado e periodicidade da fatura.')
                            ->schema([
                                Forms\Components\TextInput::make('price')
                                    ->label('Valor Mensal')
                                    ->numeric()
                                    ->prefix('R$')
                                    ->placeholder('0,00')
                                    ->step(0.01)
                                    ->minValue(0)
                                    ->required(),
                                Forms\Components\TextInput::make('price_yearly')
                                    ->label('Valor Anual')
                                    ->numeric()
                                    ->prefix('R$')
                                    ->placeholder('0,00')
                                    ->step(0.01)
                                    ->minValue(0),
                                Forms\Components\Select::make('update_behavior')
                                    ->label('Regra de Assinatura (na alteração)')
                                    ->options([
                                        'keep_old' => 'Manter valor para clientes antigos',
                                        'apply_new' => 'Aplicar novo valor para todos',
                                        'apply_renewal' => 'Aplicar na próxima renovação',
                                    ])
                                    ->default('keep_old')
                                    ->required()
                                    ->native(false),

                            ])
                            ->columns(2),
                    ])
                    ->columns(['default' => 1, 'lg' => 2]),

                Forms\Components\Section::make('Limites do plano')
                    ->icon('heroicon-o-scale')
                    ->description('Quotas máximas por assinatura. Use números inteiros ≥ 0.')
                    ->schema([
                        Forms\Components\TextInput::make('max_users')
                            ->label('Membros no painel')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->default(2)
                            ->suffix('usuários')
                            ->helperText('Total de contas de utilizador permitidas.'),
                        Forms\Components\TextInput::make('max_attendants')
                            ->label('Atendentes')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->default(1)
                            ->suffix('agentes')
                            ->helperText('Utilizadores com perfil de atendimento ativo.'),
                        Forms\Components\TextInput::make('max_channels')
                            ->label('Canais')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->default(1)
                            ->suffix('canais'),
                        Forms\Components\TextInput::make('max_chatbots')
                            ->label('Chatbots')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->default(1)
                            ->suffix('bots'),
                        Forms\Components\TextInput::make('max_ai_conversations')
                            ->label('Conversas IA (Mês)')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->default(500)
                            ->suffix('conversas')
                            ->helperText('Franquia mensal de conversas geradas por IA.'),
                        Forms\Components\TextInput::make('max_messages')
                            ->label('Mensagens')
                            ->numeric()
                            ->integer()
                            ->default(-1)
                            ->helperText('-1 para ilimitado'),
                        Forms\Components\TextInput::make('max_integrations')
                            ->label('Integrações')
                            ->numeric()
                            ->integer()
                            ->default(-1)
                            ->helperText('-1 para ilimitado'),
                    ])
                    ->columns(['default' => 1, 'sm' => 2, 'lg' => 3])
                    ->collapsible(),

                Forms\Components\Section::make('Funcionalidades e destaque')
                    ->icon('heroicon-o-sparkles')
                    ->description('Lista para páginas de preços e opções de exibição.')
                    ->schema([
                        Forms\Components\TagsInput::make('features')
                            ->label('Funcionalidades em destaque')
                            ->placeholder('Escreva e pressione Enter…')
                            ->separator(',')
                            ->reorderable()
                            ->columnSpanFull(),
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('sort_order')
                                    ->label('Ordem')
                                    ->numeric()
                                    ->default(0),
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Plano ativo')
                                    ->helperText('Se desligado, novas subscrições não devem usar este plano.')
                                    ->default(true)
                                    ->inline(false),
                                Forms\Components\Toggle::make('is_popular')
                                    ->label('Marcar como popular')
                                    ->helperText('Destaque visual em listagens de planos, se a UI suportar.')
                                    ->default(false)
                                    ->inline(false),
                            ]),
                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\Toggle::make('has_advanced_customization')->label('Personalização Avançada')->inline(false),
                                Forms\Components\Toggle::make('has_quick_replies')->label('Quick Replies')->inline(false),
                                Forms\Components\Toggle::make('has_contextual_ai')->label('IA Contextual')->inline(false),
                                Forms\Components\Toggle::make('has_chatbot_faq')->label('FAQ no Chatbot')->inline(false),
                                Forms\Components\Toggle::make('has_whatsapp')->label('WhatsApp')->inline(false),
                                Forms\Components\Toggle::make('has_telegram')->label('Telegram')->inline(false),
                                Forms\Components\Toggle::make('has_instagram')->label('Instagram')->inline(false),
                                Forms\Components\Toggle::make('has_facebook')->label('Facebook')->inline(false),
                                Forms\Components\Toggle::make('has_webchat')->label('Webchat')->inline(false),
                                Forms\Components\Toggle::make('has_openai')->label('OpenAI')->inline(false),
                                Forms\Components\Toggle::make('has_rag')->label('RAG (IA)')->inline(false),
                                Forms\Components\Toggle::make('has_inbox')->label('Caixa de Entrada')->inline(false),
                                Forms\Components\Toggle::make('has_flow_builder')->label('Construtor de Fluxo')->inline(false),
                                Forms\Components\Toggle::make('has_api')->label('Acesso à API')->inline(false),
                                Forms\Components\Toggle::make('has_whitelabel')->label('White Label')->inline(false),
                                Forms\Components\Toggle::make('has_webhooks')->label('Webhooks')->inline(false),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Plano')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Mensal')
                    ->money('BRL')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_yearly')
                    ->label('Anual')
                    ->money('BRL')
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_attendants')
                    ->label('Atendentes')
                    ->numeric()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('max_users')
                    ->label('Membros')
                    ->numeric()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('max_ai_conversations')
                    ->label('Conversas IA')
                    ->numeric()
                    ->alignCenter(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_popular')
                    ->label('Popular')
                    ->boolean(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->sortable()
                    ->fontFamily('mono')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->searchPlaceholder('Pesquisar plano ou slug…')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Ativo'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ReplicateAction::make()
                    ->beforeReplicaSaved(function (\Illuminate\Database\Eloquent\Model $replica): void {
                        $replica->name = $replica->name . ' (Cópia)';
                        $replica->slug = $replica->slug . '-copia-' . time();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Nenhum plano')
            ->emptyStateDescription('Crie planos com limites e preços para aparecerem na página de assinatura.')
            ->emptyStateIcon('heroicon-o-credit-card');
    }

    public static function getRelations(): array
    {
        return [
            PlanResource\RelationManagers\AuditLogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlans::route('/'),
            'create' => Pages\CreatePlan::route('/create'),
            'edit' => Pages\EditPlan::route('/{record}/edit'),
        ];
    }
}
