<?php

namespace App\Filament\Resources\ChatbotResource\Pages;

use App\Filament\Actions\HelpAction;
use App\Filament\Resources\ChatbotResource;
use App\Filament\Resources\ChatbotResource\Widgets\ChatbotOverviewWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListChatbots extends ListRecords
{
    protected static string $resource = ChatbotResource::class;

    public function getTitle(): string
    {
        return 'Chatbots';
    }

    public function getSubheading(): ?string
    {
        return 'Um registo por assistente: mensagens iniciais, horário, canal e opcionalmente IA. Clique numa linha para editar; use os ícones para testar ou abrir o fluxo.';
    }

    protected function getHeaderActions(): array
    {
        return [
            HelpAction::make()->module('Chatbots'),
            Actions\CreateAction::make()
                ->label('Novo chatbot')
                ->icon('heroicon-o-plus-circle'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ChatbotOverviewWidget::class,
        ];
    }
}
