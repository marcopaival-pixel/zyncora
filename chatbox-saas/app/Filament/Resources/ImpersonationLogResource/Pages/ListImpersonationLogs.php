<?php

namespace App\Filament\Resources\ImpersonationLogResource\Pages;

use App\Filament\Resources\ImpersonationLogResource;
use Filament\Resources\Pages\ListRecords;

class ListImpersonationLogs extends ListRecords
{
    protected static string $resource = ImpersonationLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
