<?php

namespace App\Filament\Resources\DealResource\Pages;

use App\Filament\Resources\DealResource;
use Filament\Actions;
use App\Filament\Resources\Pages\BaseEditRecord;

class EditDeal extends BaseEditRecord
{
    protected static string $resource = DealResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();
        if ($record?->stage) {
            $data['pipeline_id'] = $record->stage->pipeline_id;
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
