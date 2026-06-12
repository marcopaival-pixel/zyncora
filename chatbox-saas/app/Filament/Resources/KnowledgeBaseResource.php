<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KnowledgeBaseResource\Pages;
use App\Models\KnowledgeBase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use App\Jobs\ScrapeUrlForKnowledgeBase;
use Filament\Notifications\Notification;

class KnowledgeBaseResource extends Resource
{
    protected static ?string $model = KnowledgeBase::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Automação';

    protected static ?string $modelLabel = 'Snippet';

    protected static ?string $pluralModelLabel = 'Base de conhecimento';

    protected static ?int $navigationSort = 3;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->canManageIntegrations() ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['company']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Conteúdo')
                    ->description('Texto que a IA pode usar como contexto nas respostas.')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\Select::make('company_id')
                            ->relationship('company', 'name')
                            ->label('Empresa')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->visible(fn (): bool => auth()->user()?->isPlatformAdmin() ?? false),

                        Forms\Components\TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ex.: Preços 2026, política de devoluções'),

                        Forms\Components\RichEditor::make('content')
                            ->label('Texto')
                            ->required(fn (Forms\Get $get): bool => $get('source_type') === 'text')
                            ->hidden(fn (Forms\Get $get): bool => $get('source_type') === 'url' && empty($get('content')))
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'h2',
                                'h3',
                                'bulletList',
                                'orderedList',
                                'blockquote',
                                'redo',
                                'undo',
                            ])
                            ->helperText('Quanto mais claro e estruturado, melhor a IA aproveita o contexto.'),
                    ]),

                Forms\Components\Section::make('Origem (opcional)')
                    ->description('Indique se o texto veio de um URL ou ficheiro importado.')
                    ->icon('heroicon-o-link')
                    ->collapsed()
                    ->schema([
                        Forms\Components\Select::make('source_type')
                            ->label('Tipo de origem')
                            ->options([
                                'text' => 'Texto direto (manual)',
                                'url' => 'Página web (Auto-Sync)',
                                'file' => 'Ficheiro',
                            ])
                            ->default('text')
                            ->live()
                            ->native(false),
                        Forms\Components\TextInput::make('source_path')
                            ->label('URL')
                            ->maxLength(2048)
                            ->placeholder('https://...')
                            ->visible(fn (Forms\Get $get) => in_array($get('source_type'), ['url']))
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('source_file')
                            ->label('Upload de Documento (TXT, PDF, DOCX)')
                            ->acceptedFileTypes(['text/plain', 'application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->visible(fn (Forms\Get $get) => $get('source_type') === 'file')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Publicação')
                    ->description('Apenas snippets ativos entram no contexto enviado à IA.')
                    ->icon('heroicon-o-signal')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Ativo para a IA')
                            ->default(true)
                            ->inline(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company.name')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->visible(fn (): bool => auth()->user()?->isPlatformAdmin() ?? false),

                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (KnowledgeBase $record): ?string => Str::limit(strip_tags((string) ($record->content ?? '')), 80)),

                Tables\Columns\TextColumn::make('source_type')
                    ->label('Origem')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'url' => 'info',
                        'file' => 'warning',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('IA')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-pause-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('tokens_count')
                    ->label('Tokens (Custo)')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state > 2000 => 'danger',
                        $state > 500 => 'warning',
                        default => 'success',
                    })
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado')
                    ->since()
                    ->sortable()
                    ->tooltip(fn (KnowledgeBase $record): ?string => $record->updated_at?->format('d/m/Y H:i')),
            ])
            ->defaultSort('updated_at', 'desc')
            ->searchPlaceholder('Pesquisar título ou conteúdo…')
            ->poll('60s')
            ->emptyStateHeading('Nenhum snippet na base')
            ->emptyStateDescription('Adicione títulos e textos para a IA citar preços, políticas e FAQs com consistência.')
            ->emptyStateIcon('heroicon-o-academic-cap')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->placeholder('Todos')
                    ->trueLabel('Ativos')
                    ->falseLabel('Inativos'),
                Tables\Filters\SelectFilter::make('source_type')
                    ->label('Origem')
                    ->options([
                        'text' => 'Texto manual',
                        'url' => 'URL',
                        'file' => 'Ficheiro',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('sync')
                    ->label('Sincronizar RAG')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->visible(fn (KnowledgeBase $record): bool => $record->source_type === 'url')
                    ->action(function (KnowledgeBase $record) {
                        ScrapeUrlForKnowledgeBase::dispatch($record);
                        Notification::make()
                            ->title('Sincronização iniciada')
                            ->body('A URL está sendo raspada em background. O texto será atualizado em breve.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('preview')
                    ->label('Conteúdo Bruto')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn (KnowledgeBase $record) => 'Conteúdo: ' . $record->title)
                    ->modalContent(fn (KnowledgeBase $record) => view('filament.components.html-preview', ['html' => $record->content]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fechar'),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListKnowledgeBases::route('/'),
            'create' => Pages\CreateKnowledgeBase::route('/create'),
            'edit' => Pages\EditKnowledgeBase::route('/{record}/edit'),
        ];
    }
}

