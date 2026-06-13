<?php

namespace App\Filament\Resources\PipelineResource\Pages;

use App\Filament\Resources\Pages\BaseEditRecord;
use App\Filament\Resources\PipelineResource;
use Filament\Actions;

class EditPipeline extends BaseEditRecord
{
    protected static string $resource = PipelineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
