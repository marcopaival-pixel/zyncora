<?php

namespace App\Filament\Resources\ChatbotFlowTemplateResource\Pages;

use App\Filament\Resources\ChatbotFlowTemplateResource;
use Filament\Actions;
use App\Filament\Resources\Pages\BaseEditRecord;

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
