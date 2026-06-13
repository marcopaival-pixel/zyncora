<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\LandingPageSettingResource\Pages;
use App\Models\LandingPageSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LandingPageSettingResource extends Resource
{
    protected static ?string $model = LandingPageSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $modelLabel = 'Configuração da Landing Page';

    protected static ?string $pluralModelLabel = 'Configurações da Landing Page';

    protected static ?string $navigationGroup = 'Site Público';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Hero Section')
                            ->schema([
                                Forms\Components\TextInput::make('hero_title')
                                    ->label('Título Principal')
                                    ->required(),
                                Forms\Components\Textarea::make('hero_subtitle')
                                    ->label('Subtítulo')
                                    ->required(),
                            ]),
                        Forms\Components\Tabs\Tab::make('Prova Social')
                            ->schema([
                                Forms\Components\Repeater::make('stats')
                                    ->label('Estatísticas')
                                    ->schema([
                                        Forms\Components\TextInput::make('label')->required()->label('Rótulo (ex: Clientes)'),
                                        Forms\Components\TextInput::make('value')->required()->label('Valor (ex: +10k)'),
                                    ])->columns(2),
                            ]),
                        Forms\Components\Tabs\Tab::make('Benefícios')
                            ->schema([
                                Forms\Components\Repeater::make('benefits')
                                    ->label('Diferenciais da Plataforma')
                                    ->schema([
                                        Forms\Components\TextInput::make('title')->required()->label('Benefício'),
                                    ]),
                            ]),
                        Forms\Components\Tabs\Tab::make('CTAs e Sucesso')
                            ->schema([
                                Forms\Components\TextInput::make('primary_cta_text')->label('Texto Botão Principal')->required(),
                                Forms\Components\TextInput::make('secondary_cta_text')->label('Texto Botão Secundário')->required(),
                                Forms\Components\TextInput::make('trial_days')->label('Dias de Trial')->numeric()->required(),
                                Forms\Components\TextInput::make('success_message_title')->label('Título Mensagem Sucesso')->required(),
                                Forms\Components\TextInput::make('success_message_subtitle')->label('Subtítulo Mensagem Sucesso')->required(),
                            ]),
                        Forms\Components\Tabs\Tab::make('Contatos e Redes Sociais')
                            ->schema([
                                Forms\Components\TextInput::make('contact_email')->label('E-mail Comercial')->email(),
                                Forms\Components\TextInput::make('contact_whatsapp')->label('WhatsApp Comercial'),
                                Forms\Components\TextInput::make('contact_phone')->label('Telefone Comercial'),
                                Forms\Components\TextInput::make('social_linkedin')->label('LinkedIn')->url(),
                                Forms\Components\TextInput::make('social_instagram')->label('Instagram')->url(),
                                Forms\Components\TextInput::make('social_facebook')->label('Facebook')->url(),
                                Forms\Components\TextInput::make('social_youtube')->label('YouTube')->url(),
                            ])->columns(2),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('hero_title')->label('Título Principal')->limit(50),
                Tables\Columns\TextColumn::make('updated_at')->label('Última Atualização')->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLandingPageSettings::route('/'),
            'create' => Pages\CreateLandingPageSetting::route('/create'),
            'edit' => Pages\EditLandingPageSetting::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return LandingPageSetting::count() === 0;
    }
}
