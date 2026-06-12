<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SectorResource\Pages;
use App\Models\Sector;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class SectorResource extends Resource
{
    protected static ?string $model = Sector::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    protected static ?string $navigationGroup = 'Configurações & Auditoria';

    protected static ?string $modelLabel = 'Setor';

    protected static ?string $pluralModelLabel = 'Setores';

    protected static ?int $navigationSort = 3;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasPermission('view_filas') ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission('view_filas') ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('conversations');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Definição do setor')
                    ->description('Utilizado para organizar filas e encaminhamento (cor visível nos painéis de atendimento).')
                    ->icon('heroicon-o-squares-plus')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome do setor')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ex.: Comercial, Suporte, Financeiro'),
                        Forms\Components\ColorPicker::make('color')
                            ->label('Cor de destaque')
                            ->default('#8b5cf6')
                            ->helperText('Ajuda a identificar o setor em listas e cartões.'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Ativo')
                            ->inline(false)
                            ->default(true)
                            ->helperText('Setores inativos não devem receber novas atribuições.'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Setor')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function (Sector $record): HtmlString {
                        $color = e($record->color ?: '#94a3b8');
                        $name = e($record->name);

                        return new HtmlString(
                            '<span class="inline-flex items-center gap-2">'
                            .'<span class="inline-block h-2.5 w-2.5 shrink-0 rounded-full ring-1 ring-gray-900/10 dark:ring-white/10" style="background:'.$color.'"></span>'
                            .'<span class="font-medium text-gray-900 dark:text-gray-100">'.$name.'</span>'
                            .'</span>'
                        );
                    })
                    ->html(),
                Tables\Columns\TextColumn::make('conversations_count')
                    ->label('Conversas')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->tooltip('Número de conversas associadas a este setor.'),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Ativo')
                    ->alignCenter(),
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
            ->searchPlaceholder('Pesquisar setor…')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->placeholder('Todos')
                    ->trueLabel('Só ativos')
                    ->falseLabel('Só inativos'),
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
            ->emptyStateHeading('Nenhum setor criado')
            ->emptyStateDescription('Crie setores para distribuir conversas por equipa ou área (Comercial, Suporte, etc.).')
            ->emptyStateIcon('heroicon-o-rectangle-group');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSectors::route('/'),
        ];
    }
}

