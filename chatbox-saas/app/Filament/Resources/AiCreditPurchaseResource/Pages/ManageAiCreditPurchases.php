<?php

namespace App\Filament\Resources\AiCreditPurchaseResource\Pages;

use App\Filament\Resources\AiCreditPurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageAiCreditPurchases extends ManageRecords
{
    protected static string $resource = AiCreditPurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
