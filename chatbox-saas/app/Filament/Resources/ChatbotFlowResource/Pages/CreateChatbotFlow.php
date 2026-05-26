<?php

namespace App\Filament\Resources\ChatbotFlowResource\Pages;

use App\Filament\Resources\ChatbotFlowResource;
use Filament\Resources\Pages\CreateRecord;

class CreateChatbotFlow extends CreateRecord
{
    protected static string $resource = ChatbotFlowResource::class;

    protected static ?string $title = 'Novo fluxo';

    protected ?string $subheading = 'Defina o gatilho (palavra-chave), a resposta do bot e ações opcionais; ajuste prioridade na secção avançada.';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        if ($user && ! $user->isPlatformAdmin()) {
            $data['company_id'] = $user->company_id;
        }

        return $data;
    }
}
