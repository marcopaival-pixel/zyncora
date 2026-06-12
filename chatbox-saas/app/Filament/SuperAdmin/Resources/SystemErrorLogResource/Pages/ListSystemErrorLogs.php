<?php

namespace App\Filament\SuperAdmin\Resources\SystemErrorLogResource\Pages;

use App\Filament\SuperAdmin\Resources\SystemErrorLogResource;
use Filament\Resources\Pages\ListRecords;

class ListSystemErrorLogs extends ListRecords
{
    protected static string $resource = SystemErrorLogResource::class;

    protected ?string $heading = 'Logs de erros do sistema';

    protected ?string $subheading = 'Falhas HTTP e exceções capturadas para diagnóstico. Apenas administradores da plataforma.';

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
