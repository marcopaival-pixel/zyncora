<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LgpdAuditResource\Pages;
use App\Models\LgpdAuditLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LgpdAuditResource extends Resource
{
    protected static ?string $model = LgpdAuditLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-finger-print';

    protected static ?string $navigationGroup = 'Configurações & Auditoria';

    protected static ?string $modelLabel = 'Log de Auditoria';

    protected static ?string $pluralModelLabel = 'Logs de Auditoria';

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        return $user?->isCompanyAdmin() || $user?->isPlatformAdmin() || $user?->isSupervisor();
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user?->isCompanyAdmin() || $user?->isPlatformAdmin() || $user?->isSupervisor();
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with('user');
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
                Forms\Components\TextInput::make('action')
                    ->label('Ação'),
                Forms\Components\TextInput::make('resource_type')
                    ->label('Tipo de Recurso'),
                Forms\Components\TextInput::make('resource_id')
                    ->label('ID do Recurso'),
                Forms\Components\TextInput::make('ip_address')
                    ->label('IP'),
                Forms\Components\KeyValue::make('payload')
                    ->label('Dados'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuário')
                    ->searchable()
                    ->placeholder('Sistema'),
                Tables\Columns\TextColumn::make('action')
                    ->label('Ação')
                    ->searchable(),
                Tables\Columns\TextColumn::make('resource_type')
                    ->label('Recurso')
                    ->formatStateUsing(fn ($state) => class_basename($state)),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data/Hora')
                    ->since()
                    ->sortable()
                    ->tooltip(fn (LgpdAuditLog $record): ?string => $record->created_at?->format('d/m/Y H:i:s')),
            ])
            ->defaultSort('created_at', 'desc')
            ->searchPlaceholder('Pesquisar ação, recurso ou IP…')
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->label('Ação')
                    ->options(fn (): array => LgpdAuditLog::query()
                        ->whereNotNull('action')
                        ->orderBy('action')
                        ->distinct()
                        ->pluck('action', 'action')
                        ->all()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                //
            ])
            ->emptyStateHeading('Sem auditoria LGPD')
            ->emptyStateDescription('Alterações sensíveis a dados pessoais são registadas aqui.')
            ->emptyStateIcon('heroicon-o-shield-exclamation');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLgpdAudits::route('/'),
        ];
    }
}
