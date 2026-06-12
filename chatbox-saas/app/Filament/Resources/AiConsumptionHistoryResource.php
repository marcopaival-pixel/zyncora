<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AiConsumptionHistoryResource\Pages;
use App\Filament\Resources\AiConsumptionHistoryResource\RelationManagers;
use App\Models\AiConsumptionHistory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AiConsumptionHistoryResource extends Resource
{
    protected static ?string $model = AiConsumptionHistory::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Plataforma';

    protected static ?string $modelLabel = 'Histórico de Consumo (IA)';

    protected static ?string $pluralModelLabel = 'Consumo de IA';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('company_id')
                    ->relationship('company', 'name')
                    ->required(),
                Forms\Components\DatePicker::make('period_start')
                    ->required(),
                Forms\Components\DatePicker::make('period_end')
                    ->required(),
                Forms\Components\TextInput::make('conversations_contracted')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('conversations_used')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('credits_purchased')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('period_start')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('period_end')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('conversations_contracted')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('conversations_used')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('credits_purchased')
                    ->numeric()
                    ->sortable(),
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
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ManageAiConsumptionHistories::route('/'),
        ];
    }
}

