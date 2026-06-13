<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class WelcomeAgentWidget extends Widget
{
    protected static ?string $pollingInterval = null;

    protected static string $view = 'filament.widgets.welcome-agent-widget';

    protected int | string | array $columnSpan = 'full';
    
    protected static bool $isLazy = true;

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
        $company = Auth::user()?->company;
        if (!$company) return 0;
        return \Illuminate\Support\Facades\Cache::remember("company_{$company->id}_knowledge_count", now()->addMinutes(30), fn () => $company->knowledgeBases()->count());
    }

    public function getFlowsCount(): int
    {
        $company = Auth::user()?->company;
        if (!$company) return 0;
        return \Illuminate\Support\Facades\Cache::remember("company_{$company->id}_flows_count", now()->addMinutes(30), fn () => $company->chatbotFlows()->count());
    }
}

