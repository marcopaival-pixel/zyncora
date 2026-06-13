<?php

namespace App\Filament\Resources\ChatbotFlowTemplateResource\Pages;

use App\Filament\Resources\ChatbotFlowTemplateResource;
use App\Filament\Resources\Pages\BaseEditRecord;
use Filament\Actions;

class EditChatbotFlowTemplate extends BaseEditRecord
{
    protected static string $resource = ChatbotFlowTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
