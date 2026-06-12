<?php

namespace App\Filament\SuperAdmin\Resources\PlanResource\Pages;

use App\Filament\Resources\Pages\BaseEditRecord;
use App\Filament\SuperAdmin\Resources\PlanResource;
use App\Filament\Widgets\PlanEditSummaryWidget;
use Filament\Actions;
use Illuminate\Contracts\Support\Htmlable;

class EditPlan extends BaseEditRecord
{
    protected static string $resource = PlanResource::class;

    /**
     * @return array<class-string<\Filament\Widgets\Widget> | \Filament\Widgets\WidgetConfiguration>
     */
    protected function getHeaderWidgets(): array
    {
        return [
            PlanEditSummaryWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | string | array
    {
        return 1;
    }

    public function getTitle(): string | Htmlable
    {
        return 'Editar plano de assinatura';
    }

    public function getHeading(): string | Htmlable
    {
        return $this->getRecord()?->name ?? 'Plano';
    }

    public function getSubheading(): string | Htmlable | null
    {
        $record = $this->getRecord();
        if ($record === null) {
            return null;
        }

        $price = number_format((float) $record->price, 2, ',', '.');
        $interval = $record->interval === 'year' ? 'ano' : 'mês';
        $status = $record->is_active ? 'Ativo para novas vendas' : 'Inativo';

        return "Slug: {$record->slug} · R$ {$price} / {$interval} · {$status}";
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Eliminar plano'),
        ];
    }

    protected function getSaveFormAction(): \Filament\Actions\Action
    {
        return parent::getSaveFormAction()
            ->label('Guardar alterações');
    }

    protected function getCancelFormAction(): \Filament\Actions\Action
    {
        return parent::getCancelFormAction()
            ->label('Descartar');
    }
}
