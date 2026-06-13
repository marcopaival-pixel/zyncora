<?php

namespace App\Filament\SuperAdmin\Resources\LandingPageAnalyticResource\Pages;

use App\Filament\SuperAdmin\Resources\LandingPageAnalyticResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLandingPageAnalytic extends EditRecord
{
    protected static string $resource = LandingPageAnalyticResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
