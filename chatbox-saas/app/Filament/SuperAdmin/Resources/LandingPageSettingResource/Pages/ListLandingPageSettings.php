<?php

namespace App\Filament\SuperAdmin\Resources\LandingPageSettingResource\Pages;

use App\Filament\SuperAdmin\Resources\LandingPageSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLandingPageSettings extends ListRecords
{
    protected static string $resource = LandingPageSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
