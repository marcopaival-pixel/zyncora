<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserSessionLogResource\Pages;
use App\Models\UserSessionLog;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserSessionLogResource extends Resource
{
    protected static ?string $model = UserSessionLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';

    protected static ?string $navigationGroup = 'Configurações & Auditoria';

    protected static ?string $modelLabel = 'Sessão';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->canViewLogs() ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->canViewLogs() ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with('user');

        // Removido o agrupamento para permitir a seleção de registros individuais para exclusão

        $user = auth()->user();
        if ($user && ! $user->isPlatformAdmin()) {
            $query->whereHas('user', fn ($q) => $q->where('company_id', $user->company_id));
        }

        return $query;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Usuário')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary')
                    ->action(
                        Tables\Actions\Action::make('view_user_sessions')
                            ->label('Ver Histórico')
                            ->modalHeading(fn ($record) => "Histórico de Acessos: {$record->user?->name}")
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('Fechar')
                            ->slideOver()
                            ->modalWidth('3xl')
                            ->infolist([
                                Section::make()
                                    ->schema([
                                        RepeatableEntry::make('sessions')
                                            ->label('Atividades Recentes')
                                            ->state(fn ($record) => $record->user?->sessionLogs()->orderBy('login_at', 'desc')->limit(50)->get())
                                            ->schema([
                                                Split::make([
                                                    TextEntry::make('login_at')
                                                        ->icon('heroicon-m-calendar-days')
                                                        ->state(fn ($record) => $record->login_at?->format('d/m/Y H:i') ?? 'N/A')
                                                        ->color('success')
                                                        ->grow(false),
                                                    TextEntry::make('ip_address')
                                                        ->icon('heroicon-m-globe-alt')
                                                        ->state(fn ($record) => "IP: {$record->ip_address}")
                                                        ->grow(false),
                                                    TextEntry::make('browser')
                                                        ->icon('heroicon-m-computer-desktop')
                                                        ->state(fn ($record) => "{$record->browser} / {$record->platform}")
                                                        ->suffix(fn ($record) => ' ('.ucfirst($record->device_type ?? 'Desconhecido').')'),
                                                ]),
                                            ])
                                            ->contained(true),
                                    ]),
                            ])
                    )
                    ->description(fn ($record) => $record->user?->email),
                TextColumn::make('ip_address')
                    ->label('Último IP')
                    ->searchable(),
                TextColumn::make('device_type')
                    ->label('Dispositivo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'desktop' => 'info',
                        'mobile' => 'success',
                        'tablet' => 'warning',
                        default => 'gray',
                    }),
                IconColumn::make('is_active')
                    ->label('Sessão Ativa')
                    ->boolean(),
                TextColumn::make('login_at')
                    ->label('Último Acesso')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('login_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Apenas Ativas'),
                SelectFilter::make('user_id')
                    ->label('Filtrar por Usuário')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\Action::make('terminate')
                    ->label('Encerrar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->is_active)
                    ->action(function ($record) {
                        // Invalida na tabela de sessões do Laravel
                        \DB::table('sessions')->where('id', $record->session_id)->delete();

                        $record->update([
                            'is_active' => false,
                            'logout_at' => now(),
                        ]);
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserSessionLogs::route('/'),
        ];
    }
}
