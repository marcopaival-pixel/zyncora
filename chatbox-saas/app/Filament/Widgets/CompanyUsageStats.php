<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\RequiresCompanyOrPlatformAdmin;
use App\Models\Company;
use App\Models\Message;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CompanyUsageStats extends BaseWidget
{
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

        // Admins see a global summary, Clients see their own usage
        if ($user->isPlatformAdmin()) {
            return [
                Stat::make('Total de Empresas', Company::count())
                    ->description('Organizações ativas na plataforma')
                    ->descriptionIcon('heroicon-m-building-office')
                    ->color('primary'),
                Stat::make('Conversas (Hoje)', Message::whereDate('created_at', now())->count())
                    ->description('Volume total de mensagens')
                    ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                    ->color('success'),
                Stat::make('Planos Enterprise', Company::where('plan', 'enterprise')->count())
                    ->description('Clientes de alto nível')
                    ->descriptionIcon('heroicon-m-star'),
            ];
        }

        $company = $user->company;

        if (! $company) {
            return [];
        }

        $membersCount = $company->users()->count();
        $attendantsCount = $company->users()->where('role', \App\Models\User::ROLE_AGENT)->where('status', 'active')->count();
        $channelsCount = $company->channels()->count();
        $chatbotsCount = $company->chatbots()->count();

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

            Stat::make('Chatbots', "{$chatbotsCount} / {$company->max_chatbots}")
                ->description('Robôs configurados')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color($chatbotsCount >= $company->max_chatbots ? 'danger' : 'info'),
        ];
    }
}
