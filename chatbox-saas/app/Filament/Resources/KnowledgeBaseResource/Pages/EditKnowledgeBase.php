<?php

namespace App\Filament\Resources\KnowledgeBaseResource\Pages;

use App\Filament\Resources\KnowledgeBaseResource;
use Filament\Actions;
use App\Filament\Resources\Pages\BaseEditRecord;

class EditKnowledgeBase extends BaseEditRecord
{
    protected static string $resource = KnowledgeBaseResource::class;

    protected ?string $heading = 'Editar snippet';

    public function getSubheading(): ?string
    {
        return $this->getRecord()?->title;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('sync')
                ->label('Sincronizar URL')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->visible(fn (): bool => $this->getRecord()->source_type === 'url')
                ->action(function () {
                    \App\Jobs\ScrapeUrlForKnowledgeBase::dispatch($this->getRecord());
                    \Filament\Notifications\Notification::make()
                        ->title('Sincronização iniciada')
                        ->body('A URL está sendo raspada em background.')
                        ->success()
                        ->send();
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
