<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;

class WelcomeHero extends Widget
{
    protected static ?string $pollingInterval = null;

    protected static string $view = 'filament.widgets.welcome-hero';

    protected static bool $isLazy = true;

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public function getUserName(): string
    {
        return auth()->user()?->name ?? 'Utilizador';
    }

    public function getGreeting(): string
    {
        $hour = date('H');
        if ($hour < 12) {
            return 'Bom dia';
        }
        if ($hour < 18) {
            return 'Boa tarde';
        }

        return 'Boa noite';
    }

    /**
     * @return array{
     *   context: 'tenant'|'platform'|'orphan',
     *   plan: string,
     *   max_users: int,
     *   users_count: int,
     *   max_chatbots: int,
     *   chatbots_count: int,
     *   days_left: int|null,
     * }
     */
    public function getCompanyData(): array
    {
        $user = auth()->user();

        if (! $user) {
            return $this->emptyTenantData('orphan');
        }

        if ($user->isPlatformAdmin() && ! $user->company_id) {
            return [
                'context' => 'platform',
                'plan' => 'PLATAFORMA',
                'max_users' => 0,
                'users_count' => 0,
                'max_chatbots' => 0,
                'chatbots_count' => 0,
                'days_left' => null,
            ];
        }

        if (! $user->isPlatformAdmin() && ! $user->company_id) {
            return $this->emptyTenantData('orphan');
        }

        $company = $user->company;

        if (! $company) {
            return $this->emptyTenantData('orphan');
        }

        $usersCount = Cache::remember("company_{$company->id}_users_count", now()->addMinutes(15), function () use ($company) {
            return $company->users()->count();
        });

        $chatbotsCount = Cache::remember("company_{$company->id}_chatbots_count", now()->addMinutes(15), function () use ($company) {
            return $company->chatbots()->count();
        });

        $daysLeft = $company->expires_at ? now()->diffInDays($company->expires_at, false) : 999;

        return [
            'context' => 'tenant',
            'plan' => strtoupper($company->plan ?? 'FREE'),
            'max_users' => $company->max_users ?? 0,
            'users_count' => $usersCount,
            'max_chatbots' => $company->max_chatbots ?? 0,
            'chatbots_count' => $chatbotsCount,
            'days_left' => (int) $daysLeft,
        ];
    }

    /**
     * @return array{
     *   context: 'orphan',
     *   plan: string,
     *   max_users: int,
     *   users_count: int,
     *   max_chatbots: int,
     *   chatbots_count: int,
     *   days_left: null,
     * }
     */
    private function emptyTenantData(string $context): array
    {
        return [
            'context' => $context,
            'plan' => '—',
            'max_users' => 0,
            'users_count' => 0,
            'max_chatbots' => 0,
            'chatbots_count' => 0,
            'days_left' => null,
        ];
    }
}
