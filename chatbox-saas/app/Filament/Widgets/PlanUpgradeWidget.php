<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class PlanUpgradeWidget extends Widget
{
    protected static string $view = 'filament.widgets.plan-upgrade-widget';

    protected int | string | array $columnSpan = 'full';

    public function redirectToStripe()
    {
        return redirect()->to('/billing');
    }
}
