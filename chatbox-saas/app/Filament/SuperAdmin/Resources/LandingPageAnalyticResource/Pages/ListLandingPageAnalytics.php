<?php

namespace App\Filament\SuperAdmin\Resources\LandingPageAnalyticResource\Pages;

use App\Filament\SuperAdmin\Resources\LandingPageAnalyticResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLandingPageAnalytics extends ListRecords
{
    protected static string $resource = LandingPageAnalyticResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
