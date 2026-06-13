<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\HelpArticleResource\Pages;
use App\Models\HelpArticle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class HelpArticleResource extends Resource
{
    protected static ?string $model = HelpArticle::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    
    protected static ?string $modelLabel = 'Artigo de Ajuda';
    protected static ?string $pluralModelLabel = 'Artigos de Ajuda';
    protected static ?string $navigationGroup = 'Gestão do Sistema';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações Principais')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('help_category_id')
                            ->relationship('category', 'name')
                            ->required()
                            ->label('Categoria'),
                            
                        Forms\Components\TextInput::make('module')
                            ->label('Módulo no Sistema')
                            ->helperText('Ex: Geral, CRM, Atendimento. (Usado para vincular à tela correta)')
                            ->maxLength(255)
                            ->required(),

                        Forms\Components\TextInput::make('title')
                            ->label('Título do Artigo')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                            
                        Forms\Components\Textarea::make('description')
                            ->label('Descrição Curta')
                            ->columnSpanFull()
                            ->rows(2),
                    ]),

                Forms\Components\Section::make('Conteúdo e Exemplos')
                    ->schema([
                        Forms\Components\RichEditor::make('content')
                            ->label('Conteúdo do Artigo')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\KeyValue::make('examples_by_segment')
                            ->label('Exemplos Práticos por Segmento')
                            ->keyLabel('Segmento (Ex: default, clinica, imobiliaria)')
                            ->valueLabel('Exemplo Prático')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Configurações')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('order_column')
                            ->label('Ordem de Exibição')
                            ->required()
                            ->numeric()
                            ->default(0),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Ativo')
                            ->default(true)
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoria')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable(),
                Tables\Columns\TextColumn::make('module')
                    ->label('Módulo')
                    ->badge()
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('order_column')
                    ->label('Ordem')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            ])
            ->defaultSort('order_column', 'asc');
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
            'index' => Pages\ListHelpArticles::route('/'),
            'create' => Pages\CreateHelpArticle::route('/create'),
            'edit' => Pages\EditHelpArticle::route('/{record}/edit'),
        ];
    }
}
