<?php

namespace App\Filament\Resources\PipelineResource\Pages;

use App\Filament\Resources\PipelineResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePipeline extends CreateRecord
{
    protected static string $resource = PipelineResource::class;

    protected static ?string $title = 'Novo funil';

    protected ?string $subheading = 'Nomeie o funil e adicione etapas (ex.: Proposta, Negociação, Fechado). Pode reordenar antes de guardar.';
}
