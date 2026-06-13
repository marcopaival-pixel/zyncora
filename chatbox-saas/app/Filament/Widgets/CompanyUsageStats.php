<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\RequiresCompanyOrPlatformAdmin;
use App\Models\Company;
use App\Models\Message;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CompanyUsageStats extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    use RequiresCompanyOrPlatformAdmin;

    protected static bool $isLazy = true;

    protected static ?int $sort = 3;

    protected function getHeading(): ?string
    {
        return 'Capacidade e consumo';
    }

    protected function getDescription(): ?string
    {
        $user = auth()->user();
        if ($user?->isPlatformAdmin()) {
            return 'Indicadores agregados de todas as organizações na plataforma.';
        }

        return 'Comparação dos limites do plano com o uso atual da organização.';
    }

    protected function getStats(): array
    {
        $user = auth()->user();

        if ($user->isPlatformAdmin()) {
            $totalCompanies = \Illuminate\Support\Facades\Cache::remember('platform_total_companies', now()->addMinutes(15), fn () => Company::count());
            $totalMessagesToday = \Illuminate\Support\Facades\Cache::remember('platform_messages_today', now()->addMinutes(15), fn () => Message::whereDate('created_at', now())->count());
            $enterprisePlans = \Illuminate\Support\Facades\Cache::remember('platform_enterprise_plans', now()->addMinutes(15), fn () => Company::where('plan', 'enterprise')->count());

            return [
                Stat::make('Total de Empresas', $totalCompanies)
                    ->description('Organizações ativas na plataforma')
                    ->descriptionIcon('heroicon-m-building-office')
                    ->color('primary'),
                Stat::make('Conversas (Hoje)', $totalMessagesToday)
                    ->description('Volume total de mensagens')
                    ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                    ->color('success'),
                Stat::make('Planos Enterprise', $enterprisePlans)
                    ->description('Clientes de alto nível')
                    ->descriptionIcon('heroicon-m-star'),
            ];
        }

        $company = $user->company;

        if (! $company) {
            return [];
        }

        $membersCount = \Illuminate\Support\Facades\Cache::remember("company_{$company->id}_members_count", now()->addMinutes(15), fn () => $company->users()->count());
        $attendantsCount = \Illuminate\Support\Facades\Cache::remember("company_{$company->id}_attendants_count", now()->addMinutes(15), fn () => $company->users()->where('role', \App\Models\User::ROLE_AGENT)->where('status', 'active')->count());
        $channelsCount = \Illuminate\Support\Facades\Cache::remember("company_{$company->id}_channels_count", now()->addMinutes(15), fn () => $company->channels()->count());
        $chatbotsCount = \Illuminate\Support\Facades\Cache::remember("company_{$company->id}_chatbots_count", now()->addMinutes(15), fn () => $company->chatbots()->count());

        return [
            Stat::make('Equipa (Membros)', "{$membersCount} / {$company->max_users}")
                ->description($membersCount >= $company->max_users ? 'Limite total atingido' : 'Espaço para novos membros')
                ->descriptionIcon('heroicon-m-user-group')
                ->color($membersCount >= $company->max_users ? 'warning' : 'success'),

            Stat::make('Atendentes Ativos', "{$attendantsCount} / {$company->max_attendants}")
                ->description($attendantsCount >= $company->max_attendants ? 'Limite de atendentes atingido' : 'Licenças de atendimento disponíveis')
                ->descriptionIcon('heroicon-m-users')
                ->color($attendantsCount >= $company->max_attendants ? 'danger' : 'success'),

            Stat::make('Canais Ativos', "{$channelsCount} / {$company->max_channels}")
                ->description('WhatsApp, Site, etc.')
                ->descriptionIcon('heroicon-m-puzzle-piece')
                ->color($channelsCount >= $company->max_channels ? 'warning' : 'primary'),

            Stat::make('Créditos de IA', "{$company->ai_credits_used} / {$company->ai_credits_balance}")
                ->description($company->ai_credits_used >= $company->ai_credits_balance ? 'Sem créditos de IA' : 'Respostas com Inteligência Artificial')
                ->descriptionIcon('heroicon-m-cpu-chip')
                ->color($company->ai_credits_used >= $company->ai_credits_balance ? 'danger' : 'info'),
        ];
    }
}

