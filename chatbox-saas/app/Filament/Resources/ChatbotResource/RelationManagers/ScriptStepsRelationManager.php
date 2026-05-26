<?php

namespace App\Filament\Resources\ChatbotResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ScriptStepsRelationManager extends RelationManager
{
    protected static string $relationship = 'scriptSteps';

    protected static ?string $title = 'Roteiro de mensagens';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('step_order')->numeric()->required()->default(0),
                Forms\Components\Textarea::make('prompt')->rows(2)->columnSpanFull(),
                Forms\Components\Textarea::make('response')->rows(3)->columnSpanFull(),
                Forms\Components\Select::make('response_type')
                    ->options([
                        'text' => 'Texto',
                        'options' => 'Opções',
                        'handoff' => 'Transferir atendente',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('next_step_key'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('step_order')
            ->columns([
                Tables\Columns\TextColumn::make('step_order')->sortable(),
                Tables\Columns\TextColumn::make('prompt')->limit(40)->wrap(),
                Tables\Columns\TextColumn::make('response_type')->badge(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('step_order');
    }
}
