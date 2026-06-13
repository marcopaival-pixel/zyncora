<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LgpdConsentResource\Pages;
use App\Models\LgpdConsent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LgpdConsentResource extends Resource
{
    protected static ?string $model = LgpdConsent::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $navigationGroup = 'Configurações & Auditoria';

    protected static ?string $modelLabel = 'Consentimento';

    protected static ?string $pluralModelLabel = 'Consentimentos';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->canViewLogs() ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->canViewLogs() ?? false;
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
                Forms\Components\TextInput::make('name')
                    ->label('Nome'),
                Forms\Components\TextInput::make('email')
                    ->label('E-mail')
                    ->email(),
                Forms\Components\TextInput::make('ip_address')
                    ->label('Endereço IP'),
                Forms\Components\DateTimePicker::make('consent_at')
                    ->label('Data/Hora Consentimento'),
                Forms\Components\Textarea::make('user_agent')
                    ->label('User Agent')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->placeholder('Anônimo'),
                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP'),
                Tables\Columns\IconColumn::make('consent_given')
                    ->label('Consentiu')
                    ->boolean(),
                Tables\Columns\TextColumn::make('consent_at')
                    ->label('Data/Hora')
                    ->since()
                    ->sortable()
                    ->tooltip(fn (LgpdConsent $record): ?string => $record->consent_at?->format('d/m/Y H:i')),
            ])
            ->defaultSort('consent_at', 'desc')
            ->searchPlaceholder('Pesquisar nome, e-mail ou IP…')
            ->filters([
                Tables\Filters\TernaryFilter::make('consent_given')
                    ->label('Consentimento')
                    ->placeholder('Todos')
                    ->trueLabel('Com consentimento')
                    ->falseLabel('Sem consentimento'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                //
            ])
            ->emptyStateHeading('Sem consentimentos')
            ->emptyStateDescription('Registos de aceitação de política e cookies aparecem aqui.')
            ->emptyStateIcon('heroicon-o-document-check');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLgpdConsents::route('/'),
        ];
    }
}
