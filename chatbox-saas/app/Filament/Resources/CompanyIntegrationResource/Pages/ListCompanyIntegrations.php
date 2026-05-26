<?php

namespace App\Filament\Resources\CompanyIntegrationResource\Pages;

use App\Filament\Resources\CompanyIntegrationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCompanyIntegrations extends ListRecords
{
    protected static string $resource = CompanyIntegrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
