<?php

namespace App\Filament\Resources\ConversationResource\Pages;

use App\Filament\Resources\ConversationResource;
use Filament\Actions;
use App\Filament\Resources\Pages\BaseEditRecord;

class EditConversation extends BaseEditRecord
{
    protected static string $resource = ConversationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
