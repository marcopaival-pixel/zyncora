<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string 
    {
        return 'Membros e Utilizadores';
    }

    public function getBreadcrumb(): string
    {
        return 'Lista de Membros';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Adicionar Novo Membro')
                ->icon('heroicon-m-plus'),
        ];
    }
}
