<?php

namespace App\Filament\Resources\CompanyIntegrationResource\Pages;

use App\Filament\Resources\CompanyIntegrationResource;
use App\Filament\Resources\Pages\BaseEditRecord;
use Filament\Actions;

class EditCompanyIntegration extends BaseEditRecord
{
    protected static string $resource = CompanyIntegrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['credentials'] = array_filter($data['credentials'] ?? []);

        return $data;
    }
}
