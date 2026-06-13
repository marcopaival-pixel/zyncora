<?php

namespace App\Filament\Pages;

use App\Models\Deal;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class CRMBoard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-view-columns';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?string $title = 'Pipeline Visual';

    protected static ?string $navigationLabel = 'Pipeline Visual';

    protected static ?int $navigationSort = 0;

    protected static string $view = 'filament.pages.crm-board';

    protected ?string $subheading = 'Arraste os cartões entre etapas para atualizar o funil. Valores em reais (R$), indicativos.';

    public static function canAccess(): bool
    {
        return auth()->user()?->canChat() ?? false;
    }

    public ?int $selectedPipelineId = null;

    public function mount(): void
    {
        $this->selectedPipelineId = Pipeline::query()->orderBy('name')->value('id');
    }

    public function getPipelinesProperty(): Collection
    {
        return Pipeline::query()->orderBy('name')->get();
    }

    public function getTotalPipelineValueProperty(): string
    {
        if (! $this->selectedPipelineId) {
            return '0,00';
        }

        $total = Pipeline::query()->find($this->selectedPipelineId)?->deals()->sum('value') ?? 0;

        return number_format((float) $total, 2, ',', '.');
    }

    public function getStagesProperty(): Collection
    {
        if (! $this->selectedPipelineId) {
            return collect();
        }

        return PipelineStage::query()
            ->where('pipeline_id', $this->selectedPipelineId)
            ->with(['deals' => function ($q): void {
                $user = auth()->user();
                if ($user && ! $user->isPlatformAdmin()) {
                    $q->where('company_id', $user->company_id);
                }
                $q->orderBy('updated_at', 'desc');
            }])
            ->orderBy('sort_order')
            ->get();
    }

    public function updateDealStage(int|string $dealId, int|string $newStageId): void
    {
        $user = auth()->user();
        $deal = Deal::query()->find($dealId);

        if (! $deal) {
            Notification::make()
                ->title('Negócio não encontrado')
                ->danger()
                ->send();

            return;
        }

        if ($user && ! $user->isPlatformAdmin() && (int) $deal->company_id !== (int) $user->company_id) {
            Notification::make()
                ->title('Operação não autorizada')
                ->body('Este negócio não pertence à sua empresa.')
                ->danger()
                ->send();

            return;
        }

        $stage = PipelineStage::query()->find($newStageId);

        if (! $stage || (int) $stage->pipeline_id !== (int) $this->selectedPipelineId) {
            Notification::make()
                ->title('Etapa inválida')
                ->body('Escolha uma coluna do funil atual.')
                ->danger()
                ->send();

            return;
        }

        $deal->update(['pipeline_stage_id' => (int) $newStageId]);

        Notification::make()
            ->title('Negócio atualizado')
            ->body('A etapa foi guardada.')
            ->success()
            ->send();
    }
}
