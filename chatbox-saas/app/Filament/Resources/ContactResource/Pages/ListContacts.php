<?php

namespace App\Filament\Resources\ContactResource\Pages;

use App\Filament\Resources\ContactResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\ContactResource\Widgets\ContactStatsOverview;

class ListContacts extends ListRecords
{
    protected static string $resource = ContactResource::class;

    protected ?string $heading = 'Contactos';

    protected ?string $subheading = 'Base de clientes e leads: ligue etiquetas, acompanhe conversas e negócios associados.';

    protected function getHeaderWidgets(): array
    {
        return [
            ContactStatsOverview::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Novo contacto')
                ->modalHeading('Registar contacto')
                ->modalDescription('O telefone deve ser único na empresa. Pode associar etiquetas para segmentar.')
                ->icon('heroicon-o-user-plus'),
        ];
    }
}
