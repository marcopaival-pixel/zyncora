<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermissionResource\Pages;
use App\Models\Permission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationGroup = 'Configurações & Auditoria';

    protected static ?string $navigationLabel = 'Permissões';

    protected static ?string $modelLabel = 'Permissão';

    protected static ?string $pluralModelLabel = 'Catálogo de Permissões';

    protected static ?int $navigationSort = 4;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasPermission('view_perfis') ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission('view_perfis') ?? false;
    }

    public static function getNavigationBadge(): ?string
    {
        return cache()->remember('navigation_badge_permissions_count', 300, fn (): string => (string) Permission::query()->count());
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Código da Permissão')
                            ->placeholder('ex: view_users')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('module')
                            ->label('Módulo')
                            ->placeholder('ex: Usuários')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->label('Descrição')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('module')
                    ->label('Módulo')
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains(strtolower($state), 'utilizad') => 'info',
                        str_contains(strtolower($state), 'usuario') => 'info',
                        str_contains(strtolower($state), 'conversa') => 'success',
                        str_contains(strtolower($state), 'atendimen') => 'success',
                        str_contains(strtolower($state), 'chatbot') => 'success',
                        str_contains(strtolower($state), 'lgpd') => 'warning',
                        str_contains(strtolower($state), 'sistema') => 'danger',
                        str_contains(strtolower($state), 'plataform') => 'danger',
                        str_contains(strtolower($state), 'perfil') => 'primary',
                        str_contains(strtolower($state), 'permiss') => 'primary',
                        str_contains(strtolower($state), 'crm') => 'purple',
                        str_contains(strtolower($state), 'integra') => 'gray',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Código')
                    ->searchable()
                    ->sortable()
                    ->fontFamily('mono'),
                Tables\Columns\TextColumn::make('description')
                    ->label('Descrição')
                    ->limit(50)
                    ->wrap(),
                Tables\Columns\TextColumn::make('roles_count')
                    ->label('Perfis que usam')
                    ->counts('roles')
                    ->badge()
                    ->color(fn (int $state): string => $state === 0 ? 'danger' : 'success')
                    ->sortable()
                    ->tooltip(fn (Permission $record): string => $record->roles->isEmpty()
                            ? '⚠️ Nenhum perfil usa esta permissão (órfã)'
                            : $record->roles->pluck('name')->join(', ')
                    ),
            ])
            ->defaultSort('module')
            ->defaultGroup('module')
            ->searchPlaceholder('Pesquisar módulo, código ou descrição…')
            ->filters([
                Tables\Filters\SelectFilter::make('module')
                    ->label('Módulo')
                    ->options(fn (): array => Permission::query()
                        ->whereNotNull('module')
                        ->orderBy('module')
                        ->distinct()
                        ->pluck('module', 'module')
                        ->all()),

                Tables\Filters\Filter::make('orphaned')
                    ->label('Sem perfil associado (órfãs)')
                    ->query(fn (Builder $query): Builder => $query->doesntHave('roles')
                    )
                    ->indicator('⚠️ Apenas órfãs'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->groups([
                Tables\Grouping\Group::make('module')
                    ->label('Módulo')
                    ->collapsible(),
            ])
            ->emptyStateHeading('Sem permissões')
            ->emptyStateDescription('As permissões são normalmente criadas por migrações ou seeders do sistema.')
            ->emptyStateIcon('heroicon-o-key');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('roles');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePermissions::route('/'),
        ];
    }
}
