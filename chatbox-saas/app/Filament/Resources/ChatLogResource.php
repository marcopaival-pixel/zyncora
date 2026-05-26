<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChatLogResource\Pages;
use App\Models\ChatLog;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontFamily;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ChatLogResource extends Resource
{
    protected static ?string $model = ChatLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Operação';

    protected static ?string $modelLabel = 'Log operacional';

    protected static ?string $pluralModelLabel = 'Logs de operação';

    protected static ?int $navigationSort = 10;

    public static function canCreate(): bool
    {
        return false;
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

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Evento')
                    ->description('Tipo e descrição registada pelo sistema.')
                    ->icon('heroicon-o-bolt')
                    ->schema([
                        Infolists\Components\TextEntry::make('company.name')
                            ->label('Empresa')
                            ->placeholder('—')
                            ->visible(fn (): bool => auth()->user()?->isPlatformAdmin() ?? false),
                        Infolists\Components\TextEntry::make('log_type')
                            ->label('Tipo')
                            ->badge()
                            ->color(fn (string $state): string => match (true) {
                                str_contains(strtolower($state), 'error') => 'danger',
                                str_contains(strtolower($state), 'warn') => 'warning',
                                str_contains(strtolower($state), 'success') => 'success',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('description')
                            ->label('Descrição')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('logged_at')
                            ->label('Registado em')
                            ->dateTime('d/m/Y H:i:s')
                            ->icon('heroicon-o-clock'),
                    ]),

                Infolists\Components\Section::make('Contexto')
                    ->description('Metadados adicionais (JSON).')
                    ->icon('heroicon-o-code-bracket')
                    ->visible(fn (ChatLog $record): bool => is_array($record->context) && $record->context !== [])
                    ->schema([
                        Infolists\Components\TextEntry::make('context')
                            ->label('')
                            ->state(fn (ChatLog $record): string => (string) json_encode($record->context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))
                            ->fontFamily(FontFamily::Mono)
                            ->columnSpanFull()
                            ->copyable(),
                    ])
                    ->collapsible(),
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
                    ->placeholder('—')
                    ->toggleable()
                    ->visible(fn (): bool => auth()->user()?->isPlatformAdmin() ?? false),

                Tables\Columns\TextColumn::make('log_type')
                    ->label('Tipo')
                    ->badge()
                    ->searchable()
                    ->color(fn (string $state): string => match (true) {
                        str_contains(strtolower($state), 'error') => 'danger',
                        str_contains(strtolower($state), 'warn') => 'warning',
                        str_contains(strtolower($state), 'success') => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('description')
                    ->label('Descrição')
                    ->limit(70)
                    ->wrap()
                    ->searchable()
                    ->tooltip(fn (ChatLog $record): string => $record->description ?? ''),

                Tables\Columns\TextColumn::make('logged_at')
                    ->label('Quando')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable()
                    ->description(fn (ChatLog $record) => $record->logged_at?->diffForHumans()),
            ])
            ->defaultSort('logged_at', 'desc')
            ->striped()
            ->poll('30s')
            ->emptyStateHeading('Sem eventos')
            ->emptyStateDescription('Ainda não há registos de operação ou os filtros não coincidem com nenhuma linha.')
            ->emptyStateIcon('heroicon-o-clipboard-document-list')
            ->filters([
                Tables\Filters\SelectFilter::make('log_type')
                    ->label('Tipo')
                    ->options(fn (): array => ChatLog::query()
                        ->select('log_type')
                        ->distinct()
                        ->whereNotNull('log_type')
                        ->orderBy('log_type')
                        ->pluck('log_type', 'log_type')
                        ->all())
                    ->searchable()
                    ->multiple()
                    ->indicator('Tipo'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Abrir'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChatLogs::route('/'),
            'view' => Pages\ViewChatLog::route('/{record}'),
        ];
    }
}
