<?php

namespace App\Filament\SuperAdmin\Resources\CompanyResource\Pages;

use App\Filament\Resources\Pages\BaseEditRecord;
use App\Filament\SuperAdmin\Resources\CompanyResource;
use Filament\Actions;

class EditCompany extends BaseEditRecord
{
    protected static string $resource = CompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
