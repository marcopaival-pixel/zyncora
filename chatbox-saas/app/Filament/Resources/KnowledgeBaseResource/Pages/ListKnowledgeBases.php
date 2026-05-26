<?php

namespace App\Filament\Resources\KnowledgeBaseResource\Pages;

use App\Filament\Resources\KnowledgeBaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKnowledgeBases extends ListRecords
{
    protected static string $resource = KnowledgeBaseResource::class;

    protected ?string $heading = 'Base de conhecimento';

    protected ?string $subheading = 'Snippets de texto que a IA usa como contexto (FAQs, preços, políticas).';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back_to_premium')
                ->label('Área Premium')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(\App\Filament\Pages\PremiumArea::getUrl()),
            Actions\CreateAction::make(),
        ];
    }
}
