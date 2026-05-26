<?php

namespace App\Filament\Resources\LgpdSettingResource\Pages;

use App\Filament\Resources\LgpdSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLgpdSettings extends ListRecords
{
    protected static string $resource = LgpdSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
