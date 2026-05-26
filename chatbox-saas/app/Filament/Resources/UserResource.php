<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Configurações de Acesso';

    protected static ?string $modelLabel = 'Membro da Equipa';

    protected static ?string $pluralModelLabel = 'Membros e Utilizadores';

    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasPermission('view_usuários') ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission('view_usuários') ?? false;
    }

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }

        return cache()->remember("nav_badge_users_active_{$user->company_id}", 60, function () use ($user) {
            $query = static::getModel()::where('status', 'active');
            if ($user && ! $user->isPlatformAdmin()) {
                $query->where('company_id', $user->company_id);
            }

            return (string) $query->count();
        });
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
                Forms\Components\Section::make('Segurança e perfil profissional')
                    ->description('Acesso, cargo e vínculo à organização.')
                    ->icon('heroicon-o-user-circle')
                    ->aside()
                    ->schema([
                        Forms\Components\Select::make('company_id')
                            ->label('Organização / Conta')
                            ->placeholder('Selecione uma empresa...')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->visible(fn () => auth()->user()?->isPlatformAdmin() ?? false)
                            ->columnSpanFull(),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nome Completo')
                                    ->placeholder('Ex: João Silva')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('email')
                                    ->label('Endereço de E-mail')
                                    ->placeholder('atendente@empresa.com')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                            ]),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('phone')
                                    ->label('Telefone / WhatsApp')
                                    ->placeholder('5511999999999')
                                    ->tel()
                                    ->maxLength(64),


                                Forms\Components\Select::make('status')
                                    ->label('Estado da Conta')
                                    ->options([
                                        'active' => 'Ativo',
                                        'inactive' => 'Inativo',
                                    ])
                                    ->default('active')
                                    ->required(),

                                Forms\Components\Select::make('presence_status')
                                    ->label('Disponibilidade do Agente')
                                    ->options([
                                        'online' => 'Disponível (Online)',
                                        'busy' => 'Ocupado',
                                        'offline' => 'Indisponível (Offline)',
                                    ])
                                    ->default('offline')
                                    ->required(),

                                Forms\Components\TextInput::make('max_simultaneous_chats')
                                    ->label('Limite de Chats Sims.')
                                    ->numeric()
                                    ->default(10)
                                    ->required(),
                            ]),

                        Forms\Components\Select::make('role')
                            ->label('Cargo / Perfil')
                            ->options([
                                User::ROLE_COMPANY_ADMIN => 'Administrador da empresa',
                                User::ROLE_SUPERVISOR => 'Supervisor',
                                User::ROLE_AGENT => 'Agente de atendimento',
                                User::ROLE_MANAGER => 'Gestor',
                                User::ROLE_FINANCIAL => 'Financeiro',
                                User::ROLE_TECHNICAL_SUPPORT => 'Suporte técnico',
                                User::ROLE_CLIENT => 'Cliente',
                            ])
                            ->default(User::ROLE_AGENT)
                            ->required()
                            ->native(false)
                            ->visible(fn () => ! (auth()->user()?->isPlatformAdmin() ?? false))
                            ->disabled(fn () => ! (auth()->user()?->canManageUsers() ?? false)),

                        Forms\Components\Select::make('role')
                            ->label('Cargo / Perfil (plataforma)')
                            ->options([
                                User::ROLE_PLATFORM_ADMIN => 'Administrador da plataforma',
                                User::ROLE_COMPANY_ADMIN => 'Administrador da empresa',
                                User::ROLE_SUPERVISOR => 'Supervisor',
                                User::ROLE_AGENT => 'Agente de atendimento',
                                User::ROLE_MANAGER => 'Gestor',
                                User::ROLE_FINANCIAL => 'Financeiro',
                                User::ROLE_TECHNICAL_SUPPORT => 'Suporte técnico',
                                User::ROLE_CLIENT => 'Cliente',
                            ])
                            ->required()
                            ->native(false)
                            ->visible(fn () => auth()->user()?->isPlatformAdmin() ?? false),

                        Forms\Components\Select::make('sectors')
                            ->relationship('sectors', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->label('Setores de Atendimento')
                            ->placeholder('Selecione os setores...'),

                        Forms\Components\TextInput::make('password')
                            ->label('Nova Senha')
                            ->helperText('Deixe em branco para manter a senha atual.')
                            ->password()
                            ->revealable()
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn (User $record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&color=FFFFFF&background=8b5cf6')
                    ->grow(false),

                Tables\Columns\TextColumn::make('name')
                    ->label('Membro')
                    ->description(fn (User $record): string => $record->email)
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage('Nome copiado'),

                Tables\Columns\TextColumn::make('company.name')
                    ->label('Organização / Conta')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable()
                    ->visible(fn () => auth()->user()?->isPlatformAdmin() ?? false),

                Tables\Columns\TextColumn::make('role')
                    ->label('Cargo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        User::ROLE_PLATFORM_ADMIN => 'danger',
                        User::ROLE_COMPANY_ADMIN => 'warning',
                        User::ROLE_SUPERVISOR => 'info',
                        User::ROLE_AGENT => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        User::ROLE_PLATFORM_ADMIN => 'Platform Admin',
                        User::ROLE_COMPANY_ADMIN => 'Admin Empresa',
                        User::ROLE_SUPERVISOR => 'Supervisor',
                        User::ROLE_AGENT => 'Agente',
                        default => ucfirst($state),
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower(trim($state))) {
                        'active', 'ativo' => 'success',
                        'inactive', 'inativo' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match (strtolower(trim($state))) {
                        'active', 'ativo' => 'Ativo',
                        'inactive', 'inativo' => 'Inativo',
                        default => ucfirst($state),
                    }),

                Tables\Columns\TextColumn::make('presence_status')
                    ->label('Disponibilidade')
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower(trim($state))) {
                        'online', 'disponível' => 'success',
                        'busy', 'ocupado' => 'warning',
                        'offline', 'indisponível' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match (strtolower(trim($state))) {
                        'online', 'disponível' => 'Disponível',
                        'busy', 'ocupado' => 'Ocupado',
                        'offline', 'indisponível' => 'Indisponível',
                        default => ucfirst($state),
                    }),

                Tables\Columns\TextColumn::make('last_active_at')
                    ->label('Última Atividade')
                    ->since()
                    ->sortable()
                    ->color('gray')
                    ->placeholder('—')
                    ->tooltip(fn (User $record): ?string => $record->last_active_at?->format('d/m/Y H:i'))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->searchPlaceholder('Pesquisar por nome ou e-mail...')
            ->emptyStateHeading('Nenhum membro encontrado')
            ->emptyStateDescription('Tente ajustar os filtros ou adicionar um novo utilizador à equipa.')
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Cargo ou Função')
                    ->indicator('Cargo')
                    ->options([
                        User::ROLE_COMPANY_ADMIN => 'Administrador da empresa',
                        User::ROLE_SUPERVISOR => 'Supervisor',
                        User::ROLE_AGENT => 'Agente de Atendimento',
                    ])
                    ->native(false),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado da Conta')
                    ->indicator('Estado')
                    ->options([
                        'active' => 'Ativo',
                        'inactive' => 'Inativo',
                    ])
                    ->native(false),
            ])
            ->filtersTriggerAction(fn ($action) => $action->label('Filtros Avançados'))
            ->persistFiltersInSession()
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Editar')
                    ->button()
                    ->icon('heroicon-m-pencil-square'),
                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar')
                    ->iconButton()
                    ->color('danger')
                    ->modalHeading('Eliminar Utilizador')
                    ->modalDescription('Tem a certeza que deseja eliminar este utilizador? Esta ação não pode ser desfeita.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Eliminar selecionados'),
                ])->label('Ações em massa'),
            ])
            ->emptyStateIcon('heroicon-o-users');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
