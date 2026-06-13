<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChannelResource\Pages;
use App\Models\Channel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ChannelResource extends Resource
{
    protected static ?string $model = Channel::class;

    protected static ?string $navigationIcon = 'heroicon-o-signal';

    protected static ?string $navigationGroup = 'Integrações';

    protected static ?string $modelLabel = 'Canal';

    protected static ?string $pluralModelLabel = 'Canais';

    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->canManageIntegrations() ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->canManageIntegrations() ?? false;
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Empresa e tipo de canal')
                    ->description('Cada canal representa uma origem de conversas (WhatsApp, site, API, etc.).')
                    ->icon('heroicon-o-signal')
                    ->schema([
                        Forms\Components\Select::make('company_id')
                            ->relationship('company', 'name')
                            ->label('Empresa')
                            ->required()
                            ->visible(fn () => auth()->user()?->isPlatformAdmin() ?? false)
                            ->searchable()
                            ->preload()
                            ->live(),
                        Forms\Components\ToggleButtons::make('type')
                            ->label('Tipo')
                            ->options([
                                'whatsapp' => 'WhatsApp',
                                'site' => 'Site (widget)',
                                'internal' => 'Interno',
                                'api' => 'API / parceiro',
                            ])
                            ->icons([
                                'whatsapp' => 'heroicon-o-chat-bubble-left-right',
                                'site' => 'heroicon-o-globe-alt',
                                'internal' => 'heroicon-o-cpu-chip',
                                'api' => 'heroicon-o-code-bracket',
                            ])
                            ->inline()
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Identificação técnica')
                    ->description('Referência no sistema externo e credenciais de API, quando aplicável.')
                    ->icon('heroicon-o-key')
                    ->schema([
                        Forms\Components\TextInput::make('external_ref')
                            ->label('Referência externa')
                            ->placeholder('ID ou código no provedor')
                            ->maxLength(191),
                        Forms\Components\Textarea::make('token_api')
                            ->label('Token ou segredo')
                            ->rows(3)
                            ->placeholder('Bearer, API key ou segredo partilhado')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Estado')
                    ->icon('heroicon-o-check-circle')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Estado do canal')
                            ->options([
                                'connected' => 'Conectado',
                                'failed' => 'Falha',
                                'disconnected' => 'Desconectado',
                            ])
                            ->default('connected')
                            ->required()
                            ->native(false),
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
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->searchable(),

                Tables\Columns\TextColumn::make('external_ref')
                    ->label('Ref. externa')
                    ->placeholder('—')
                    ->copyable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'connected' => 'success',
                        'failed' => 'danger',
                        'disconnected' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'connected' => 'Conectado',
                        'failed' => 'Falha',
                        'disconnected' => 'Desconectado',
                        default => 'Desconhecido',
                    }),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado')
                    ->since()
                    ->sortable()
                    ->tooltip(fn (Channel $record): ?string => $record->updated_at?->format('d/m/Y H:i')),
            ])
            ->defaultSort('updated_at', 'desc')
            ->searchPlaceholder('Pesquisar empresa, tipo ou referência…')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'connected' => 'Conectado',
                        'failed' => 'Falha',
                        'disconnected' => 'Desconectado',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Nenhum canal')
            ->emptyStateDescription('Registe canais (site, WhatsApp, etc.) para associar conversas e chatbots.')
            ->emptyStateIcon('heroicon-o-signal');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChannels::route('/'),
            'create' => Pages\CreateChannel::route('/create'),
            'edit' => Pages\EditChannel::route('/{record}/edit'),
        ];
    }
}
