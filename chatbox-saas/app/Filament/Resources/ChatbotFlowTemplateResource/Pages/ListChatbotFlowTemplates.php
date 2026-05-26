<?php

namespace App\Filament\Resources\ChatbotFlowTemplateResource\Pages;

use App\Filament\Resources\ChatbotFlowTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListChatbotFlowTemplates extends ListRecords
{
    protected static string $resource = ChatbotFlowTemplateResource::class;

    public function getTitle(): string
    {
        return 'Modelos de fluxo (templates)';
    }

    public function getSubheading(): ?string
    {
        return 'Biblioteca global de fluxos Drawflow para referência e cópia nos chatbots. Modelos sem empresa são da plataforma.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generate_defaults')
                ->label('Gerar templates padrão')
                ->icon('heroicon-o-sparkles')
                ->color('warning')
                ->modalHeading('Popular biblioteca com exemplos')
                ->modalDescription('Executa o seeder de templates (saudação, vendas, NPS, etc.). Registos existentes com o mesmo nome são atualizados (updateOrCreate).')
                ->modalSubmitActionLabel('Gerar agora')
                ->requiresConfirmation()
                ->action(function (): void {
                    (new \Database\Seeders\ChatbotFlowTemplateSeeder())->run();
                    \Filament\Notifications\Notification::make()
                        ->title('Templates atualizados')
                        ->body('A biblioteca foi sincronizada com os modelos padrão.')
                        ->success()
                        ->send();
                }),
            Actions\CreateAction::make()
                ->label('Novo modelo'),
        ];
    }
}
