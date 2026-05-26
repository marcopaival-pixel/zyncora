<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Painel de controle';

    protected static ?string $navigationLabel = 'Painel de controle';

    protected ?string $subheading = null;

    public function mount(): void
    {
        $this->subheading = now()
            ->locale(config('app.locale', 'pt_BR'))
            ->translatedFormat('l, d \d\e F \d\e Y')
            .' · Visão geral da operação, métricas e atalhos.';
    }

    public function getColumns(): int | string | array
    {
        return 12;
    }
}
