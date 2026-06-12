<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OpportunityResource\Pages;
use App\Models\Opportunity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OpportunityResource extends Resource
{
    protected static ?string $model = Opportunity::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'CRM / Oportunidades';
    protected static ?string $pluralModelLabel = 'Oportunidades de Venda';
    protected static ?string $navigationGroup = 'Atendimento';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('lead_name')
                    ->label('Nome do Lead')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->label('Fase (Kanban)')
                    ->options([
                        'new' => 'Novo Lead',
                        'negotiating' => 'Em Negociação',
                        'won' => 'Ganho (Fechado)',
                        'lost' => 'Perdido',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('value')
                    ->label('Valor (R$)')
                    ->numeric()
                    ->prefix('R$'),
                Forms\Components\TextInput::make('ai_score')
                    ->label('Score IA (0-100)')
                    ->numeric()
                    ->disabled(),
                Forms\Components\Textarea::make('summary')
                    ->label('Resumo da IA')
                    ->columnSpanFull()
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('lead_name')
                    ->label('Lead')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Fase')
                    ->badge()
                    ->colors([
                        'primary' => 'new',
                        'warning' => 'negotiating',
                        'success' => 'won',
                        'danger' => 'lost',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'new' => 'Novo Lead',
                        'negotiating' => 'Negociando',
                        'won' => 'Ganho',
                        'lost' => 'Perdido',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('value')
                    ->label('Valor')
                    ->money('BRL')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ai_score')
                    ->label('Score IA')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 80 => 'success',
                        $state >= 50 => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Fase')
                    ->options([
                        'new' => 'Novos Leads',
                        'negotiating' => 'Em Negociação',
                        'won' => 'Ganhos',
                        'lost' => 'Perdidos',
                    ])
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('view_chat')
                    ->label('Ver Chat')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->url(fn (Opportunity $record): string => $record->conversation_id 
                        ? ConversationResource::getUrl('index') . '?tableFilters[id][value]=' . $record->conversation_id
                        : '#')
                    ->visible(fn (Opportunity $record): bool => $record->conversation_id !== null),
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
            'index' => Pages\ManageOpportunities::route('/'),
        ];
    }
}
