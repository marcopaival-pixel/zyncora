<?php

namespace App\Filament\Resources\ChatbotFlowResource\Pages;

use App\Filament\Resources\ChatbotFlowResource;
use App\Filament\Resources\Pages\BaseEditRecord;
use Filament\Actions;

class EditChatbotFlow extends BaseEditRecord
{
    protected static string $resource = ChatbotFlowResource::class;

    protected ?string $heading = 'Editar fluxo';

    public function getSubheading(): ?string
    {
        $t = $this->getRecord()?->trigger;

        return filled($t) ? 'Gatilho: '.$t : 'Fluxo fallback (sem palavra-chave)';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
