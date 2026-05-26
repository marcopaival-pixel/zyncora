<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationGroup = 'Configurações de Acesso';

    protected static ?string $modelLabel = 'Organização / Conta';

    protected static ?string $pluralModelLabel = 'Organizações e Contas';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isPlatformAdmin() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Identidade Corporativa')
                        ->icon('heroicon-m-identification')
                        ->schema([
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nome da Organização')
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
                                Forms\Components\TextInput::make('cnpj')
                                    ->label('Identificação Fiscal (CNPJ)')
                                    ->maxLength(32),
                                Forms\Components\TextInput::make('email')
                                    ->label('E-mail Administrativo')
                                    ->email(),
                                Forms\Components\TextInput::make('phone')
                                    ->label('Contacto Geral')
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
                    
                    Forms\Components\Section::make('Personalização')
                        ->icon('heroicon-m-paint-brush')
                        ->collapsible()
                        ->collapsed()
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

                    Forms\Components\Section::make('Chat e Horários')
                        ->icon('heroicon-m-chat-bubble-left-right')
                        ->collapsible()
                        ->collapsed()
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
                                    if ($state === '') return null;
                                    $decoded = json_decode($state, true);
                                    return is_array($decoded) ? $decoded : null;
                                }),
                        ]),

                    Forms\Components\Section::make('Instalação (Widget)')
                        ->icon('heroicon-m-code-bracket')
                        ->hiddenOn('create')
                        ->collapsible()
                        ->schema([
                            Forms\Components\TextInput::make('widget_script')
                                ->label('Código de Incorporação')
                                ->readonly()
                                ->formatStateUsing(function (?Company $record) {
                                    if (!$record) return '';
                                    $baseUrl = config('app.url');
                                    return "<script src=\"{$baseUrl}/widget/chatbox-widget.js\" data-slug=\"{$record->slug}\" data-api=\"{$baseUrl}/api\" defer></script>";
                                })
                                ->extraAttributes([
                                    'onclick' => "this.select(); document.execCommand('copy'); window.alert('Copiado para a área de transferência!');"
                                ])
                                ->helperText('Clique no campo para copiar o código e insira-o antes do fechamento da tag </body> do seu site.')
                                ->columnSpanFull(),
                        ]),
                ])->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Status da Conta')
                        ->schema([
                            Forms\Components\Select::make('status')
                                ->label('Estado da Conta')
                                ->options([
                                    'active' => 'Ativa',
                                    'suspended' => 'Desativada',
                                ])
                                ->default('active')
                                ->required()
                                ->native(false),
                        ]),

                    Forms\Components\Section::make('Plano SaaS')
                        ->description('Controle de limites e subscrição')
                        ->schema([
                            Forms\Components\ToggleButtons::make('plan')
                                ->label('Nível do Plano')
                                ->options([
                                    'basic' => 'Básico',
                                    'pro' => 'Profissional',
                                    'enterprise' => 'Empresarial',
                                ])
                                ->icons([
                                    'basic' => 'heroicon-m-sparkles',
                                    'pro' => 'heroicon-m-bolt',
                                    'enterprise' => 'heroicon-m-trophy',
                                ])
                                ->colors([
                                    'basic' => 'gray',
                                    'pro' => 'primary',
                                    'enterprise' => 'amber',
                                ])
                                ->default('basic')
                                ->inline()
                                ->required(),
                            Forms\Components\DateTimePicker::make('expires_at')
                                ->label('Válido até')
                                ->nullable()
                                ->native(false),
                            Forms\Components\Grid::make(2)->schema([
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
                            ]),
                        ]),
                ])->columnSpan(['lg' => 1]),

            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo_path')
                    ->label('Logo')
                    ->circular()
                    ->defaultImageUrl(fn (Company $record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&color=FFFFFF&background=09090b'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Organização')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Company $record) => $record->slug),
                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->icon('heroicon-m-envelope')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('plan')
                    ->label('Nível de Serviço')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'basic' => 'gray',
                        'pro', 'professional' => 'primary',
                        'enterprise' => 'amber',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'basic' => 'Básico',
                        'pro', 'professional' => 'Profissional',
                        'enterprise' => 'Empresarial',
                        default => ucfirst($state),
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower(trim($state))) {
                        'active', 'ativa' => 'success',
                        'suspended', 'suspensa', 'desativada', 'inativa' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match (strtolower(trim($state))) {
                        'active', 'ativa' => 'Ativa',
                        'suspended', 'suspensa', 'desativada', 'inativa' => 'Desativada',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('max_users')
                    ->label('Limites (M / A)')
                    ->tooltip('Membros / Atendentes')
                    ->formatStateUsing(fn (Company $record) => "{$record->max_users} / {$record->max_attendants}")
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Vencimento')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\ColorColumn::make('chat_color')
                    ->label('Cor Branding')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'active' => 'Ativa',
                        'suspended' => 'Desativada',
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
            ])
            ->defaultSort('name')
            ->searchPlaceholder('Pesquisar organização...')
            ->emptyStateHeading('Nenhuma organização')
            ->emptyStateDescription('Crie uma conta de cliente para isolar dados, utilizadores e canais.')
            ->emptyStateIcon('heroicon-o-building-office-2')
            ->actions([
                Tables\Actions\EditAction::make()->label('Gerir')->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Excluir Selecionados'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
