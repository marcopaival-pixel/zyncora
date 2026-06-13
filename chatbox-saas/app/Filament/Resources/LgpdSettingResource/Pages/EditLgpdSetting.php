<?php

namespace App\Filament\Resources\LgpdSettingResource\Pages;

use App\Filament\Resources\LgpdSettingResource;
use App\Filament\Resources\Pages\BaseEditRecord;
use Filament\Actions;

class EditLgpdSetting extends BaseEditRecord
{
    protected static string $resource = LgpdSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
