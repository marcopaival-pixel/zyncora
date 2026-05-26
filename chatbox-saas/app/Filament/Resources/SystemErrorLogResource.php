<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SystemErrorLogResource\Pages;
use App\Models\SystemErrorLog;
use Filament\Forms;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SystemErrorLogResource extends Resource
{
    protected static ?string $model = SystemErrorLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-bug-ant';

    protected static ?string $navigationGroup = 'Gestão do Sistema';

    protected static ?string $modelLabel = 'Log de Erro';

    protected static ?string $pluralModelLabel = 'Logs de Erros';

    protected static ?int $navigationSort = 10;

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
        return parent::getEloquentQuery()->with(['user', 'company']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Quando')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable()
                    ->description(fn (SystemErrorLog $record) => $record->created_at?->diffForHumans())
                    ->color('gray'),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'SQL' => 'danger',
                        'Validation' => 'warning',
                        'API' => 'info',
                        'Exception' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('message')
                    ->label('Mensagem')
                    ->limit(55)
                    ->wrap()
                    ->searchable()
                    ->tooltip(fn (SystemErrorLog $record): string => $record->message),

                Tables\Columns\TextColumn::make('company.name')
                    ->label('Empresa')
                    ->placeholder('—')
                    ->toggleable()
                    ->searchable()
                    ->visible(fn () => auth()->user()?->isPlatformAdmin() ?? false),

                Tables\Columns\TextColumn::make('url')
                    ->label('URL')
                    ->limit(36)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->tooltip(fn (SystemErrorLog $record): ?string => $record->url),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Utilizador')
                    ->placeholder('Sistema / visitante')
                    ->searchable(),

                Tables\Columns\TextColumn::make('status_code')
                    ->label('HTTP')
                    ->badge()
                    ->color(fn (?int $state): string => $state === null ? 'gray' : ($state >= 500 ? 'danger' : ($state >= 400 ? 'warning' : 'success')))
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->poll('60s')
            ->emptyStateHeading('Nenhum erro registado')
            ->emptyStateDescription('Não há falhas no período ou os filtros estão demasiado restritivos.')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo de erro')
                    ->options([
                        'SQL' => 'SQL',
                        'Exception' => 'Exception',
                        'API' => 'API',
                        'Validation' => 'Validation',
                    ])
                    ->multiple()
                    ->indicator('Tipo'),
                Tables\Filters\Filter::make('period')
                    ->label('Período')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Desde')->native(false),
                        Forms\Components\DatePicker::make('until')->label('Até')->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date): Builder => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date): Builder => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Detalhes')
                    ->modalHeading('Detalhe do erro')
                    ->modalWidth(MaxWidth::SevenExtraLarge)
                    ->slideOver(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Resumo')
                    ->description('Identificação rápida do incidente.')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('type')
                                    ->label('Tipo')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'SQL' => 'danger',
                                        'Validation' => 'warning',
                                        'API' => 'info',
                                        'Exception' => 'danger',
                                        default => 'gray',
                                    }),
                                TextEntry::make('status_code')
                                    ->label('HTTP')
                                    ->badge()
                                    ->color(fn (?int $state): string => $state === null ? 'gray' : ($state >= 500 ? 'danger' : ($state >= 400 ? 'warning' : 'success'))),
                                TextEntry::make('created_at')
                                    ->label('Data e hora')
                                    ->dateTime('d/m/Y H:i:s'),
                            ]),
                        TextEntry::make('message')
                            ->label('Mensagem')
                            ->columnSpanFull()
                            ->weight('bold')
                            ->color('danger')
                            ->copyable()
                            ->copyMessage('Mensagem copiada'),
                    ]),

                Section::make('Pedido e utilizador')
                    ->description('URL, método, IP e sessão.')
                    ->icon('heroicon-o-globe-alt')
                    ->schema([
                        TextEntry::make('url')
                            ->label('URL')
                            ->copyable()
                            ->columnSpanFull(),
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('method')
                                    ->label('Método')
                                    ->badge()
                                    ->color('info'),
                                TextEntry::make('ip_address')
                                    ->label('IP')
                                    ->copyable(),
                                TextEntry::make('company.name')
                                    ->label('Empresa')
                                    ->placeholder('—')
                                    ->visible(fn (SystemErrorLog $record): bool => filled($record->company_id)),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('file')
                                    ->label('Ficheiro')
                                    ->copyable()
                                    ->fontFamily(FontFamily::Mono),
                                TextEntry::make('line')
                                    ->label('Linha')
                                    ->badge()
                                    ->color('gray'),
                            ]),
                        TextEntry::make('user.name')
                            ->label('Utilizador autenticado')
                            ->placeholder('Nenhum'),
                    ])
                    ->collapsible(),

                Section::make('Stack trace')
                    ->description('Útil para localizar a origem no código.')
                    ->icon('heroicon-o-code-bracket')
                    ->schema([
                        TextEntry::make('stack_trace')
                            ->label('')
                            ->formatStateUsing(fn ($state) => $state)
                            ->fontFamily(FontFamily::Mono)
                            ->columnSpanFull()
                            ->copyable()
                            ->copyMessage('Stack copiado')
                            ->extraAttributes([
                                'class' => 'max-h-[28rem] overflow-x-auto overflow-y-auto whitespace-pre-wrap rounded-lg border border-gray-200 bg-gray-950 p-4 text-xs text-gray-100 dark:border-white/10 dark:bg-gray-950',
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Payload da requisição')
                    ->description('Corpo ou parâmetros associados ao pedido (pode conter dados sensíveis).')
                    ->icon('heroicon-o-document-magnifying-glass')
                    ->schema([
                        TextEntry::make('payload')
                            ->label('')
                            ->state(fn (SystemErrorLog $record): string => json_encode($record->payload ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))
                            ->fontFamily(FontFamily::Mono)
                            ->columnSpanFull()
                            ->copyable()
                            ->extraAttributes([
                                'class' => 'max-h-80 overflow-auto whitespace-pre-wrap rounded-lg border border-gray-200 bg-gray-900 p-4 text-xs text-emerald-100 dark:border-white/10',
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSystemErrorLogs::route('/'),
        ];
    }
}
