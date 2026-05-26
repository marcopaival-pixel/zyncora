<?php

namespace App\Filament\Resources\ConversationResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class MessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'messages';

    protected static ?string $title = 'Mensagens';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('sender_type')->badge(),
                Tables\Columns\TextColumn::make('body')->wrap()->searchable(),
                Tables\Columns\TextColumn::make('message_type')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('sent_at')->dateTime()->sortable(),
            ])
            ->defaultSort('id')
            ->paginated([25, 50, 100]);
    }
}
