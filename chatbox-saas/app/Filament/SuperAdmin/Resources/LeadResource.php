<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\LeadResource\Pages;
use App\Models\Lead;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Comercial';

    protected static ?string $modelLabel = 'Lead de Contato';

    protected static ?string $pluralModelLabel = 'Leads (Contatos)';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações do Contato')
                    ->description('Dados preenchidos pelo lead no formulário do site.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\Textarea::make('message')
                            ->label('Mensagem Original')
                            ->columnSpanFull()
                            ->rows(5)
                            ->disabled()
                            ->dehydrated(false),
                    ])->columns(2),

                Forms\Components\Section::make('Gestão Comercial')
                    ->description('Atualize o progresso deste contato.')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status do Lead')
                            ->options([
                                'new' => 'Novo (Não Lido)',
                                'contacted' => 'Em Atendimento / Contatado',
                                'closed' => 'Finalizado / Arquivado',
                            ])
                            ->required()
                            ->native(true)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'warning',
                        'contacted' => 'success',
                        'closed' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'new' => 'Novo',
                        'contacted' => 'Contatado',
                        'closed' => 'Fechado',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'new' => 'Novo',
                        'contacted' => 'Contatado',
                        'closed' => 'Fechado/Arquivado',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('markContacted')
                    ->label('Marcar Contatado')
                    ->icon('heroicon-o-phone')
                    ->color('success')
                    ->action(fn (Lead $record) => $record->update(['status' => 'contacted']))
                    ->visible(fn (Lead $record) => $record->status === 'new'),
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
            'index' => Pages\ListLeads::route('/'),
            'edit' => Pages\EditLead::route('/{record}/edit'),
        ];
    }
}
