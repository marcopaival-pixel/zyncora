<?php

namespace App\Filament\Resources\LgpdSettingResource\Pages;

use App\Filament\Resources\LgpdSettingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLgpdSetting extends CreateRecord
{
    protected static string $resource = LgpdSettingResource::class;

    protected static ?string $title = 'Nova configuração LGPD';

    protected ?string $subheading = 'Defina política de privacidade, termo curto de consentimento e prazo de retenção em dias.';
}
