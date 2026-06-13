<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\LandingPageAnalyticResource\Pages;
use App\Models\LandingPageAnalytic;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LandingPageAnalyticResource extends Resource
{
    protected static ?string $model = LandingPageAnalytic::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $modelLabel = 'Analytics da Landing Page';

    protected static ?string $pluralModelLabel = 'Analytics da Landing Page';

    protected static ?string $navigationGroup = 'Site Público';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('type')->required(),
                Forms\Components\TextInput::make('utm_source'),
                Forms\Components\TextInput::make('utm_medium'),
                Forms\Components\TextInput::make('utm_campaign'),
                Forms\Components\TextInput::make('referer'),
                Forms\Components\TextInput::make('ip'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')->label('Tipo')->sortable(),
                Tables\Columns\TextColumn::make('utm_source')->label('Origem (UTM)')->sortable(),
                Tables\Columns\TextColumn::make('utm_medium')->label('Meio')->sortable(),
                Tables\Columns\TextColumn::make('utm_campaign')->label('Campanha')->sortable(),
                Tables\Columns\TextColumn::make('browser')->label('Navegador'),
                Tables\Columns\TextColumn::make('created_at')->label('Data')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'page_view' => 'Page View',
                        'cta_click' => 'CTA Click',
                        'conversion' => 'Conversão (Lead)',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLandingPageAnalytics::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
