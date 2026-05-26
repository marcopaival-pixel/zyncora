<?php

namespace App\Filament\Resources\ChatbotResource\Pages;

use App\Filament\Resources\ChatbotResource;
use Filament\Actions;
use App\Filament\Resources\Pages\BaseEditRecord;

class EditChatbot extends BaseEditRecord
{
    protected static string $resource = ChatbotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('builder')
                ->label('Abrir Construtor de Fluxo')
                ->icon('heroicon-o-rectangle-group')
                ->color('warning')
                ->url(fn (): string => static::getResource()::getUrl('builder', ['record' => $this->getRecord()])),
            Actions\DeleteAction::make(),
        ];
    }
}
