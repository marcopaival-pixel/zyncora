<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\CouponResource\Pages;
use App\Models\Coupon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detalhes do Cupom')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('Código do Cupom')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('discount_type')
                            ->label('Tipo de Desconto')
                            ->options([
                                'percent' => 'Porcentagem (%)',
                                'fixed' => 'Valor Fixo (R$)',
                            ])
                            ->default('percent')
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('discount_value')
                            ->label('Valor do Desconto')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Ativo')
                            ->default(true)
                            ->inline(false),
                    ])->columns(2),

                Forms\Components\Section::make('Regras e Limites')
                    ->schema([
                        Forms\Components\DateTimePicker::make('valid_from')
                            ->label('Válido a partir de')
                            ->nullable(),
                        Forms\Components\DateTimePicker::make('valid_until')
                            ->label('Válido até')
                            ->nullable(),
                        Forms\Components\TextInput::make('max_uses')
                            ->label('Limite de Usos (Opcional)')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->nullable(),
                        Forms\Components\TextInput::make('used_count')
                            ->label('Vezes Utilizado')
                            ->disabled()
                            ->default(0),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Código')
                    ->searchable(),
                Tables\Columns\TextColumn::make('discount_type')
                    ->label('Tipo')
                    ->formatStateUsing(fn ($state) => $state === 'percent' ? 'Porcentagem' : 'Fixo'),
                Tables\Columns\TextColumn::make('discount_value')
                    ->label('Valor'),
                Tables\Columns\TextColumn::make('used_count')
                    ->label('Usos')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('valid_until')
                    ->label('Validade')
                    ->dateTime('d/m/Y H:i'),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Ativo'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
