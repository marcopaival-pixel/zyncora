<?php

namespace App\Filament\Resources\CompanyIntegrationResource\Pages;

use App\Filament\Resources\CompanyIntegrationResource;
use Filament\Actions;
use App\Filament\Resources\Pages\BaseEditRecord;

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
