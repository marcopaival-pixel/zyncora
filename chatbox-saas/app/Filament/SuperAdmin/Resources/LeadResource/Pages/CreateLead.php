<?php

namespace App\Filament\SuperAdmin\Resources\LeadResource\Pages;

use App\Filament\SuperAdmin\Resources\LeadResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateLead extends CreateRecord
{
    protected static string $resource = LeadResource::class;
}
