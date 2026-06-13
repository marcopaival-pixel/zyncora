<?php

namespace App\Filament\SuperAdmin\Resources\AiCreditPurchaseResource\Pages;

use App\Filament\SuperAdmin\Resources\AiCreditPurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAiCreditPurchases extends ListRecords
{
    protected static string $resource = AiCreditPurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
