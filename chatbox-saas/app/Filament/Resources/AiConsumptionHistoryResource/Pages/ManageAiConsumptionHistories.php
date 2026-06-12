<?php

namespace App\Filament\Resources\AiConsumptionHistoryResource\Pages;

use App\Filament\Resources\AiConsumptionHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageAiConsumptionHistories extends ManageRecords
{
    protected static string $resource = AiConsumptionHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
