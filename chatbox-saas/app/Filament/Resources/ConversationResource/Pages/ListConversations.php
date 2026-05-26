<?php

namespace App\Filament\Resources\ConversationResource\Pages;

use App\Filament\Resources\ConversationResource;
use Filament\Resources\Pages\ListRecords;

class ListConversations extends ListRecords
{
    protected static string $resource = ConversationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('status_online')
                ->label('Ficar Online')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->action(function () {
                    auth()->user()->update(['presence_status' => 'online']);
                    \Filament\Notifications\Notification::make()->title('Você está online')->success()->send();
                    // Trigger distribution immediately
                    app(\App\Services\AgentDistributionService::class)->distribute(auth()->user()->company_id);
                })
                ->visible(fn () => auth()->user()->presence_status !== 'online'),

            \Filament\Actions\Action::make('status_busy')
                ->label('Ficar Ocupado')
                ->color('warning')
                ->icon('heroicon-o-minus-circle')
                ->action(function () {
                    auth()->user()->update(['presence_status' => 'busy']);
                    \Filament\Notifications\Notification::make()->title('Você está ocupado')->warning()->send();
                })
                ->visible(fn () => auth()->user()->presence_status !== 'busy'),

            \Filament\Actions\Action::make('status_offline')
                ->label('Ficar Offline')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->action(function () {
                    auth()->user()->update(['presence_status' => 'offline']);
                    \Filament\Notifications\Notification::make()->title('Você está offline')->danger()->send();
                })
                ->visible(fn () => auth()->user()->presence_status !== 'offline'),
        ];
    }
}
