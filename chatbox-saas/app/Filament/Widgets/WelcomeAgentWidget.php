<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class WelcomeAgentWidget extends Widget
{
    protected static ?string $pollingInterval = null;

    protected static string $view = 'filament.widgets.welcome-agent-widget';

    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = -1;

    public function getDaysRemaining(): int
    {
        $company = Auth::user()->company;
        if (!$company || !$company->expires_at) return 0;

        return max(0, (int) now()->diffInDays($company->expires_at, false));
    }

    public function getPrimaryAgent()
    {
        return Auth::user()?->company?->chatbots()->first();
    }

    public function getKnowledgeCount(): int
    {
        return Auth::user()?->company?->knowledgeBases()->count() ?? 0;
    }

    public function getFlowsCount(): int
    {
        return Auth::user()?->company?->chatbotFlows()->count() ?? 0;
    }
}

