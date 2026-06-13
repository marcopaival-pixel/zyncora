<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Financeiro';

    protected static ?string $modelLabel = 'Fatura';

    protected static ?string $pluralModelLabel = 'Faturas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('company_id')
                    ->relationship('company', 'name')
                    ->label('Empresa')
                    ->required(),
                Forms\Components\TextInput::make('invoice_number')
                    ->label('Número da Fatura')
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->label('Valor (R$)')
                    ->numeric()
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'open' => 'Aberta',
                        'paid' => 'Paga',
                        'overdue' => 'Vencida',
                        'canceled' => 'Cancelada',
                        'refunded' => 'Reembolsada',
                        'failed' => 'Falhou',
                    ])
                    ->required(),
                Forms\Components\DatePicker::make('due_date')
                    ->label('Vencimento')
                    ->required(),
                Forms\Components\DatePicker::make('paid_at')
                    ->label('Data de Pagamento'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Número')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('company.name')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Valor')
                    ->money('BRL')
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Vencimento')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'info',
                        'paid' => 'success',
                        'overdue' => 'danger',
                        'canceled' => 'gray',
                        'refunded' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'open' => 'Aberta',
                        'paid' => 'Paga',
                        'overdue' => 'Vencida',
                        'canceled' => 'Cancelada',
                        'refunded' => 'Reembolsada',
                        'failed' => 'Falhou',
                        default => ucfirst($state),
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'open' => 'Aberta',
                        'paid' => 'Paga',
                        'overdue' => 'Vencida',
                        'canceled' => 'Cancelada',
                        'refunded' => 'Reembolsada',
                        'failed' => 'Falhou',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('download_pdf')
                    ->label('Baixar PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn (Invoice $record) => response()->download($record->pdf_path ?? ''))
                    ->hidden(fn (Invoice $record) => empty($record->pdf_path)),
                Tables\Actions\Action::make('cancel_invoice')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (Invoice $record) => $record->update(['status' => 'canceled']))
                    ->visible(fn (Invoice $record) => in_array($record->status, ['open', 'overdue'])),
                Tables\Actions\Action::make('refund_invoice')
                    ->label('Reembolsar')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(fn (Invoice $record) => $record->update(['status' => 'refunded']))
                    ->visible(fn (Invoice $record) => $record->status === 'paid'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
