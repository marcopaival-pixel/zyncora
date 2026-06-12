<?php

namespace App\Filament\Resources\ChatbotFlowResource\Pages;

use App\Filament\Resources\ChatbotFlowResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListChatbotFlows extends ListRecords
{
    protected static string $resource = ChatbotFlowResource::class;

    protected ?string $heading = 'Fluxos por palavra-chave';

    protected ?string $subheading = 'Defina gatilhos, respostas e prioridade. Fluxos sem gatilho podem servir de fallback.';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back_to_premium')
                ->label('Recursos Avançados')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(\App\Filament\Pages\PremiumArea::getUrl()),
            Actions\CreateAction::make(),
        ];
    }
}
