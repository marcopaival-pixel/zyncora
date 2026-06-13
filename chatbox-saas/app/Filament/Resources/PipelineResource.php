<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PipelineResource\Pages;
use App\Models\Pipeline;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PipelineResource extends Resource
{
    protected static ?string $model = Pipeline::class;

    protected static ?string $navigationIcon = 'heroicon-o-funnel';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?string $modelLabel = 'Funil de vendas';

    protected static ?string $pluralModelLabel = 'Funis de vendas';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        if ($user && $user->isPlatformAdmin()) {
            return false;
        }

        return $user?->hasPermission('view_clientes') ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission('view_clientes') ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount(['stages', 'deals']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Configuração do funil')
                    ->description('Um nome claro ajuda a equipa a escolher o funil certo ao criar negócios.')
                    ->icon('heroicon-o-funnel')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome do funil')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ex.: Novos clientes B2B'),
                    ]),

                Forms\Components\Section::make('Etapas do funil')
                    ->description('Defina as fases do processo comercial. Arraste as linhas para reordenar.')
                    ->icon('heroicon-o-queue-list')
                    ->schema([
                        Forms\Components\Repeater::make('stages')
                            ->relationship('stages')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nome da etapa')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('sort_order')
                                    ->label('Ordem')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),
                            ])
                            ->orderColumn('sort_order')
                            ->reorderable()
                            ->columns(2)
                            ->addActionLabel('Adicionar etapa'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Funil')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('stages_count')
                    ->label('Etapas')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('deals_count')
                    ->label('Negócios')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->tooltip('Total de negócios em todas as etapas deste funil.'),
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
            ->defaultSort('name')
            ->searchPlaceholder('Pesquisar funil…')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Nenhum funil criado')
            ->emptyStateDescription('Crie um funil com etapas (ex.: Contacto → Proposta → Fechado) para usar o quadro Kanban e o quadro de negócios.')
            ->emptyStateIcon('heroicon-o-funnel');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPipelines::route('/'),
            'create' => Pages\CreatePipeline::route('/create'),
            'edit' => Pages\EditPipeline::route('/{record}/edit'),
        ];
    }
}
