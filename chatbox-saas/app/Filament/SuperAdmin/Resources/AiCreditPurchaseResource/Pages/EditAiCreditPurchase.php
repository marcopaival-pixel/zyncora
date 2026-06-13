<?php

namespace App\Filament\SuperAdmin\Resources\AiCreditPurchaseResource\Pages;

use App\Filament\SuperAdmin\Resources\AiCreditPurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAiCreditPurchase extends EditRecord
{
    protected static string $resource = AiCreditPurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
