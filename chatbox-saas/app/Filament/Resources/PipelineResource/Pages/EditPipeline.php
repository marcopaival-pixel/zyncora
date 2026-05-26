<?php

namespace App\Filament\Resources\PipelineResource\Pages;

use App\Filament\Resources\PipelineResource;
use Filament\Actions;
use App\Filament\Resources\Pages\BaseEditRecord;

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
