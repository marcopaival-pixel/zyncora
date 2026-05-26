<?php

namespace App\Filament\Resources\DealResource\Pages;

use App\Filament\Resources\DealResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDeals extends ListRecords
{
    protected static string $resource = DealResource::class;

    protected ?string $heading = 'Quadro de negócios';

    protected ?string $subheading = 'Lista de oportunidades com funil, etapa e valor. Utilize filtros ou o pipeline visual para mover cartões.';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Novo negócio')
                ->icon('heroicon-o-plus'),
        ];
    }
}
