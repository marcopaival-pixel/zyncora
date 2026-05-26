<?php

namespace App\Filament\Resources\ChannelResource\Pages;

use App\Filament\Resources\ChannelResource;
use Filament\Actions;
use App\Filament\Resources\Pages\BaseEditRecord;

class EditChannel extends BaseEditRecord
{
    protected static string $resource = ChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
