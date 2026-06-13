<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\CompanyResource\Pages;
use App\Models\Company;
use App\Models\ImpersonationLog;
use App\Notifications\CompanyImpersonatedNotification;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\ColorEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\Tabs\Tab;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationGroup = 'Configurações de Acesso';

    protected static ?string $modelLabel = 'Organização / Conta';

    protected static ?string $pluralModelLabel = 'Organizações e Contas';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'slug', 'cnpj', 'email', 'responsible_name'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->name.' ('.($record->status === 'active' ? 'Ativa' : ucfirst($record->status)).')';
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Email' => $record->email ?? 'N/A',
            'Plano' => $record->plan?->name ?? $record->plan ?? 'N/A',
            'Conversas IA' => number_format((float) $record->ai_credits_used, 0, ',', '.').' usadas',
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isPlatformAdmin() ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount(['users', 'chatbots']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Dados Gerais')
                            ->icon('heroicon-m-identification')
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('name')
                                        ->label('Nome Fantasia')
                                        ->placeholder('Ex: Google DeepMind')
                                        ->required()
                                        ->maxLength(255)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug((string) $state))),
                                    Forms\Components\TextInput::make('slug')
                                        ->label('Identificador Unique (Slug)')
                                        ->required()
                                        ->maxLength(255)
                                        ->unique(ignoreRecord: true)
                                        ->helperText('Identificador único para webhooks e integrações.'),
                                    Forms\Components\TextInput::make('custom_domain')
                                        ->label('Domínio Personalizado (White Label)')
                                        ->maxLength(255)
                                        ->unique(ignoreRecord: true)
                                        ->placeholder('Ex: painel.minhaagencia.com')
                                        ->helperText('Apenas para planos Enterprise.'),
                                    Forms\Components\TextInput::make('legal_name')
                                        ->label('Razão Social')
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('cnpj')
                                        ->label('Identificação Fiscal (CNPJ)')
                                        ->maxLength(32),
                                    Forms\Components\TextInput::make('responsible_name')
                                        ->label('Nome do Responsável')
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('email')
                                        ->label('E-mail Administrativo')
                                        ->email(),
                                    Forms\Components\TextInput::make('phone')
                                        ->label('Contacto Geral / Telefone Principal')
                                        ->maxLength(64),
                                ]),
                                Forms\Components\FileUpload::make('logo_path')
                                    ->label('Logótipo da Empresa')
                                    ->disk('public')
                                    ->directory('logos')
                                    ->image()
                                    ->imageEditor()
                                    ->nullable()
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Tabs\Tab::make('Assinatura & Limites')
                            ->icon('heroicon-m-credit-card')
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\Select::make('status')
                                        ->label('Estado da Conta')
                                        ->options([
                                            'active' => 'Ativa',
                                            'trial' => 'Em Trial',
                                            'suspended' => 'Suspensa/Bloqueada',
                                            'expired' => 'Expirada',
                                            'canceled' => 'Cancelada',
                                        ])
                                        ->default('active')
                                        ->required()
                                        ->native(false),
                                    Forms\Components\Select::make('plan_id')
                                        ->label('Plano SaaS')
                                        ->relationship('plan', 'name')
                                        ->native(false),
                                    Forms\Components\TextInput::make('plan')
                                        ->label('Nível do Plano (Legado)')
                                        ->disabled(),
                                    Forms\Components\DateTimePicker::make('expires_at')
                                        ->label('Válido até (Vencimento)')
                                        ->nullable()
                                        ->native(false),
                                    Forms\Components\DateTimePicker::make('trial_end_at')
                                        ->label('Fim do Trial')
                                        ->nullable()
                                        ->native(false),
                                ]),
                                Forms\Components\Fieldset::make('Limites Manuais')
                                    ->schema([
                                        Forms\Components\TextInput::make('max_users')
                                            ->label('Membros')
                                            ->numeric()
                                            ->default(2),
                                        Forms\Components\TextInput::make('max_attendants')
                                            ->label('Atendentes')
                                            ->numeric()
                                            ->default(1),
                                        Forms\Components\TextInput::make('max_channels')
                                            ->label('Canais')
                                            ->numeric()
                                            ->default(1),
                                        Forms\Components\TextInput::make('max_chatbots')
                                            ->label('Chatbots')
                                            ->numeric()
                                            ->default(1),
                                        Forms\Components\TextInput::make('ai_credits_balance')
                                            ->label('Saldo de Conversas IA')
                                            ->numeric()
                                            ->default(0),
                                    ])->columns(3),
                                Forms\Components\Fieldset::make('Features Premium')
                                    ->schema([
                                        Forms\Components\Toggle::make('has_advanced_customization')
                                            ->label('Personalização Avançada')
                                            ->default(false)
                                            ->inline(false),
                                        Forms\Components\Toggle::make('has_quick_replies')
                                            ->label('Quick Replies')
                                            ->default(false)
                                            ->inline(false),
                                        Forms\Components\Toggle::make('has_contextual_ai')
                                            ->label('IA Contextual')
                                            ->default(false)
                                            ->inline(false),
                                        Forms\Components\Toggle::make('has_chatbot_faq')
                                            ->label('FAQ no Chatbot')
                                            ->default(false)
                                            ->inline(false),
                                    ])->columns(4),
                            ]),

                        Forms\Components\Tabs\Tab::make('Personalização')
                            ->icon('heroicon-m-paint-brush')
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\ColorPicker::make('chat_color')
                                        ->label('Cor de Identidade (Chat)')
                                        ->default('#0ea5e9'),
                                    Forms\Components\ColorPicker::make('brand_color')
                                        ->label('Cor do Painel (Brand Color)')
                                        ->helperText('Injeta esta cor em componentes chave do dashboard.')
                                        ->default('#8b5cf6'),
                                ]),
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\FileUpload::make('favicon_path')
                                        ->label('Favicon (32x32)')
                                        ->disk('public')
                                        ->directory('branding')
                                        ->image()
                                        ->nullable(),
                                    Forms\Components\FileUpload::make('panel_logo_path')
                                        ->label('Logo do Painel (Escuro)')
                                        ->disk('public')
                                        ->directory('branding')
                                        ->image()
                                        ->nullable(),
                                ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Chat e Horários')
                            ->icon('heroicon-m-chat-bubble-left-right')
                            ->schema([
                                Forms\Components\Toggle::make('auto_reply_enabled')
                                    ->label('Habilitar Auto-Resposta')
                                    ->default(true),
                                Forms\Components\Textarea::make('welcome_message')
                                    ->label('Mensagem de Boas-vindas')
                                    ->rows(3)
                                    ->columnSpanFull(),
                                Forms\Components\Textarea::make('offline_message')
                                    ->label('Mensagem Offline')
                                    ->rows(2)
                                    ->columnSpanFull()
                                    ->helperText('Exibida quando fora do horário configurado.'),
                                Forms\Components\Textarea::make('business_hours')
                                    ->label('Horário Comercial (JSON)')
                                    ->rows(6)
                                    ->columnSpanFull()
                                    ->helperText('JSON opcional. Ex.: {"monday":{"start":"09:00","end":"18:00"}}')
                                    ->formatStateUsing(fn ($state) => is_array($state) && $state !== []
                                        ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                                        : '')
                                    ->dehydrateStateUsing(function ($state) {
                                        $state = trim((string) $state);
                                        if ($state === '') {
                                            return null;
                                        }
                                        $decoded = json_decode($state, true);

                                        return is_array($decoded) ? $decoded : null;
                                    }),
                            ]),

                        Forms\Components\Tabs\Tab::make('Instalação (Widget)')
                            ->icon('heroicon-m-code-bracket')
                            ->hiddenOn('create')
                            ->schema([
                                Forms\Components\TextInput::make('widget_script')
                                    ->label('Código de Incorporação')
                                    ->readonly()
                                    ->formatStateUsing(function (?Company $record) {
                                        if (! $record) {
                                            return '';
                                        }
                                        $baseUrl = config('app.url');

                                        return "<script src=\"{$baseUrl}/widget/chatbox-widget.js\" data-slug=\"{$record->slug}\" data-api=\"{$baseUrl}/api\" defer></script>";
                                    })
                                    ->extraAttributes([
                                        'onclick' => "this.select(); document.execCommand('copy'); window.alert('Copiado para a área de transferência!');",
                                    ])
                                    ->helperText('Clique no campo para copiar o código e insira-o antes do fechamento da tag </body> do seu site.')
                                    ->columnSpanFull(),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo_path')
                    ->label('Logo')
                    ->circular()
                    ->defaultImageUrl(fn (Company $record) => 'https://ui-avatars.com/api/?name='.urlencode($record->name).'&color=FFFFFF&background=09090b'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome Fantasia')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Company $record) => $record->slug),
                Tables\Columns\TextColumn::make('legal_name')
                    ->label('Razão Social')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail / Resp.')
                    ->searchable()
                    ->description(fn (Company $record) => $record->responsible_name)
                    ->icon('heroicon-m-envelope'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data Cadastro')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('users_count')
                    ->label('Usuários')
                    ->counts('users')
                    ->sortable(),
                Tables\Columns\TextColumn::make('chatbots_count')
                    ->label('Chatbots')
                    ->counts('chatbots')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ai_credits_used')
                    ->label('Consumo IA')
                    ->numeric()
                    ->sortable()
                    ->description('conversas IA'),
                Tables\Columns\IconColumn::make('is_onboarding_completed')
                    ->label('Onboarding')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('plan')
                    ->label('Plano')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'basic' => 'gray',
                        'pro', 'professional' => 'primary',
                        'enterprise' => 'amber',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower(trim($state))) {
                        'active', 'ativa' => 'success',
                        'trial' => 'info',
                        'suspended', 'suspensa', 'desativada', 'inativa', 'blocked' => 'danger',
                        'expired', 'expirada' => 'warning',
                        'canceled', 'cancelada' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match (strtolower(trim($state))) {
                        'active', 'ativa' => 'Ativa',
                        'trial' => 'Trial',
                        'suspended', 'suspensa', 'desativada', 'inativa', 'blocked' => 'Bloqueada',
                        'expired', 'expirada' => 'Expirada',
                        'canceled', 'cancelada' => 'Cancelada',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('health_status')
                    ->label('Saúde')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'saudável' => 'success',
                        'atenção' => 'warning',
                        'risco' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => ucfirst($state)),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Vencimento')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('plan.monthly_price')
                    ->label('Valor Assinatura')
                    ->money('BRL')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('users.last_login_at')
                    ->label('Último Login')
                    ->getStateUsing(function (Company $record) {
                        $lastLogin = $record->users()->max('last_login_at');

                        return $lastLogin ? Carbon::parse($lastLogin)->format('d/m/Y H:i') : 'Nunca';
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'active' => 'Ativa',
                        'trial' => 'Em Trial',
                        'suspended' => 'Bloqueada',
                        'expired' => 'Expirada',
                        'canceled' => 'Cancelada',
                    ])
                    ->native(false),
                Tables\Filters\SelectFilter::make('plan')
                    ->label('Plano')
                    ->options([
                        'basic' => 'Básico',
                        'pro' => 'Profissional',
                        'enterprise' => 'Empresarial',
                    ])
                    ->native(false),
                Tables\Filters\Filter::make('ai_credits_used')
                    ->form([
                        Forms\Components\TextInput::make('consumo_minimo')
                            ->label('Consumo IA Mínimo')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['consumo_minimo'],
                                fn (Builder $query, $consumo): Builder => $query->where('ai_credits_used', '>=', $consumo),
                            );
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->searchPlaceholder('Pesquisar organização...')
            ->emptyStateHeading('Nenhuma organização')
            ->emptyStateDescription('Crie uma conta de cliente para isolar dados, utilizadores e canais.')
            ->emptyStateIcon('heroicon-o-building-office-2')
            ->actions([
                Tables\Actions\ViewAction::make()->label('Visão 360')->iconButton(),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()->label('Editar Dados'),

                    Tables\Actions\Action::make('alterar_plano')
                        ->label('Alterar Plano')
                        ->icon('heroicon-m-credit-card')
                        ->form([
                            Forms\Components\Select::make('plan_id')
                                ->label('Novo Plano')
                                ->relationship('plan', 'name')
                                ->required()
                                ->native(false),
                        ])
                        ->action(function (Company $record, array $data) {
                            $record->update(['plan_id' => $data['plan_id']]);
                        }),

                    Tables\Actions\Action::make('renovar')
                        ->label('Renovar / Estender')
                        ->icon('heroicon-m-calendar-days')
                        ->form([
                            Forms\Components\DateTimePicker::make('expires_at')
                                ->label('Nova Data de Vencimento')
                                ->required()
                                ->default(fn (Company $record) => $record->expires_at?->copy()->addMonth() ?? now()->addMonth())
                                ->native(false),
                        ])
                        ->action(function (Company $record, array $data) {
                            $record->update([
                                'expires_at' => $data['expires_at'],
                                'status' => 'active',
                            ]);
                        }),

                    Tables\Actions\Action::make('conceder_bonus')
                        ->label('Bônus de IA')
                        ->icon('heroicon-m-sparkles')
                        ->form([
                            Forms\Components\TextInput::make('bonus_tokens')
                                ->label('Conversas IA a adicionar')
                                ->numeric()
                                ->required()
                                ->default(10000),
                        ])
                        ->action(function (Company $record, array $data) {
                            $record->increment('ai_credits_balance', $data['bonus_tokens']);
                        }),
                ])->label('Ações')->button(),

                Tables\Actions\Action::make('alterar_status')
                    ->iconButton()
                    ->label('Alterar Status')
                    ->icon('heroicon-m-shield-exclamation')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('Novo Status')
                            ->options([
                                'active' => 'Ativa',
                                'trial' => 'Em Trial',
                                'suspended' => 'Suspensa/Bloqueada',
                                'expired' => 'Expirada',
                                'canceled' => 'Cancelada',
                            ])
                            ->required()
                            ->native(false)
                            ->default(fn (Company $record) => $record->status),
                    ])
                    ->action(function (Company $record, array $data) {
                        $record->update(['status' => $data['status']]);
                    }),

                Tables\Actions\Action::make('impersonate')
                    ->label('Entrar como Empresa')
                    ->icon('heroicon-m-eye')
                    ->iconButton()
                    ->color('success')
                    ->visible(fn () => auth()->user()?->isPlatformAdmin() ?? false)
                    ->form([
                        Forms\Components\Select::make('permission_level')
                            ->label('Nível de Permissão')
                            ->options([
                                'view_only' => 'Apenas Visualizar',
                                'view_edit' => 'Visualizar e Editar',
                                'view_fix' => 'Visualizar e Corrigir Configurações',
                                'full_access' => 'Acesso Total',
                            ])
                            ->required()
                            ->default('view_only')
                            ->native(false),
                        Forms\Components\TextInput::make('reason')
                            ->label('Motivo do Acesso')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ex: Suporte a chamado #123'),
                        Forms\Components\Toggle::make('notify_client')
                            ->label('Enviar alerta ao cliente?')
                            ->helperText('Notifica o responsável de que um admin acessou a conta.')
                            ->default(false),
                    ])
                    ->action(function (Company $record, array $data) {
                        $user = auth()->user();

                        // Gravar Log de Impersonação
                        $log = ImpersonationLog::create([
                            'super_admin_id' => $user->id,
                            'company_id' => $record->id,
                            'reason' => $data['reason'],
                            'permission_level' => $data['permission_level'],
                            'started_at' => now(),
                            'ip_address' => request()->ip(),
                        ]);

                        // Set session variables
                        session([
                            'impersonated_company_id' => $record->id,
                            'impersonation_level' => $data['permission_level'],
                            'impersonation_started_at' => now(),
                            'impersonation_reason' => $data['reason'],
                            'impersonation_log_id' => $log->id,
                        ]);

                        if ($data['notify_client']) {
                            Notification::route('mail', $record->email)
                                ->notify(new CompanyImpersonatedNotification($user->name, $data['reason']));
                        }

                        return redirect()->route('filament.admin.pages.dashboard');
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Excluir Selecionados'),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Tabs::make('Visão 360')
                    ->tabs([
                        Tab::make('Visão Geral')
                            ->icon('heroicon-m-identification')
                            ->schema([
                                Section::make('Informações da Empresa')
                                    ->schema([
                                        TextEntry::make('name')->label('Nome Fantasia')->size('lg')->weight('bold'),
                                        TextEntry::make('slug')->label('Slug')->color('gray'),
                                        TextEntry::make('legal_name')->label('Razão Social'),
                                        TextEntry::make('cnpj')->label('CNPJ'),
                                        TextEntry::make('responsible_name')->label('Responsável'),
                                        TextEntry::make('email')->label('Email')->icon('heroicon-m-envelope'),
                                        TextEntry::make('phone')->label('Telefone'),
                                        TextEntry::make('created_at')->label('Data de Cadastro')->date('d/m/Y'),
                                    ])->columns(3),
                            ]),
                        Tab::make('Assinatura e Limites')
                            ->icon('heroicon-m-credit-card')
                            ->schema([
                                Section::make('Status do Plano e Saúde')
                                    ->schema([
                                        TextEntry::make('status')
                                            ->badge()
                                            ->color(fn (string $state): string => match ($state) {
                                                'active' => 'success',
                                                'trial' => 'info',
                                                'suspended', 'canceled' => 'danger',
                                                'expired' => 'warning',
                                                default => 'gray',
                                            }),
                                        TextEntry::make('health_status')
                                            ->label(fn ($record) => 'Saúde (Score: '.$record->health_score.')')
                                            ->badge()
                                            ->color(fn (string $state): string => match ($state) {
                                                'saudável' => 'success',
                                                'atenção' => 'warning',
                                                'risco' => 'danger',
                                                default => 'gray',
                                            })
                                            ->formatStateUsing(fn (string $state) => ucfirst($state)),
                                        TextEntry::make('plan.name')->label('Plano Atual'),

                                        TextEntry::make('trial_end_at')
                                            ->label('Fim do Trial')
                                            ->date('d/m/Y')
                                            ->color(fn ($record) => $record->trial_end_at?->isPast() ? 'danger' : 'success'),
                                        TextEntry::make('expires_at')
                                            ->label('Vencimento')
                                            ->date('d/m/Y'),
                                    ])->columns(4),
                                Section::make('Consumo e Recursos')
                                    ->schema([
                                        TextEntry::make('users_count')->label('Usuários')->state(fn ($record) => $record->users()->count()),
                                        TextEntry::make('chatbots_count')->label('Chatbots')->state(fn ($record) => $record->chatbots()->count()),
                                        TextEntry::make('ai_credits_used')->label('Conversas IA Consumidas')->numeric(),
                                        TextEntry::make('ai_credits_balance')->label('Saldo IA Total')->numeric(),
                                    ])->columns(4),
                            ]),
                        Tab::make('Personalização')
                            ->icon('heroicon-m-paint-brush')
                            ->schema([
                                Section::make('Cores e Identidade')
                                    ->schema([
                                        ColorEntry::make('panel_color_primary')->label('Cor Primária do Painel'),
                                        ColorEntry::make('panel_color_secondary')->label('Cor Secundária do Painel'),
                                    ]),
                            ]),
                        Tab::make('Chamados de Suporte')
                            ->icon('heroicon-m-ticket')
                            ->schema([
                                RepeatableEntry::make('supportTickets')
                                    ->label('Últimos Chamados')
                                    ->schema([
                                        TextEntry::make('subject')->label('Assunto')->weight('bold'),
                                        TextEntry::make('status')
                                            ->label('Status')
                                            ->badge()
                                            ->color(fn (string $state): string => match ($state) {
                                                'open' => 'danger',
                                                'in_progress' => 'warning',
                                                'resolved', 'closed' => 'success',
                                                default => 'gray',
                                            }),
                                        TextEntry::make('priority')
                                            ->label('Prioridade')
                                            ->badge(),
                                        TextEntry::make('created_at')->label('Criado em')->dateTime('d/m/Y H:i'),
                                    ])
                                    ->columns(4)
                                    ->hidden(fn ($record) => $record->supportTickets()->count() === 0),
                                TextEntry::make('no_tickets')
                                    ->label('Chamados')
                                    ->state('Esta empresa não possui chamados de suporte registrados.')
                                    ->hidden(fn ($record) => $record->supportTickets()->count() > 0),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CompanyResource\RelationManagers\UsersRelationManager::class,
            CompanyResource\RelationManagers\InvoicesRelationManager::class,
            CompanyResource\RelationManagers\PaymentHistoriesRelationManager::class,
            CompanyResource\RelationManagers\SubscriptionAuditLogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'view' => Pages\ViewCompany::route('/{record}'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
