<?php

namespace App\Filament\Resources;

use App\Filament\Pages\CRMBoard;
use App\Filament\Resources\ContactResource;
use App\Filament\Resources\DealResource\Pages;
use App\Filament\Resources\PipelineResource;
use App\Models\Deal;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class DealResource extends Resource
{
    protected static ?string $model = Deal::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?string $modelLabel = 'Negócio';

    protected static ?string $pluralModelLabel = 'Quadro de Negócios';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        if ($user && $user->isPlatformAdmin()) return false;
        return $user?->canChat() ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->canChat() ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['contact', 'stage.pipeline']);
    }

    public static function form(Form $form): Form
    {
        $pipelineHint = new HtmlString(
            'O funil define as etapas do processo. '
            .'<a href="'.PipelineResource::getUrl('index').'" class="text-primary-600 underline decoration-primary-600/30 hover:text-primary-500 dark:text-primary-400">Gerenciar funis</a> '
            .'ou use o <a href="'.CRMBoard::getUrl().'" class="text-primary-600 underline decoration-primary-600/30 hover:text-primary-500 dark:text-primary-400">pipeline visual</a>.'
        );

        $contactHint = new HtmlString(
            'Só aparecem contatos da sua empresa. '
            .'<a href="'.ContactResource::getUrl('index').'" class="text-primary-600 underline decoration-primary-600/30 hover:text-primary-500 dark:text-primary-400">Cadastrar contatos</a>.'
        );

        return $form
            ->columns(1)
            ->schema([
                Forms\Components\Section::make('Oportunidade')
                    ->description('Nomeie o negócio e o valor estimado em reais (indicativo para relatórios e priorização).')
                    ->icon('heroicon-o-currency-dollar')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ex.: Plano Enterprise — ACME Ltda')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('value')
                            ->label('Valor estimado')
                            ->numeric()
                            ->prefix('R$')
                            ->default(0)
                            ->minValue(0)
                            ->step(0.01)
                            ->helperText('Use um valor aproximado; pode ajustar depois na edição.'),
                    ])->columns(2),

                Forms\Components\Section::make('Cliente')
                    ->description($contactHint)
                    ->icon('heroicon-o-user')
                    ->schema([
                        Forms\Components\Select::make('contact_id')
                            ->label('Contato no CRM')
                            ->relationship('contact', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->placeholder('Buscar por nome…'),
                    ]),

                Forms\Components\Section::make('Funil de vendas')
                    ->description($pipelineHint)
                    ->icon('heroicon-o-view-columns')
                    ->schema([
                        Forms\Components\Select::make('pipeline_id')
                            ->label('Funil')
                            ->options(fn () => Pipeline::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->live()
                            ->required()
                            ->dehydrated(false)
                            ->native(false)
                            ->afterStateUpdated(function (Set $set, $state): void {
                                if (! $state) {
                                    $set('pipeline_stage_id', null);

                                    return;
                                }
                                $first = PipelineStage::query()
                                    ->where('pipeline_id', $state)
                                    ->orderBy('sort_order')
                                    ->value('id');
                                $set('pipeline_stage_id', $first);
                            })
                            ->helperText('Ao mudar o funil, a etapa é ajustada para a primeira do funil (pode alterar).'),

                        Forms\Components\Select::make('pipeline_stage_id')
                            ->label('Etapa')
                            ->options(function (Forms\Get $get) {
                                $pipelineId = $get('pipeline_id');
                                if (! $pipelineId) {
                                    return [];
                                }

                                return PipelineStage::query()
                                    ->where('pipeline_id', $pipelineId)
                                    ->orderBy('sort_order')
                                    ->pluck('name', 'id');
                            })
                            ->required()
                            ->native(false)
                            ->helperText('Ordem das etapas é a definida no cadastro do funil.'),
                    ])->columns(2),

                Forms\Components\Section::make('Notas internas')
                    ->description('Visível para a equipe no painel; não é enviado ao cliente.')
                    ->icon('heroicon-o-document-text')
                    ->collapsed()
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Observações')
                            ->rows(5)
                            ->placeholder('Próximos passos, objeções, histórico da conversa…')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Negócio')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (Deal $record): ?string => $record->stage?->pipeline?->name),
                Tables\Columns\TextColumn::make('contact.name')
                    ->label('Contacto')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stage.name')
                    ->label('Etapa')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                Tables\Columns\TextColumn::make('value')
                    ->label('Valor')
                    ->money('BRL', 0, 'pt_BR')
                    ->sortable()
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última atividade')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->searchPlaceholder('Pesquisar negócio, contacto ou etapa…')
            ->filters([
                Tables\Filters\SelectFilter::make('pipeline')
                    ->label('Funil')
                    ->options(fn () => Pipeline::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;
                        if (! filled($value)) {
                            return $query;
                        }

                        return $query->whereHas('stage', fn (Builder $q) => $q->where('pipeline_id', $value));
                    }),
                Tables\Filters\SelectFilter::make('etapa')
                    ->label('Etapa')
                    ->options(function (): array {
                        $query = PipelineStage::query()->orderBy('sort_order');
                        $user = auth()->user();
                        if ($user && ! $user->isPlatformAdmin() && $user->company_id) {
                            $query->whereHas('pipeline', fn (Builder $p) => $p->where('company_id', $user->company_id));
                        }

                        return $query->pluck('name', 'id')->all();
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;
                        if (! filled($value)) {
                            return $query;
                        }

                        return $query->where('pipeline_stage_id', $value);
                    }),
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
            ->emptyStateHeading('Sem negócios registados')
            ->emptyStateDescription('Crie o primeiro negócio ou use o pipeline visual para arrastar oportunidades entre etapas.')
            ->emptyStateIcon('heroicon-o-currency-dollar');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeals::route('/'),
            'create' => Pages\CreateDeal::route('/create'),
            'edit' => Pages\EditDeal::route('/{record}/edit'),
        ];
    }
}

