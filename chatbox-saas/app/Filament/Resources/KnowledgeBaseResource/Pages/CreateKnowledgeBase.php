<?php

namespace App\Filament\Resources\KnowledgeBaseResource\Pages;

use App\Filament\Resources\KnowledgeBaseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateKnowledgeBase extends CreateRecord
{
    protected static string $resource = KnowledgeBaseResource::class;

    protected static ?string $title = 'Novo snippet';

    protected ?string $subheading = 'Título claro e texto estruturado; opcionalmente indique origem (URL/ficheiro). Apenas ativos alimentam a IA.';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        if ($user && ! $user->isPlatformAdmin()) {
            $data['company_id'] = $user->company_id;
        }

        if (($data['source_type'] ?? '') === 'url' && empty($data['content'])) {
            $data['content'] = 'Aguardando sincronização... (O conteúdo será atualizado em breve)';
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->record->source_type === 'url') {
            \App\Jobs\ScrapeUrlForKnowledgeBase::dispatch($this->record);
        }
    }
}
