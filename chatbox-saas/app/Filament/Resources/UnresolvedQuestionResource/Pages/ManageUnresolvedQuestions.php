<?php

namespace App\Filament\Resources\UnresolvedQuestionResource\Pages;

use App\Filament\Resources\UnresolvedQuestionResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageUnresolvedQuestions extends ManageRecords
{
    protected static string $resource = UnresolvedQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
