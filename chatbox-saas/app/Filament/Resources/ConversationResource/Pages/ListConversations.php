<?php

namespace App\Filament\Resources\ConversationResource\Pages;

use App\Filament\Resources\ConversationResource;
use App\Services\AgentDistributionService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListConversations extends ListRecords
{
    protected static string $resource = ConversationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('inbox')
                ->label('Abrir Inbox Omnichannel')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('primary')
                ->url(fn (): string => ConversationResource::getUrl('inbox')),

            Action::make('status_online')
                ->label('Ficar Online')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->action(function () {
                    auth()->user()->update(['presence_status' => 'online']);
                    Notification::make()->title('Você está online')->success()->send();
                    // Trigger distribution immediately
                    app(AgentDistributionService::class)->distribute(auth()->user()->company_id);
                })
                ->visible(fn () => auth()->user()->presence_status !== 'online'),

            Action::make('status_busy')
                ->label('Ficar Ocupado')
                ->color('warning')
                ->icon('heroicon-o-minus-circle')
                ->action(function () {
                    auth()->user()->update(['presence_status' => 'busy']);
                    Notification::make()->title('Você está ocupado')->warning()->send();
                })
                ->visible(fn () => auth()->user()->presence_status !== 'busy'),

            Action::make('status_offline')
                ->label('Ficar Offline')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->action(function () {
                    auth()->user()->update(['presence_status' => 'offline']);
                    Notification::make()->title('Você está offline')->danger()->send();
                })
                ->visible(fn () => auth()->user()->presence_status !== 'offline'),
        ];
    }
}
