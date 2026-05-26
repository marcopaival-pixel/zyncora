<?php

namespace App\Filament\Resources\PipelineResource\Pages;

use App\Filament\Resources\PipelineResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPipelines extends ListRecords
{
    protected static string $resource = PipelineResource::class;

    protected ?string $heading = 'Funis de vendas';

    protected ?string $subheading = 'Defina etapas por funil. O total de negócios reflete todas as oportunidades ligadas a esse funil.';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Novo funil')
                ->icon('heroicon-o-plus'),
        ];
    }
}
