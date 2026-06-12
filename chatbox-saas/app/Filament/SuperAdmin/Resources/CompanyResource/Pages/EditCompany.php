<?php

namespace App\Filament\SuperAdmin\Resources\CompanyResource\Pages;

use App\Filament\SuperAdmin\Resources\CompanyResource;
use Filament\Actions;
use App\Filament\Resources\Pages\BaseEditRecord;

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
