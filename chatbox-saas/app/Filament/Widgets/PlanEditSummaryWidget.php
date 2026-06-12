<?php

namespace App\Filament\Widgets;

use App\Models\Plan;
use Filament\Widgets\Widget;

/**
 * Cartão de resumo na edição de planos (só usado em EditPlan; não listar no dashboard).
 */
class PlanEditSummaryWidget extends Widget
{
    protected static ?string $pollingInterval = null;

    protected static string $view = 'filament.widgets.plan-edit-summary';

    protected static bool $isDiscovered = false;

    protected int | string | array $columnSpan = 'full';

    public ?Plan $record = null;

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $plan = $this->record;

        return [
            'plan' => $plan,
            'priceFormatted' => $plan ? number_format((float) $plan->price, 2, ',', '.') : '—',
            'intervalLabel' => $plan?->interval === 'year' ? 'Anual' : 'Mensal',
            'featuresCount' => is_array($plan?->features) ? count($plan->features) : 0,
        ];
    }
}

