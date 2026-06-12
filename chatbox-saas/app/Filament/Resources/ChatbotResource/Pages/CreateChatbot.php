<?php

namespace App\Filament\Resources\ChatbotResource\Pages;

use App\Filament\Resources\ChatbotResource;
use Filament\Resources\Pages\CreateRecord;

class CreateChatbot extends CreateRecord
{
    protected static string $resource = ChatbotResource::class;

    protected static ?string $title = 'Novo chatbot';

    protected ?string $subheading = 'Configure nome, mensagem inicial, horário e canal; opcionalmente ative IA e instruções na secção colapsável.';

    protected bool $generateWithAi = false;
    protected string $chatbotObjective = 'suporte';
    protected array $chatbotChannels = ['site'];
    protected ?string $chatbotSegment = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        if ($user && ! $user->isPlatformAdmin()) {
            $data['company_id'] = $user->company_id;
        }

        $this->generateWithAi = $data['generate_with_ai'] ?? false;
        $this->chatbotObjective = $data['chatbot_objective'] ?? 'suporte';
        $this->chatbotChannels = $data['chatbot_channels'] ?? ['site'];
        $this->chatbotSegment = $data['chatbot_segment'] ?? null;

        unset($data['generate_with_ai'], $data['chatbot_objective'], $data['chatbot_channels'], $data['chatbot_segment']);

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->generateWithAi) {
            $chatbot = $this->record;
            $company = $chatbot->company;
            $segment = $this->chatbotSegment ?? $company->segment ?? 'Outro Segmento';
            $channels = $this->chatbotChannels;
            
            app(\App\Services\AgentPersonalizationService::class)->generateForSegment(
                $company, 
                $chatbot, 
                $segment, 
                $this->chatbotObjective, 
                $channels
            );

            \Filament\Notifications\Notification::make()
                ->title('IA configurou seu assistente com sucesso!')
                ->success()
                ->send();
        }
    }
}
