<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupportTicketResource\Pages;
use App\Models\SupportTicket;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SupportTicketResource extends Resource
{
    protected static ?string $model = SupportTicket::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Gestão Executiva';

    protected static ?string $modelLabel = 'Chamado de Suporte';

    protected static ?string $pluralModelLabel = 'Chamados de Suporte';

    protected static ?int $navigationSort = 3;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isPlatformAdmin() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('company_id')
                    ->relationship('company', 'name')
                    ->label('Empresa')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Solicitante')
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('subject')
                    ->label('Assunto')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('description')
                    ->label('Descrição do Problema')
                    ->required()
                    ->rows(5)
                    ->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'open' => 'Aberto',
                        'in_progress' => 'Em Andamento',
                        'resolved' => 'Resolvido',
                        'closed' => 'Fechado',
                    ])
                    ->default('open')
                    ->required(),
                Forms\Components\Select::make('priority')
                    ->label('Prioridade')
                    ->options([
                        'low' => 'Baixa',
                        'medium' => 'Média',
                        'high' => 'Alta',
                        'critical' => 'Crítica',
                    ])
                    ->default('medium')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company.name')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject')
                    ->label('Assunto')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'danger',
                        'in_progress' => 'warning',
                        'resolved', 'closed' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'open' => 'Aberto',
                        'in_progress' => 'Em Andamento',
                        'resolved' => 'Resolvido',
                        'closed' => 'Fechado',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('priority')
                    ->label('Prioridade')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'low' => 'gray',
                        'medium' => 'info',
                        'high' => 'warning',
                        'critical' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'open' => 'Aberto',
                        'in_progress' => 'Em Andamento',
                        'resolved' => 'Resolvido',
                        'closed' => 'Fechado',
                    ]),
                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'low' => 'Baixa',
                        'medium' => 'Média',
                        'high' => 'Alta',
                        'critical' => 'Crítica',
                    ]),
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
            'index' => Pages\ListSupportTickets::route('/'),
            'create' => Pages\CreateSupportTicket::route('/create'),
            'edit' => Pages\EditSupportTicket::route('/{record}/edit'),
        ];
    }
}
