<?php

namespace App\Filament\SuperAdmin\Resources\PasswordRecoveryAuditResource\Pages;

use App\Filament\SuperAdmin\Resources\PasswordRecoveryAuditResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPasswordRecoveryAudits extends ListRecords
{
    protected static string $resource = PasswordRecoveryAuditResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
