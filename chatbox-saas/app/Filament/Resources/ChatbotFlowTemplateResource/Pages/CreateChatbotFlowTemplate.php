<?php

namespace App\Filament\Resources\ChatbotFlowTemplateResource\Pages;

use App\Filament\Resources\ChatbotFlowTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateChatbotFlowTemplate extends CreateRecord
{
    protected static string $resource = ChatbotFlowTemplateResource::class;

    protected static ?string $title = 'Novo modelo de fluxo';

    protected ?string $subheading = 'Nomeie o modelo, opcionalmente associe a uma empresa, e cole o JSON do fluxo exportado do construtor.';
}
