<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyIntegrationResource\Pages;
use App\Models\Company;
use App\Models\CompanyIntegration;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class CompanyIntegrationResource extends Resource
{
    protected static ?string $model = CompanyIntegration::class;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static ?string $navigationGroup = 'Integrações';

    protected static ?string $modelLabel = 'Integração';

    protected static ?string $pluralModelLabel = 'Integrações';

    protected static ?int $navigationSort = 1;

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
        $query = parent::getEloquentQuery();
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
                Forms\Components\Section::make('Empresa e canal')
                    ->description('Defina a que empresa pertence esta integração e qual o tipo de conexão.')
                    ->icon('heroicon-o-building-office-2')
                    ->schema([
                        Forms\Components\Select::make('company_id')
                            ->relationship('company', 'name')
                            ->label('Empresa')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->visible(fn () => auth()->user()?->isPlatformAdmin() ?? false),
                        Forms\Components\ToggleButtons::make('driver')
                            ->label('Tipo de canal para conexão')
                            ->options([
                                'whatsapp_cloud' => 'WhatsApp Cloud API (Meta)',
                                'gateway_generic' => 'Integração via Gateway Genérico',
                            ])
                            ->icons([
                                'whatsapp_cloud' => 'heroicon-o-chat-bubble-left-right',
                                'gateway_generic' => 'heroicon-o-server-stack',
                            ])
                            ->colors([
                                'whatsapp_cloud' => 'success',
                                'gateway_generic' => 'info',
                            ])
                            ->columns(2)
                            ->required()
                            ->live()
                            ->helperText('Escolha o canal oficial da Meta para WhatsApp ou use o Gateway para outras extensões.')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Webhook e verificação')
                    ->description('O mesmo URL recebe GET (verificação) e POST (eventos). O token abaixo deve coincidir com o da consola Meta.')
                    ->icon('heroicon-o-link')
                    ->schema([
                        Forms\Components\TextInput::make('webhook_verify_token')
                            ->label('Token de verificação do webhook')
                            ->maxLength(255)
                            ->placeholder('ex.: um token seguro definido por si na Meta')
                            ->helperText('Copie este valor para o campo de verificação ao subscrever o webhook na Meta.'),
                        Forms\Components\Placeholder::make('webhook_url_preview')
                            ->label('URL público do webhook')
                            ->content(function (Get $get, $livewire): HtmlString {
                                $slug = static::resolveWebhookCompanySlug($get, $livewire);

                                if ($slug === null) {
                                    return new HtmlString(
                                        '<p class="text-sm text-gray-600 dark:text-gray-400">'
                                        .e(auth()->user()?->isPlatformAdmin()
                                            ? 'Selecione a empresa acima para pré-visualizar o URL completo.'
                                            : 'O URL será baseado no slug da sua empresa.')
                                        .'</p>'
                                    );
                                }

                                $url = url('/api/v1/integrations/whatsapp/webhook/'.$slug);

                                return new HtmlString(
                                    '<div class="space-y-2">'
                                    .'<code class="fi-input block w-full break-all rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-xs leading-relaxed text-gray-900 dark:border-white/10 dark:bg-white/5 dark:text-gray-100">'
                                    .e($url)
                                    .'</code>'
                                    .'<p class="text-xs text-gray-500 dark:text-gray-400">'
                                    .'Deve usar o mesmo host que <strong>APP_URL</strong> na sua instalação.'
                                    .'</p>'
                                    .'</div>'
                                );
                            })
                            ->visible(fn (Get $get) => $get('driver') === 'whatsapp_cloud'),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Credenciais WhatsApp Cloud API')
                    ->description('Valores da aplicação e do número na Meta (developers.facebook.com). O App Secret valida a assinatura X-Hub-Signature-256 dos POST.')
                    ->icon('heroicon-o-key')
                    ->schema([
                        Forms\Components\TextInput::make('credentials.phone_number_id')
                            ->label('Phone number ID')
                            ->placeholder('ID do número no Graph API')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('credentials.waba_id')
                            ->label('WABA ID')
                            ->placeholder('Opcional')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('credentials.access_token')
                            ->label('Access token')
                            ->password()
                            ->revealable()
                            ->maxLength(2048)
                            ->helperText('Token de longa duração ou de sistema, conforme a configuração da App.')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('credentials.app_secret')
                            ->label('App Secret')
                            ->password()
                            ->revealable()
                            ->maxLength(512)
                            ->helperText('Obrigatório para aceitar webhooks assinados pela Meta.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->visible(fn (Get $get) => $get('driver') === 'whatsapp_cloud'),

                Forms\Components\Section::make('Gateway genérico')
                    ->description('Fluxo em evolução.')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        Forms\Components\Placeholder::make('gateway_placeholder')
                            ->label('')
                            ->content(new HtmlString(
                                '<p class="text-sm text-gray-600 dark:text-gray-400">'
                                .'Para integrações internas ou gateways personalizados, as credenciais específicas serão definidas conforme o contrato da API. '
                                .'Guarde a integração e ajuste depois se necessário.'
                                .'</p>'
                            )),
                    ])
                    ->visible(fn (Get $get) => $get('driver') === 'gateway_generic'),

                Forms\Components\Section::make('Estado')
                    ->icon('heroicon-o-signal')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Estado da integração')
                            ->options([
                                'pending' => 'Pendente',
                                'connected' => 'Conectado',
                                'error' => 'Erro',
                            ])
                            ->default('pending')
                            ->required()
                            ->native(false),
                    ]),
            ]);
    }

    /**
     * Slug da empresa para montar o URL do webhook (criação e edição).
     */
    protected static function resolveWebhookCompanySlug(Get $get, mixed $livewire): ?string
    {
        $record = null;
        if (is_object($livewire) && method_exists($livewire, 'getRecord')) {
            $record = $livewire->getRecord();
        }

        if ($record instanceof CompanyIntegration && $record->exists) {
            $record->loadMissing('company');

            return $record->company?->slug;
        }

        $companyId = $get('company_id');
        if ($companyId) {
            return Company::query()->whereKey($companyId)->value('slug');
        }

        return auth()->user()?->company?->slug;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company.name')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('driver')
                    ->label('Integração')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'whatsapp_cloud' => 'WhatsApp Cloud',
                        default => str_replace('_', ' ', $state),
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active', 'connected' => 'success',
                        'inactive', 'disconnected' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado')
                    ->since()
                    ->sortable()
                    ->tooltip(fn (CompanyIntegration $record): ?string => $record->updated_at?->format('d/m/Y H:i')),
            ])
            ->defaultSort('updated_at', 'desc')
            ->searchPlaceholder('Pesquisar empresa ou tipo de integração…')
            ->filters([
                Tables\Filters\SelectFilter::make('driver')
                    ->label('Tipo')
                    ->options(fn (): array => CompanyIntegration::query()
                        ->whereNotNull('driver')
                        ->distinct()
                        ->orderBy('driver')
                        ->pluck('driver', 'driver')
                        ->mapWithKeys(fn (string $d): array => [$d => match ($d) {
                            'whatsapp_cloud' => 'WhatsApp Cloud',
                            default => str_replace('_', ' ', $d),
                        }])
                        ->all()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Nenhuma integração')
            ->emptyStateDescription('Ligue WhatsApp Cloud ou outros canais para receber e enviar mensagens.')
            ->emptyStateIcon('heroicon-o-puzzle-piece');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanyIntegrations::route('/'),
            'create' => Pages\CreateCompanyIntegration::route('/create'),
            'edit' => Pages\EditCompanyIntegration::route('/{record}/edit'),
        ];
    }
}
