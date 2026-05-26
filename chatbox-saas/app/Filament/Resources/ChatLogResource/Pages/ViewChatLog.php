<?php

namespace App\Filament\Resources\ChatLogResource\Pages;

use App\Filament\Resources\ChatLogResource;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Str;

class ViewChatLog extends ViewRecord
{
    protected static string $resource = ChatLogResource::class;

    protected ?string $heading = 'Detalhe do log';

    public function getSubheading(): ?string
    {
        $desc = $this->getRecord()?->description;

        return $desc ? (string) Str::of($desc)->limit(140) : null;
    }
}
