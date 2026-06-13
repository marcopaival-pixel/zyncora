<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChatbotResource\Pages;
use App\Filament\Resources\ChatbotResource\RelationManagers;
use App\Helpers\SegmentHelper;
use App\Models\Chatbot;
use App\Services\AgentPersonalizationService;
use App\Services\PlanService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class ChatbotResource extends Resource
{
    protected static ?string $model = Chatbot::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'Atendimento';

    protected static ?string $navigationLabel = 'Chatbots';

    protected static ?string $modelLabel = 'Chatbot';

    protected static ?string $pluralModelLabel = 'Chatbots';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 1;

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'whatsapp_phone', 'company.name'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        $statusLabel = match ($record->status) {
            Chatbot::STATUS_INCOMPLETE => 'Configuração Incompleta',
            Chatbot::STATUS_CONFIGURING => 'Em Configuração',
            Chatbot::STATUS_READY => 'Pronto para Publicar',
            Chatbot::STATUS_PUBLISHED => 'Publicado',
            Chatbot::STATUS_ACTIVE => 'Ativo',
            Chatbot::STATUS_PAUSED => 'Pausado',
            default => 'Inativo',
        };

        return $record->name.' ('.$statusLabel.')';
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Empresa' => $record->company?->name ?? 'N/A',
            'Canal' => $record->channel?->type ?? $record->default_channel ?? 'N/A',
            'Telefone' => $record->whatsapp_phone ?? 'N/A',
        ];
    }

    /** Linha secundária na lista (canal + origem lógica). */
    public static function formatChatbotListContext(Chatbot $record): ?string
    {
        $parts = [];
        if ($record->channel?->type) {
            $parts[] = 'Canal: '.$record->channel->type;
        }
        if (filled($record->default_channel)) {
            $parts[] = match ($record->default_channel) {
                'whatsapp' => 'Origem: WhatsApp',
                'site' => 'Origem: Site',
                'internal' => 'Origem: Sistema',
                default => 'Origem: '.$record->default_channel,
            };
        }

        return $parts !== [] ? implode(' · ', $parts) : null;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasPermission('view_chatbot') ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission('view_chatbot') ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['company', 'channel']);
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
                Forms\Components\Tabs::make('ChatbotTabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Geral')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Forms\Components\Section::make('Identidade e ligação')
                                    ->description('Nome do bot, empresa e canal padrão de entrada.')
                                    ->icon('heroicon-o-sparkles')
                                    ->schema([
                                        Forms\Components\Select::make('company_id')
                                            ->relationship('company', 'name')
                                            ->label('Empresa')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->visible(fn () => auth()->user()?->isPlatformAdmin() ?? false),
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nome do chatbot')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('Ex.: Atendimento comercial'),
                                    ])
                                    ->columns(2),

                                Forms\Components\Section::make('Estado')
                                    ->icon('heroicon-o-adjustments-horizontal')
                                    ->schema([
                                        Forms\Components\Select::make('status')
                                            ->label('Estado')
                                            ->options([
                                                Chatbot::STATUS_INCOMPLETE => 'Configuração Incompleta',
                                                Chatbot::STATUS_CONFIGURING => 'Em Configuração',
                                                Chatbot::STATUS_READY => 'Pronto para Publicar',
                                                Chatbot::STATUS_PUBLISHED => 'Publicado',
                                                Chatbot::STATUS_ACTIVE => 'Ativo',
                                                Chatbot::STATUS_PAUSED => 'Pausado',
                                            ])
                                            ->default(Chatbot::STATUS_INCOMPLETE)
                                            ->required()
                                            ->native(true),
                                    ]),

                            ]),

                        Forms\Components\Tabs\Tab::make('Canais e Atendimento')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->schema([
                                Forms\Components\Section::make('Canais de Entrada')
                                    ->description('Configure por onde este bot irá atender os clientes.')
                                    ->icon('heroicon-o-globe-alt')
                                    ->schema([
                                        Forms\Components\Select::make('channel_id')
                                            ->relationship('channel', 'type')
                                            ->label('Canal preferencial (Integração)')
                                            ->searchable()
                                            ->preload()
                                            ->nullable()
                                            ->helperText('Opcional: associe a um canal já criado em Integrações.'),
                                        Forms\Components\Select::make('default_channel')
                                            ->label('Canal lógico padrão')
                                            ->options([
                                                'whatsapp' => 'WhatsApp',
                                                'site' => 'Site',
                                                'internal' => 'Sistema',
                                                'instagram' => 'Instagram',
                                                'messenger' => 'Facebook Messenger',
                                                'telegram' => 'Telegram',
                                                'api' => 'API Externa',
                                            ])
                                            ->default('site')
                                            ->required()
                                            ->native(true),
                                        Forms\Components\TextInput::make('whatsapp_phone')
                                            ->label('Telefone WhatsApp (E.164)')
                                            ->maxLength(64)
                                            ->placeholder('+351...')
                                            ->columnSpanFull(),
                                    ])->columns(2),

                                Forms\Components\Section::make('Primeira impressão e horário')
                                    ->description('Mensagem inicial e janela de atendimento.')
                                    ->icon('heroicon-o-clock')
                                    ->schema([
                                        Forms\Components\Textarea::make('initial_message')
                                            ->label('Mensagem inicial')
                                            ->rows(4)
                                            ->columnSpanFull(),
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TimePicker::make('hours_start')
                                                    ->label('Início')
                                                    ->seconds(false),
                                                Forms\Components\TimePicker::make('hours_end')
                                                    ->label('Fim')
                                                    ->seconds(false),
                                            ]),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Inteligência Artificial')
                            ->icon('heroicon-o-cpu-chip')
                            ->schema([
                                Forms\Components\Section::make('Cérebro da IA')
                                    ->description('Configurações para respostas automáticas baseadas em IA.')
                                    ->icon('heroicon-o-bolt')
                                    ->schema([
                                        Forms\Components\Toggle::make('use_ai')
                                            ->label('Ativar Cérebro de IA')
                                            ->helperText(function (?Chatbot $record = null) {
                                                if ($record && $record->company) {
                                                    $planService = app(PlanService::class);

                                                    if (! $planService->hasFeature($record->company, 'ai_automation')) {
                                                        return '⚠️ Funcionalidade não disponível no plano '.strtoupper($record->company->plan).'. Faça o upgrade.';
                                                    }

                                                    if (! $planService->hasAiCredits($record->company)) {
                                                        return '⛔ Seus créditos de IA esgotaram! Adquira mais créditos para continuar usando.';
                                                    }

                                                    $usage = $record->company->ai_credits_used;
                                                    $balance = $record->company->ai_credits_balance;
                                                    $percentage = $balance > 0 ? ($usage / $balance) * 100 : 100;

                                                    if ($percentage >= 80) {
                                                        return '⚠️ Atenção: Você já usou '.number_format($percentage, 0)."% dos seus créditos de IA ({$usage}/{$balance}).";
                                                    }
                                                }

                                                return 'Se ativado, o bot responderá usando a base de conhecimento (Consome 1 crédito de IA por resposta).';
                                            })
                                            ->disabled(function (?Chatbot $record = null) {
                                                return $record && $record->company && ! app(PlanService::class)->canUseAi($record->company);
                                            })
                                            ->default(false),

                                        Forms\Components\Textarea::make('ai_instruction')
                                            ->label('Personalidade do Robô (System Prompt)')
                                            ->placeholder('Ex: Você é o atendente virtual da empresa. Seja brincalhão e prestativo.')
                                            ->rows(4)
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        Forms\Components\Tabs\Tab::make('Aparência')
                            ->icon('heroicon-o-paint-brush')
                            ->schema([
                                Forms\Components\Section::make('Identidade Visual')
                                    ->description('Cores e avatar específicos para este bot.')
                                    ->schema([
                                        Forms\Components\Select::make('avatar_type')
                                            ->label('Tipo de Avatar')
                                            ->options([
                                                'default' => 'Padrão do Sistema',
                                                'company' => 'Logo da Empresa',
                                                'ai' => 'Ícone de IA',
                                                'custom' => 'Imagem Personalizada',
                                            ])
                                            ->default('default')
                                            ->live()
                                            ->native(true),
                                        Forms\Components\FileUpload::make('avatar_path')
                                            ->label('Avatar Personalizado')
                                            ->disk('public')
                                            ->directory('avatars')
                                            ->image()
                                            ->visible(fn (Forms\Get $get) => $get('avatar_type') === 'custom')
                                            ->columnSpanFull(),
                                        Forms\Components\Grid::make(4)
                                            ->schema([
                                                Forms\Components\ColorPicker::make('primary_color')
                                                    ->label('Cor Primária'),
                                                Forms\Components\ColorPicker::make('secondary_color')
                                                    ->label('Cor Secundária'),
                                                Forms\Components\ColorPicker::make('header_color')
                                                    ->label('Cor do Cabeçalho'),
                                                Forms\Components\ColorPicker::make('message_color')
                                                    ->label('Cor das Mensagens'),
                                            ]),
                                    ]),
                                Forms\Components\Section::make('Mascote Interativo (Flutuante)')
                                    ->description('Configure um boneco 3D para ficar ao lado do chat convidando os usuários.')
                                    ->schema([
                                        Forms\Components\Radio::make('mascot_type')
                                            ->label('Escolha o Mascote')
                                            ->options([
                                                'none' => 'Nenhum',
                                                'man_1' => 'Homem (Casual)',
                                                'woman_1' => 'Mulher (Profissional)',
                                                'man_2' => 'Homem (Dreads e Moletom)',
                                                'woman_2' => 'Mulher (Hijab)',
                                                'robot' => 'Robô Futurista',
                                            ])
                                            ->default('none')
                                            ->live()
                                            ->columns(3)
                                            ->disabled(function (?Chatbot $record = null) {
                                                if (! $record || ! $record->company) {
                                                    return false;
                                                } // Allow on create maybe?

                                                return ! app(PlanService::class)->hasFeature($record->company, 'custom_mascot');
                                            })
                                            ->helperText(function (?Chatbot $record = null) {
                                                if ($record && $record->company && ! app(PlanService::class)->hasFeature($record->company, 'custom_mascot')) {
                                                    return '🔒 Recurso Premium: Faça o upgrade de plano para desbloquear Mascotes 3D Interativos na sua página!';
                                                }

                                                return '';
                                            }),
                                        Forms\Components\Placeholder::make('mascot_preview')
                                            ->label('Pré-visualização')
                                            ->content(function (Forms\Get $get) {
                                                $type = $get('mascot_type');
                                                if ($type && $type !== 'none') {
                                                    return new HtmlString('<img src="/images/mascots/'.$type.'.png" style="max-height: 200px; object-fit: contain; border-radius: 12px; background: #f3f4f6; padding: 10px; border: 1px solid #e5e7eb; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">');
                                                }

                                                return '-';
                                            })
                                            ->visible(fn (Forms\Get $get) => $get('mascot_type') !== 'none'),
                                        Forms\Components\TextInput::make('mascot_greeting')
                                            ->label('Frase do Balãozinho')
                                            ->placeholder('Ex: Posso Ajudar?')
                                            ->maxLength(255)
                                            ->visible(fn (Forms\Get $get) => $get('mascot_type') !== 'none'),
                                    ]),
                            ]),
                        Forms\Components\Tabs\Tab::make('Menu e Ações')
                            ->icon('heroicon-o-squares-2x2')
                            ->schema([
                                Forms\Components\Section::make('Sugestões Rápidas (Quick Replies)')
                                    ->description('Configure os botões que aparecerão para o usuário acima do campo de digitação ou no menu fixo.')
                                    ->schema([
                                        Forms\Components\Toggle::make('is_menu_enabled')
                                            ->label('Ativar Menu Fixo Inferior')
                                            ->default(false),
                                        Forms\Components\Repeater::make('actionCards')
                                            ->relationship()
                                            ->label('Ações Rápidas')
                                            ->schema([
                                                Forms\Components\TextInput::make('title')
                                                    ->label('Título (Ex: Agendar)')
                                                    ->required()
                                                    ->maxLength(255),
                                                Forms\Components\TextInput::make('icon')
                                                    ->label('Ícone (Emoji ou classe CSS)')
                                                    ->maxLength(255),
                                                Forms\Components\Select::make('action_type')
                                                    ->label('Tipo de Ação')
                                                    ->options([
                                                        'text_reply' => 'Enviar Mensagem',
                                                        'link' => 'Abrir Link',
                                                        'faq' => 'Abrir FAQ',
                                                        'flow' => 'Iniciar Fluxo',
                                                    ])
                                                    ->default('text_reply')
                                                    ->required()
                                                    ->native(true),
                                                Forms\Components\TextInput::make('action_payload')
                                                    ->label('Ação / URL / Texto')
                                                    ->maxLength(255),
                                                Forms\Components\Toggle::make('is_active')
                                                    ->label('Ativo')
                                                    ->default(true),
                                            ])
                                            ->orderColumn('order_column')
                                            ->collapsible()
                                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                                            ->defaultItems(0)
                                            ->columnSpanFull()
                                            ->addAction(
                                                fn (Forms\Components\Actions\Action $action) => $action->label('Adicionar Sugestão')
                                            ),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
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
                    ->weight('medium')
                    ->toggleable()
                    ->visible(fn (): bool => auth()->user()?->isPlatformAdmin() ?? false),

                Stack::make([
                    Tables\Columns\TextColumn::make('name')
                        ->label('Chatbot')
                        ->searchable()
                        ->sortable()
                        ->weight('semibold')
                        ->icon('heroicon-o-sparkles')
                        ->iconColor('primary')
                        ->description(fn (Chatbot $record): ?string => static::formatChatbotListContext($record)),

                    Tables\Columns\TextColumn::make('whatsapp_phone')
                        ->label('WhatsApp (E.164)')
                        ->searchable()
                        ->placeholder('—')
                        ->copyable()
                        ->copyMessage('Número copiado')
                        ->icon('heroicon-o-phone')
                        ->color('gray')
                        ->size('sm'),
                ])
                    ->space(2),

                Tables\Columns\TextColumn::make('channel.type')
                    ->label('Integração')
                    ->badge()
                    ->placeholder('—')
                    ->color('gray')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('default_channel')
                    ->label('Origem')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'whatsapp' => 'WhatsApp',
                        'site' => 'Site / widget',
                        'internal' => 'Interno',
                        'instagram' => 'Instagram',
                        'messenger' => 'Messenger',
                        'telegram' => 'Telegram',
                        'api' => 'API',
                        default => $state ?? '—',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'whatsapp' => 'success',
                        'site' => 'info',
                        'internal' => 'warning',
                        'instagram' => 'danger',
                        'messenger' => 'primary',
                        'telegram' => 'info',
                        'api' => 'gray',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        Chatbot::STATUS_ACTIVE => 'success',
                        Chatbot::STATUS_PUBLISHED => 'info',
                        Chatbot::STATUS_READY => 'primary',
                        Chatbot::STATUS_CONFIGURING => 'warning',
                        Chatbot::STATUS_PAUSED => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Chatbot::STATUS_INCOMPLETE => 'Configuração Incompleta',
                        Chatbot::STATUS_CONFIGURING => 'Em Configuração',
                        Chatbot::STATUS_READY => 'Pronto para Publicar',
                        Chatbot::STATUS_PUBLISHED => 'Publicado',
                        Chatbot::STATUS_ACTIVE => 'Ativo',
                        Chatbot::STATUS_PAUSED => 'Pausado',
                        default => $state,
                    }),

                Tables\Columns\IconColumn::make('use_ai')
                    ->label('IA')
                    ->boolean()
                    ->trueIcon('heroicon-o-cpu-chip')
                    ->falseIcon('heroicon-o-minus-small')
                    ->trueColor('success')
                    ->alignCenter()
                    ->tooltip(fn (Chatbot $record): string => $record->use_ai ? 'Respostas com base de conhecimento' : 'Sem IA')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado')
                    ->since()
                    ->sortable()
                    ->tooltip(fn (Chatbot $record): ?string => $record->updated_at?->format('d/m/Y H:i')),
            ])
            ->recordUrl(fn (Chatbot $record): string => static::getUrl('edit', ['record' => $record]))
            ->defaultSort('updated_at', 'desc')
            ->searchPlaceholder('Pesquisar por nome, WhatsApp ou empresa…')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        Chatbot::STATUS_INCOMPLETE => 'Configuração Incompleta',
                        Chatbot::STATUS_CONFIGURING => 'Em Configuração',
                        Chatbot::STATUS_READY => 'Pronto para Publicar',
                        Chatbot::STATUS_PUBLISHED => 'Publicado',
                        Chatbot::STATUS_ACTIVE => 'Ativo',
                        Chatbot::STATUS_PAUSED => 'Pausado',
                    ]),
                Tables\Filters\TernaryFilter::make('use_ai')
                    ->label('Base de conhecimento / IA')
                    ->placeholder('Todos')
                    ->trueLabel('Com IA')
                    ->falseLabel('Sem IA'),
                Tables\Filters\SelectFilter::make('company_id')
                    ->label('Organização / Conta')
                    ->indicator('Empresa')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload()
                    ->native(true)
                    ->visible(fn () => auth()->user()?->isPlatformAdmin() ?? false),
            ])
            ->actions([
                Tables\Actions\Action::make('setup_ai')
                    ->label('IA Setup')
                    ->icon('heroicon-o-sparkles')
                    ->color('primary')
                    ->iconButton()
                    ->tooltip('Configuração Inicial Assistida por IA')
                    ->form([
                        Forms\Components\Select::make('chatbot_objective')
                            ->label('Objetivo do assistente')
                            ->options([
                                'vendas' => 'Vendas e Conversão',
                                'suporte' => 'Suporte ao Cliente / SAC',
                                'agendamento' => 'Agendamentos / Reservas',
                                'captacao' => 'Captação de Leads',
                                'informacao' => 'Tirar Dúvidas / Informações',
                            ])
                            ->required()
                            ->default('suporte'),
                        Forms\Components\Select::make('chatbot_segment')
                            ->label('Segmento Específico')
                            ->options(SegmentHelper::getSecondarySegments())
                            ->searchable()
                            ->helperText('Selecione o segmento do chatbot. Se vazio, usará o segmento da empresa.'),
                        Forms\Components\CheckboxList::make('chatbot_channels')
                            ->label('Canais')
                            ->options([
                                'site' => 'Site / WebChat',
                                'whatsapp' => 'WhatsApp',
                                'instagram' => 'Instagram',
                                'facebook' => 'Facebook Messenger',
                                'telegram' => 'Telegram',
                            ])
                            ->required()
                            ->default(['site'])
                            ->columns(2),
                    ])
                    ->action(function (Chatbot $record, array $data, AgentPersonalizationService $service) {
                        $objective = $data['chatbot_objective'];
                        $channels = $data['chatbot_channels'];
                        $segment = $data['chatbot_segment'] ?? $record->company->segment ?? 'Outro Segmento';
                        $service->generateForSegment($record->company, $record, $segment, $objective, $channels);
                        Notification::make()
                            ->title('Configuração IA concluída!')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Configuração Assistida por IA')
                    ->modalDescription('Essa ação irá gerar uma configuração inicial inteligente (FAQ, fluxos e prompt) alinhada ao objetivo. Isso não apagará os dados atuais, apenas adicionará novos.'),
                Tables\Actions\Action::make('test')
                    ->label('Testar')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->iconButton()
                    ->tooltip('Abrir consola de teste')
                    ->url(fn (Chatbot $record): string => static::getUrl('test', ['record' => $record])),
                Tables\Actions\Action::make('builder')
                    ->label('Fluxo')
                    ->icon('heroicon-o-rectangle-group')
                    ->color('warning')
                    ->iconButton()
                    ->tooltip('Editor de fluxo visual')
                    ->url(fn (Chatbot $record): string => static::getUrl('builder', ['record' => $record])),
                Tables\Actions\EditAction::make()
                    ->label('Editar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Nenhum chatbot configurado')
            ->emptyStateDescription('Crie o primeiro assistente para ligar ao widget, WhatsApp ou fluxos automáticos. Use «Testar» e «Fluxo» assim que existir um registo.')
            ->emptyStateIcon('heroicon-o-sparkles')
            ->emptyStateActions([
                Tables\Actions\Action::make('createFromEmpty')
                    ->label('Criar chatbot')
                    ->icon('heroicon-o-plus')
                    ->url(static::getUrl('create'))
                    ->button(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ScriptStepsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChatbots::route('/'),
            'create' => Pages\CreateChatbot::route('/create'),
            'edit' => Pages\EditChatbot::route('/{record}/edit'),
            'builder' => Pages\FlowBuilder::route('/{record}/builder'),
            'test' => Pages\TestChat::route('/{record}/test'),
        ];
    }
}
