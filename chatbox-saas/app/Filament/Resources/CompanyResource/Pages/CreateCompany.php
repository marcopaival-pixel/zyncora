<?php

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Resources\CompanyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCompany extends CreateRecord
{
    protected static string $resource = CompanyResource::class;

    protected static ?string $title = 'Nova organização';

    protected ?string $subheading = 'Crie a conta: identidade, plano, limites e mensagens de chat. O slug é usado em URLs e webhooks.';
}
