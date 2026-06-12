<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AiAuditLogResource\Pages;
use App\Filament\Resources\AiAuditLogResource\RelationManagers;
use App\Models\AiAuditLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AiAuditLogResource extends Resource
{
    protected static ?string $model = AiAuditLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-magnifying-glass';

    protected static ?string $navigationGroup = 'Configurações & Auditoria';

    protected static ?string $modelLabel = 'Auditoria de IA';

    protected static ?string $pluralModelLabel = 'Auditorias de IA';

    protected static ?int $navigationSort = 4;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('chatbot.name')
                    ->label('Robô')
                    ->disabled(),
                Forms\Components\TextInput::make('conversation.customer_name')
                    ->label('Cliente')
                    ->disabled(),
                Forms\Components\TextInput::make('tokens_used')
                    ->label('Créditos / Tokens')
                    ->disabled(),
                Forms\Components\TextInput::make('status')
                    ->label('Status')
                    ->disabled(),
                Forms\Components\Textarea::make('user_message')
                    ->label('Mensagem do Cliente')
                    ->columnSpanFull()
                    ->disabled(),
                Forms\Components\Textarea::make('ai_response')
                    ->label('Resposta da IA')
                    ->columnSpanFull()
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('chatbot.name')
                    ->label('Robô')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user_message')
                    ->label('Pergunta')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('ai_response')
                    ->label('Resposta Gerada')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                        'fallback' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListAiAuditLogs::route('/'),
            'create' => Pages\CreateAiAuditLog::route('/create'),
            'view' => Pages\ViewAiAuditLog::route('/{record}'),
            'edit' => Pages\EditAiAuditLog::route('/{record}/edit'),
        ];
    }
}

