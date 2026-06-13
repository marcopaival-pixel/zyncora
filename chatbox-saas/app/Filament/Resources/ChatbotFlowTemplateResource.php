<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChatbotFlowTemplateResource\Pages;
use App\Models\ChatbotFlowTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ChatbotFlowTemplateResource extends Resource
{
    protected static ?string $model = ChatbotFlowTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Plataforma';

    protected static ?string $modelLabel = 'Modelo de fluxo';

    protected static ?string $pluralModelLabel = 'Modelos de fluxo (templates)';

    protected static ?string $navigationLabel = 'Fluxos — templates';

    protected static ?int $navigationSort = 12;

    /**
     * Conta nós Drawflow em flow_data (estrutura típica: drawflow.*.data).
     */
    public static function countFlowNodes(?array $flowData): int
    {
        if (! is_array($flowData) || $flowData === []) {
            return 0;
        }

        $drawflow = $flowData['drawflow'] ?? null;
        if (! is_array($drawflow)) {
            return 0;
        }

        $total = 0;
        foreach ($drawflow as $module) {
            $nodes = is_array($module) ? ($module['data'] ?? null) : null;
            if (is_array($nodes)) {
                $total += count($nodes);
            }
        }

        return $total;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['company']);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isPlatformAdmin() ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->isPlatformAdmin() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identificação')
                    ->description('Metadados do modelo reutilizável. Modelos públicos ficam disponíveis como referência na plataforma.')
                    ->icon('heroicon-o-rectangle-stack')
                    ->schema([
                        Forms\Components\Select::make('company_id')
                            ->relationship('company', 'name')
                            ->label('Empresa (opcional)')
                            ->placeholder('Vazio = modelo apenas da plataforma')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->visible(fn () => auth()->user()?->isPlatformAdmin() ?? false),
                        Forms\Components\TextInput::make('name')
                            ->label('Nome do modelo')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ex.: Fluxo de boas-vindas v2'),
                        Forms\Components\Textarea::make('description')
                            ->label('Descrição')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('category')
                            ->label('Categoria')
                            ->required()
                            ->maxLength(255)
                            ->default('general')
                            ->placeholder('general, vendas, suporte…'),
                        Forms\Components\Toggle::make('is_public')
                            ->label('Modelo público na plataforma')
                            ->helperText('Se ativo, outros podem ver o modelo como referência (conforme política interna).')
                            ->default(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Dados do fluxo (JSON)')
                    ->description('Estrutura exportada do construtor de fluxos (Drawflow). Cole ou edite JSON válido.')
                    ->icon('heroicon-o-code-bracket')
                    ->schema([
                        Forms\Components\Textarea::make('flow_data')
                            ->label('flow_data')
                            ->required()
                            ->rows(18)
                            ->columnSpanFull()
                            ->helperText('O conteúdo é guardado como JSON; use formato pretty ao colar para facilitar revisão.')
                            ->formatStateUsing(function ($state): string {
                                if ($state === null || $state === '') {
                                    return '{}';
                                }
                                if (is_array($state)) {
                                    return json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '{}';
                                }

                                return is_string($state) ? $state : '{}';
                            })
                            ->dehydrateStateUsing(function ($state): array {
                                $raw = trim((string) $state);
                                if ($raw === '') {
                                    return [];
                                }
                                $decoded = json_decode($raw, true);

                                return is_array($decoded) ? $decoded : [];
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->description(fn (ChatbotFlowTemplate $record): ?string => $record->description
                        ? Str::limit(strip_tags($record->description), 90)
                        : null)
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->wrap(),

                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->hidden(),

                Tables\Columns\TextColumn::make('category')
                    ->label('Categoria')
                    ->badge()
                    ->color(fn (string $state): string => match (mb_strtolower($state)) {
                        'atendimento', 'suporte' => 'info',
                        'vendas', 'comercial' => 'success',
                        'pós-venda', 'pos-venda', 'feedback' => 'warning',
                        'general', 'geral' => 'gray',
                        default => 'primary',
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('flow_nodes_count')
                    ->label('Nós')
                    ->alignCenter()
                    ->tooltip('Número de nós no grafo Drawflow (aprox.)')
                    ->getStateUsing(fn (ChatbotFlowTemplate $record): int => static::countFlowNodes($record->flow_data)),

                Tables\Columns\TextColumn::make('company.name')
                    ->label('Empresa')
                    ->placeholder('Plataforma (global)')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_public')
                    ->label('Público')
                    ->boolean()
                    ->trueIcon('heroicon-o-globe-alt')
                    ->falseIcon('heroicon-o-lock-closed')
                    ->trueColor('success')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado')
                    ->since()
                    ->sortable()
                    ->tooltip(fn (ChatbotFlowTemplate $record): ?string => $record->updated_at?->format('d/m/Y H:i'))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Categoria')
                    ->options(fn (): array => ChatbotFlowTemplate::query()
                        ->whereNotNull('category')
                        ->orderBy('category')
                        ->distinct()
                        ->pluck('category')
                        ->mapWithKeys(fn (string $c): array => [$c => $c])
                        ->all()),
                Tables\Filters\TernaryFilter::make('is_public')
                    ->label('Visibilidade')
                    ->placeholder('Todos')
                    ->trueLabel('Só públicos')
                    ->falseLabel('Só internos'),
                Tables\Filters\SelectFilter::make('company_id')
                    ->label('Empresa')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Todas'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Editar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Nenhum modelo de fluxo')
            ->emptyStateDescription('Crie um modelo manualmente ou use «Gerar templates padrão» para popular a biblioteca com exemplos (saudação, vendas, NPS).')
            ->emptyStateIcon('heroicon-o-squares-2x2');
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
            'index' => Pages\ListChatbotFlowTemplates::route('/'),
            'create' => Pages\CreateChatbotFlowTemplate::route('/create'),
            'edit' => Pages\EditChatbotFlowTemplate::route('/{record}/edit'),
        ];
    }
}
