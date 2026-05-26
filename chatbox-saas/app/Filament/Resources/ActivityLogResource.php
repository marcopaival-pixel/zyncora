<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use App\Models\ActivityLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?string $navigationGroup = 'Segurança';

    protected static ?string $modelLabel = 'Log de Atividade';
    
    protected static ?string $pluralModelLabel = 'Logs de Atividade';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->canViewLogs() ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->canViewLogs() ?? false;
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery()->with('user');
        $user = auth()->user();
        if ($user && ! $user->isPlatformAdmin()) {
            $query->where('company_id', $user->company_id);
        }

        return $query;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuário')
                    ->searchable(),
                Tables\Columns\TextColumn::make('event')
                    ->label('Evento')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'login' => 'success',
                        'delete' => 'danger',
                        default => 'info',
                    }),
                Tables\Columns\TextColumn::make('description')
                    ->label('Descrição')
                    ->limit(50),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data/Hora')
                    ->since()
                    ->sortable()
                    ->tooltip(fn (ActivityLog $record): ?string => $record->created_at?->format('d/m/Y H:i:s')),
            ])
            ->defaultSort('created_at', 'desc')
            ->searchPlaceholder('Pesquisar utilizador, evento ou descrição…')
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->label('Evento')
                    ->options(fn (): array => ActivityLog::query()
                        ->whereNotNull('event')
                        ->orderBy('event')
                        ->distinct()
                        ->pluck('event', 'event')
                        ->all()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                //
            ])
            ->emptyStateHeading('Sem atividade registada')
            ->emptyStateDescription('Os eventos de auditoria aparecem aqui quando os utilizadores interagem com o sistema.')
            ->emptyStateIcon('heroicon-o-list-bullet');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
        ];
    }
}
