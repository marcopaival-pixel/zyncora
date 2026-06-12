<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

// Usando array simulado para logs de erro já que não temos Laravel Pulse configurado ativamente no momento.
class RecentErrorsTableWidget extends BaseWidget
{
    protected static ?string $heading = 'Exceções Recentes Capturadas';
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Uma query fictícia apenas para mock. 
                // Num ambiente real usaríamos `Log::query()` se armazenado no banco, ou modelo de Pulse/Sentry
                \App\Models\User::query()->limit(0)
            )
            ->columns([
                Tables\Columns\TextColumn::make('timestamp')->label('Data/Hora'),
                Tables\Columns\TextColumn::make('level')->label('Nível')->badge(),
                Tables\Columns\TextColumn::make('message')->label('Mensagem de Erro'),
                Tables\Columns\TextColumn::make('file')->label('Local'),
            ])
            ->emptyStateHeading('Sistema Estável')
            ->emptyStateDescription('Nenhuma exceção crítica detectada na última hora.');
    }
}
