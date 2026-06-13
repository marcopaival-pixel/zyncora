<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AiCreditPurchaseResource\Pages;
use App\Models\AiCreditPurchase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AiCreditPurchaseResource extends Resource
{
    protected static ?string $model = AiCreditPurchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Plataforma';

    protected static ?string $modelLabel = 'Compra de Créditos';

    protected static ?string $pluralModelLabel = 'Compras de Créditos (IA)';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detalhes da Compra')
                    ->description('Informe os detalhes da compra de créditos de IA.')
                    ->schema([
                        Forms\Components\Select::make('company_id')
                            ->relationship('company', 'name')
                            ->required()
                            ->label('Empresa')
                            ->searchable()
                            ->columnSpanFull(),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('package_name')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Nome do Pacote'),
                                Forms\Components\TextInput::make('conversations_added')
                                    ->required()
                                    ->numeric()
                                    ->label('Conversas Adicionadas'),
                                Forms\Components\TextInput::make('price')
                                    ->required()
                                    ->numeric()
                                    ->prefix('R$')
                                    ->label('Preço'),
                                Forms\Components\TextInput::make('payment_method')
                                    ->maxLength(255)
                                    ->default(null)
                                    ->label('Método de Pagamento'),
                                Forms\Components\TextInput::make('status')
                                    ->required()
                                    ->maxLength(255)
                                    ->default('completed')
                                    ->label('Status')
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('package_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('conversations_added')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->slideOver(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageAiCreditPurchases::route('/'),
        ];
    }
}
