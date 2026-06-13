<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentHistoryResource\Pages;
use App\Filament\Resources\PaymentHistoryResource\RelationManagers;
use App\Models\PaymentHistory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentHistoryResource extends Resource
{
    protected static ?string $model = PaymentHistory::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';

    protected static ?string $navigationGroup = 'Plataforma';

    protected static ?string $modelLabel = 'Histórico Financeiro';

    protected static ?string $pluralModelLabel = 'Histórico e Recibos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detalhes do Histórico Financeiro')
                    ->description('Informe os detalhes do pagamento ou recibo.')
                    ->schema([
                        Forms\Components\Select::make('company_id')
                            ->relationship('company', 'name')
                            ->required()
                            ->label('Empresa')
                            ->searchable()
                            ->columnSpanFull()
                            ->disabled(fn() => !auth()->user()->isPlatformAdmin()),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('type')
                                    ->required()
                                    ->label('Tipo (ex: plan_subscription)'),
                                Forms\Components\TextInput::make('amount')
                                    ->required()
                                    ->numeric()
                                    ->prefix('R$')
                                    ->label('Valor'),
                                Forms\Components\TextInput::make('status')
                                    ->required()
                                    ->label('Status'),
                                Forms\Components\TextInput::make('gateway')
                                    ->label('Gateway'),
                                Forms\Components\TextInput::make('external_id')
                                    ->label('ID Externo')
                                    ->columnSpanFull(),
                                Forms\Components\DateTimePicker::make('paid_at')
                                    ->label('Data do Pagamento')
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                if (!auth()->user()->isPlatformAdmin()) {
                    $query->where('company_id', auth()->user()->company_id);
                }
            })
            ->columns([
                Tables\Columns\TextColumn::make('company.name')
                    ->sortable()
                    ->searchable()
                    ->visible(fn() => auth()->user()->isPlatformAdmin()),
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('BRL')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'paid',
                        'warning' => 'pending',
                        'danger' => 'failed',
                    ]),
                Tables\Columns\TextColumn::make('gateway')
                    ->searchable(),
                Tables\Columns\TextColumn::make('paid_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->slideOver(),
                Tables\Actions\Action::make('download_invoice')
                    ->label('NFS-e')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn (PaymentHistory $record) => $record->invoices()->first()?->pdf_url)
                    ->openUrlInNewTab()
                    ->visible(fn (PaymentHistory $record) => $record->invoices()->whereNotNull('pdf_url')->exists()),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePaymentHistories::route('/'),
        ];
    }
}

