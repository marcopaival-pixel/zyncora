<?php

namespace App\Filament\Resources\ChannelResource\Pages;

use App\Filament\Resources\ChannelResource;
use Filament\Resources\Pages\CreateRecord;

class CreateChannel extends CreateRecord
{
    protected static string $resource = ChannelResource::class;

    protected static ?string $title = 'Novo canal';

    protected ?string $subheading = 'Defina a origem das conversas (WhatsApp, widget, API) e as credenciais necessárias para o sistema se autenticar.';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        if ($user && ! $user->isPlatformAdmin()) {
            $data['company_id'] = $user->company_id;
        }

        return $data;
    }
}
