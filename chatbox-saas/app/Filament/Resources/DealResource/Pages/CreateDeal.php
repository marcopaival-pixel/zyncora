<?php

namespace App\Filament\Resources\DealResource\Pages;

use App\Filament\Resources\DealResource;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use Filament\Resources\Pages\CreateRecord;

class CreateDeal extends CreateRecord
{
    protected static string $resource = DealResource::class;

    protected static ?string $title = 'Novo negócio';

    protected ?string $subheading = 'Registre uma oportunidade: defina o que está em jogo, associe um contato e posicione no funil. O valor em R$ é indicativo para priorização.';

    protected static bool $canCreateAnother = true;

    protected function afterFill(): void
    {
        $state = $this->form->getState();

        if (filled($state['pipeline_id'] ?? null) && filled($state['pipeline_stage_id'] ?? null)) {
            return;
        }

        $pipelineId = request()->query('pipeline');
        if ($pipelineId !== null && $pipelineId !== '') {
            $pipelineId = is_numeric($pipelineId) ? (int) $pipelineId : null;
            if ($pipelineId && ! Pipeline::query()->whereKey($pipelineId)->exists()) {
                $pipelineId = null;
            }
        }

        $pipelineId ??= Pipeline::query()->orderBy('name')->value('id');

        if (! $pipelineId) {
            return;
        }

        $stageId = request()->query('stage');
        if ($stageId !== null && $stageId !== '') {
            $stageId = is_numeric($stageId) ? (int) $stageId : null;
            if (
                $stageId
                && PipelineStage::query()
                    ->where('pipeline_id', $pipelineId)
                    ->whereKey($stageId)
                    ->doesntExist()
            ) {
                $stageId = null;
            }
        }

        $stageId ??= PipelineStage::query()
            ->where('pipeline_id', $pipelineId)
            ->orderBy('sort_order')
            ->value('id');

        $this->form->fill(array_merge($state, [
            'pipeline_id' => $pipelineId,
            'pipeline_stage_id' => $stageId,
            'value' => $state['value'] ?? 0,
        ]));
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Negócio criado';
    }
}
