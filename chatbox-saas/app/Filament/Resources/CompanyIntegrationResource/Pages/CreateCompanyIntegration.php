<?php

namespace App\Filament\Resources\CompanyIntegrationResource\Pages;

use App\Filament\Resources\CompanyIntegrationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCompanyIntegration extends CreateRecord
{
    protected static string $resource = CompanyIntegrationResource::class;

    protected static ?string $title = 'Nova integração';

    protected ?string $subheading = 'Escolha o canal, defina o token de verificação e as credenciais da Meta. O URL do webhook é pré-visualizado quando a empresa estiver definida.';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        if ($user && ! $user->isPlatformAdmin()) {
            $data['company_id'] = $user->company_id;
        }

        $data['credentials'] = array_filter($data['credentials'] ?? []);

        return $data;
    }
}
