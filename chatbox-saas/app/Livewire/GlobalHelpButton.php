<?php

namespace App\Livewire;

use App\Filament\Actions\HelpAction;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Component;

class GlobalHelpButton extends Component implements HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;

    public function helpAction(): HelpAction
    {
        return HelpAction::make('help')
            ->label('Ajuda Rápida')
            ->icon('heroicon-o-lifebuoy')
            ->color('primary')
            ->button()
            ->size('lg')
            ->module('Geral'); // Aqui poderíamos tentar pegar o módulo pela URL se fosse necessário
    }

    public function render()
    {
        return <<<'HTML'
            <div style="position: fixed; bottom: 2rem; right: 2rem; z-index: 9999;">
                {{ $this->helpAction }}
                <x-filament-actions::modals />
            </div>
        HTML;
    }
}
