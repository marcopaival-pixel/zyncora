<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ImpersonationLogResource\Pages;
use App\Models\ImpersonationLog;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ImpersonationLogResource extends Resource
{
    protected static ?string $model = ImpersonationLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Configurações & Auditoria';

    protected static ?string $modelLabel = 'Acesso Administrativo';

    protected static ?string $pluralModelLabel = 'Acessos Administrativos';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isPlatformAdmin() ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
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
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('superAdmin.name')
                    ->label('Administrador')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Motivo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('permission_level')
                    ->label('Permissão')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'view_only' => 'gray',
                        'view_edit' => 'info',
                        'view_fix' => 'warning',
                        'full_access' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'view_only' => 'Apenas Visualizar',
                        'view_edit' => 'Visualizar e Editar',
                        'view_fix' => 'Ver e Corrigir',
                        'full_access' => 'Acesso Total',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('started_at')
                    ->label('Entrada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ended_at')
                    ->label('Saída')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration')
                    ->label('Duração')
                    ->getStateUsing(function (ImpersonationLog $record) {
                        if (! $record->ended_at) {
                            return 'Em andamento';
                        }
                        $diff = $record->started_at->diffInMinutes($record->ended_at);

                        return $diff.' min';
                    }),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('started_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('company_id')
                    ->label('Empresa')
                    ->relationship('company', 'name')
                    ->searchable(),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListImpersonationLogs::route('/'),
        ];
    }
}
