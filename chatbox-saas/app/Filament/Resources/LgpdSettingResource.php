<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LgpdSettingResource\Pages;
use App\Models\LgpdSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LgpdSettingResource extends Resource
{
    protected static ?string $model = LgpdSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Configurações & Auditoria';

    protected static ?string $navigationLabel = 'Configurações LGPD';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'Configuração LGPD';

    protected static ?string $pluralModelLabel = 'Configurações LGPD';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->canManageIntegrations() ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->canManageIntegrations() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Política e consentimento')
                    ->description('Textos legais apresentados aos clientes e estado LGPD.')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('LGPD Ativo')
                            ->default(true),
                        Forms\Components\RichEditor::make('privacy_policy')
                            ->label('Política de Privacidade')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('consent_term')
                            ->label('Termo de Consentimento (Curto)')
                            ->required()
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Retenção de dados')
                    ->description('Por quanto tempo os dados dos clientes são mantidos (0 = permanente conforme política interna).')
                    ->icon('heroicon-o-archive-box')
                    ->schema([
                        Forms\Components\TextInput::make('retention_days')
                            ->label('Dias de Retenção')
                            ->numeric()
                            ->default(0)
                            ->suffix('dias')
                            ->helperText('Use 0 para retenção permanente.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('retention_days')
                    ->label('Retenção')
                    ->formatStateUsing(fn ($state) => $state == 0 ? 'Permanente' : "$state dias"),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última atualização')
                    ->since()
                    ->sortable()
                    ->tooltip(fn (LgpdSetting $record): ?string => $record->updated_at?->format('d/m/Y H:i')),
            ])
            ->defaultSort('updated_at', 'desc')
            ->searchPlaceholder('Pesquisar…')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Política ativa')
                    ->placeholder('Todas')
                    ->trueLabel('Ativas')
                    ->falseLabel('Inativas'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                //
            ])
            ->emptyStateHeading('Sem configurações LGPD')
            ->emptyStateDescription('Defina retenção e políticas por empresa quando aplicável.')
            ->emptyStateIcon('heroicon-o-cog-6-tooth');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLgpdSettings::route('/'),
            'create' => Pages\CreateLgpdSetting::route('/create'),
            'edit' => Pages\EditLgpdSetting::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();
        if ($user && ! $user->isPlatformAdmin()) {
            $query->where('company_id', $user->company_id);
        }

        return $query;
    }
}

