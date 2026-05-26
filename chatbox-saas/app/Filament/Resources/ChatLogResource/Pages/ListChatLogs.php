<?php

namespace App\Filament\Resources\ChatLogResource\Pages;

use App\Filament\Resources\ChatLogResource;
use Filament\Resources\Pages\ListRecords;

class ListChatLogs extends ListRecords
{
    protected static string $resource = ChatLogResource::class;

    protected ?string $heading = 'Logs de operação';

    public function getSubheading(): ?string
    {
        if (auth()->user()?->isPlatformAdmin()) {
            return 'Eventos de todas as empresas. Lista atualizada automaticamente a cada 30 s.';
        }

        return 'Eventos da sua organização. Lista atualizada automaticamente a cada 30 s.';
    }
}
