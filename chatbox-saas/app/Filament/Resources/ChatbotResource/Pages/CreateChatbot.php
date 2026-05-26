<?php

namespace App\Filament\Resources\ChatbotResource\Pages;

use App\Filament\Resources\ChatbotResource;
use Filament\Resources\Pages\CreateRecord;

class CreateChatbot extends CreateRecord
{
    protected static string $resource = ChatbotResource::class;

    protected static ?string $title = 'Novo chatbot';

    protected ?string $subheading = 'Configure nome, mensagem inicial, horário e canal; opcionalmente ative IA e instruções na secção colapsável.';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        if ($user && ! $user->isPlatformAdmin()) {
            $data['company_id'] = $user->company_id;
        }

        return $data;
    }
}
