<?php

namespace App\Filament\Resources\SectorResource\Pages;

use App\Filament\Resources\SectorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSectors extends ListRecords
{
    protected static string $resource = SectorResource::class;

    protected ?string $heading = 'Setores';

    protected ?string $subheading = 'Organize o atendimento por área (comercial, suporte, etc.) e veja quantas conversas cada setor acumula.';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Novo setor')
                ->modalHeading('Criar setor')
                ->modalDescription('O nome e a cor ajudam a identificar o setor em filas e encaminhamentos.')
                ->icon('heroicon-o-plus'),
        ];
    }
}
