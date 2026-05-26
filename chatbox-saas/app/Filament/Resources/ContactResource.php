<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactResource\Pages;
use App\Models\Contact;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ContactResource extends Resource
{
    protected static ?string $model = Contact::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?string $modelLabel = 'Contacto';

    protected static ?string $pluralModelLabel = 'Contactos';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 4;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->canChat() ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->canChat() ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['tags'])
            ->withCount(['conversations', 'deals']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identificação')
                    ->description('Dados usados em conversas, negócios e relatórios.')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome completo')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ex.: Maria Silva'),
                        Forms\Components\TextInput::make('phone')
                            ->label('WhatsApp / telefone')
                            ->tel()
                            ->required()
                            ->placeholder('+351 …')
                            ->helperText('Deve ser único na sua empresa (inclui indicativo).'),
                        Forms\Components\TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->maxLength(255)
                            ->placeholder('nome@empresa.com'),
                    ])->columns(2),

                Forms\Components\Section::make('Segmentação')
                    ->description('Etiquetas para filtros e campanhas.')
                    ->icon('heroicon-o-tag')
                    ->schema([
                        Forms\Components\Select::make('tags')
                            ->label('Etiquetas')
                            ->relationship('tags', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),
                    ]),

                Forms\Components\Section::make('Campos Personalizados')
                    ->description('Informações extra (ex: Empresa, Cargo, NIF, etc.)')
                    ->icon('heroicon-o-bars-3-bottom-left')
                    ->collapsed()
                    ->schema([
                        Forms\Components\KeyValue::make('custom_fields')
                            ->label('Dados Adicionais')
                            ->keyLabel('Campo')
                            ->valueLabel('Valor')
                            ->addActionLabel('Adicionar Campo')
                            ->reorderable(),
                    ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Contacto')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('Nome'),
                        Infolists\Components\TextEntry::make('phone')
                            ->label('Telefone / WhatsApp')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('email')
                            ->label('E-mail')
                            ->placeholder('—')
                            ->copyable(),
                    ])->columns(2),

                Infolists\Components\Section::make('Relacionamento')
                    ->icon('heroicon-o-chart-bar')
                    ->schema([
                        Infolists\Components\TextEntry::make('conversations_count')
                            ->label('Conversas')
                            ->numeric(),
                        Infolists\Components\TextEntry::make('deals_count')
                            ->label('Negócios no CRM')
                            ->numeric(),
                        Infolists\Components\TextEntry::make('tags')
                            ->label('Etiquetas')
                            ->state(fn (Contact $record): string => $record->tags->pluck('name')->filter()->join(', ') ?: '—'),
                    ])->columns(3),

                Infolists\Components\Section::make('Registo')
                    ->collapsed()
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Criado em')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Última atualização')
                            ->dateTime(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar_url')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn (Contact $record): string => 'https://ui-avatars.com/api/?name='.urlencode($record->name).'&background=6366f1&color=fff'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Contact $record): ?string => $record->email ?: null),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefone')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                Tables\Columns\TagsColumn::make('tags.name')
                    ->label('Etiquetas')
                    ->limit(4),
                Tables\Columns\TextColumn::make('conversations_count')
                    ->label('Conversas')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('deals_count')
                    ->label('Negócios')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('updated_at', 'desc')
            ->searchPlaceholder('Pesquisar nome, e-mail ou telefone…')
            ->filters([
                Tables\Filters\SelectFilter::make('tags')
                    ->relationship('tags', 'name')
                    ->multiple()
                    ->preload()
                    ->label('Etiquetas'),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')->label('Criado a partir de'),
                        Forms\Components\DatePicker::make('created_until')->label('Criado até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Ainda não há contactos')
            ->emptyStateDescription('Os contactos aparecem aqui quando são associados a conversas ou criados manualmente.')
            ->emptyStateIcon('heroicon-o-user-group');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContacts::route('/'),
            'view' => Pages\ViewContact::route('/{record}'),
            'edit' => Pages\EditContact::route('/{record}/edit'),
        ];
    }
}
