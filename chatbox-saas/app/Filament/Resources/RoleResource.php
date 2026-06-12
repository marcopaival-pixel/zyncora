<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Models\Permission;
use App\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Configurações & Auditoria';

    protected static ?string $modelLabel = 'Perfil / Cargo';

    protected static ?string $pluralModelLabel = 'Perfis e Permissões';

    protected static ?int $navigationSort = 3;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasPermission('view_perfis') ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission('view_perfis') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações do Perfil')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome do Perfil')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('description')
                            ->label('Descrição')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Permissões Associadas')
                    ->description('Selecione as funcionalidades que este perfil pode acessar.')
                    ->schema([
                        Forms\Components\CheckboxList::make('permissions')
                            ->relationship('permissions', 'name')
                            ->label('Permissões')
                            ->options(function () {
                                return Permission::all()
                                    ->groupBy('module')
                                    ->map(function ($permissions, $module) {
                                        return $permissions->pluck('name', 'id')->toArray();
                                    })
                                    ->toArray();
                            })
                            ->bulkToggleable()
                            ->columns(3)
                            ->searchable()
                            ->gridDirection('column'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Perfil')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Descrição')
                    ->searchable(),
                Tables\Columns\TextColumn::make('permissions_count')
                    ->label('Permissões')
                    ->counts('permissions')
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->tooltip(fn (Role $record): ?string => $record->created_at?->format('d/m/Y H:i')),
            ])
            ->defaultSort('name')
            ->searchPlaceholder('Pesquisar perfil…')
            ->filters([
                //
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
            ->emptyStateHeading('Nenhum perfil')
            ->emptyStateDescription('Defina cargos (Admin, Agente, etc.) e associe permissões por módulo.')
            ->emptyStateIcon('heroicon-o-shield-check');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageRoles::route('/'),
        ];
    }
}

