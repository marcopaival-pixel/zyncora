<?php

namespace App\Filament\Resources\ContactResource\Pages;

use App\Filament\Resources\ContactResource;
use Filament\Actions;
use App\Filament\Resources\Pages\BaseEditRecord;

class EditContact extends BaseEditRecord
{
    protected static string $resource = ContactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
