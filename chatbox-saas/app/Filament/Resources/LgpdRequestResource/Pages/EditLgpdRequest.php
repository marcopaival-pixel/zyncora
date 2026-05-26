<?php

namespace App\Filament\Resources\LgpdRequestResource\Pages;

use App\Filament\Resources\LgpdRequestResource;
use Filament\Actions;
use App\Filament\Resources\Pages\BaseEditRecord;

class EditLgpdRequest extends BaseEditRecord
{
    protected static string $resource = LgpdRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
