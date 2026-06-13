<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LgpdRequestResource\Pages;
use App\Models\LgpdRequest;
use App\Services\LgpdService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LgpdRequestResource extends Resource
{
    protected static ?string $model = LgpdRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path-rounded-square';

    protected static ?string $navigationGroup = 'Configurações & Auditoria';

    protected static ?string $modelLabel = 'Solicitação LGPD';

    protected static ?string $pluralModelLabel = 'Solicitações LGPD';

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
                Forms\Components\Section::make('Detalhes da Solicitação')
                    ->schema([
                        Forms\Components\TextInput::make('customer_id')
                            ->label('ID do Cliente')
                            ->disabled(),
                        Forms\Components\Select::make('type')
                            ->label('Tipo')
                            ->options([
                                'export' => 'Exportação de Dados',
                                'delete' => 'Exclusão de Dados',
                                'anonymize' => 'Anonimização',
                            ])
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pendente',
                                'processing' => 'Em Processamento',
                                'completed' => 'Concluído',
                                'canceled' => 'Cancelado',
                            ])
                            ->required(),
                        Forms\Components\DateTimePicker::make('completed_at')
                            ->label('Concluído em'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer_id')
                    ->label('ID Cliente')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'export' => 'info',
                        'delete' => 'danger',
                        'anonymize' => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'export' => 'Exportação',
                        'delete' => 'Exclusão',
                        'anonymize' => 'Anonimização',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'processing' => 'warning',
                        'completed' => 'success',
                        'canceled' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Solicitado em')
                    ->since()
                    ->sortable()
                    ->tooltip(fn (LgpdRequest $record): ?string => $record->created_at?->format('d/m/Y H:i')),
            ])
            ->defaultSort('created_at', 'desc')
            ->searchPlaceholder('Pesquisar ID cliente ou estado…')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'export' => 'Exportação',
                        'delete' => 'Exclusão',
                        'anonymize' => 'Anonimização',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendente',
                        'processing' => 'Em processamento',
                        'completed' => 'Concluído',
                        'canceled' => 'Cancelado',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('process')
                    ->label('Processar')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record, LgpdService $lgpdService) {
                        $record->update(['status' => 'completed', 'completed_at' => now()]);
                        $lgpdService->log('processed_lgpd_request', $record);
                    }),
            ])
            ->bulkActions([
                //
            ])
            ->emptyStateHeading('Sem solicitações LGPD')
            ->emptyStateDescription('Pedidos de exportação, eliminação ou anonimização aparecem aqui.')
            ->emptyStateIcon('heroicon-o-arrow-path-rounded-square');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLgpdRequests::route('/'),
            'edit' => Pages\EditLgpdRequest::route('/{record}/edit'),
        ];
    }
}
