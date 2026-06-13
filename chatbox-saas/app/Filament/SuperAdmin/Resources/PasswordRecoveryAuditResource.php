<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\PasswordRecoveryAuditResource\Pages;
use App\Models\PasswordRecoveryAudit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PasswordRecoveryAuditResource extends Resource
{
    protected static ?string $model = PasswordRecoveryAudit::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'Auditoria de Senhas';
    protected static ?string $modelLabel = 'Log de Recuperação de Senha';
    protected static ?string $pluralModelLabel = 'Logs de Recuperação de Senha';
    protected static ?string $navigationGroup = 'Segurança & Auditoria';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('email')
                    ->label('E-mail')
                    ->disabled(),
                Forms\Components\TextInput::make('ip_address')
                    ->label('IP')
                    ->disabled(),
                Forms\Components\TextInput::make('action')
                    ->label('Ação')
                    ->disabled(),
                Forms\Components\TextInput::make('status')
                    ->label('Status')
                    ->disabled(),
                Forms\Components\Textarea::make('user_agent')
                    ->label('Navegador/Dispositivo')
                    ->columnSpanFull()
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data/Hora')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('Endereço IP')
                    ->searchable(),
                Tables\Columns\TextColumn::make('action')
                    ->label('Ação')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'requested' => 'info',
                        'reset_success' => 'success',
                        'invalid_token' => 'danger',
                        'rate_limited' => 'warning',
                        'token_expired' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'requested' => 'Solicitado',
                        'reset_success' => 'Sucesso',
                        'invalid_token' => 'Token Inválido',
                        'rate_limited' => 'Rate Limit',
                        'token_expired' => 'Expirado',
                        default => $state,
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->label('Ação')
                    ->options([
                        'requested' => 'Solicitado',
                        'reset_success' => 'Sucesso',
                        'invalid_token' => 'Token Inválido',
                        'rate_limited' => 'Bloqueado por Rate Limit',
                        'token_expired' => 'Token Expirado',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // Read-only
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
            'index' => Pages\ListPasswordRecoveryAudits::route('/'),
        ];
    }
}
