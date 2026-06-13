<?php

namespace App\Filament\Resources\ChatbotResource\Widgets;

use App\Models\Chatbot;
use App\Models\Conversation;
use App\Models\Lead;
use App\Services\PlanService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class ChatbotOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = auth()->user();
        if (! $user) {
            return [];
        }

        $companyId = $user->isPlatformAdmin() ? null : $user->company_id;

        // 1. Conversas Hoje
        $conversationsQuery = Conversation::query();
        if ($companyId) {
            $conversationsQuery->where('company_id', $companyId);
        }
        $conversationsToday = clone $conversationsQuery;
        $countToday = $conversationsToday->whereDate('created_at', Carbon::today())->count();
        $countYesterday = (clone $conversationsQuery)->whereDate('created_at', Carbon::yesterday())->count();
        $trendConversations = $countToday <=> $countYesterday;
        $trendIconConversations = $trendConversations > 0 ? 'heroicon-m-arrow-trending-up' : ($trendConversations < 0 ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-minus');
        $trendColorConversations = $trendConversations > 0 ? 'success' : ($trendConversations < 0 ? 'danger' : 'gray');

        // 2. Leads Capturados (Assumindo que os contatos criados via chatbot geram Leads)
        $leadsQuery = Lead::query();
        if ($companyId) {
            $leadsQuery->where('company_id', $companyId);
        }
        $leadsToday = clone $leadsQuery;
        $countLeadsToday = $leadsToday->whereDate('created_at', Carbon::today())->count();
        $countLeadsYesterday = (clone $leadsQuery)->whereDate('created_at', Carbon::yesterday())->count();
        $trendLeads = $countLeadsToday <=> $countLeadsYesterday;
        $trendIconLeads = $trendLeads > 0 ? 'heroicon-m-arrow-trending-up' : ($trendLeads < 0 ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-minus');
        $trendColorLeads = $trendLeads > 0 ? 'success' : ($trendLeads < 0 ? 'danger' : 'gray');

        // 3. Economia de Tempo (Ex: Cada conversa economiza em média 5 minutos de um atendente humano)
        $totalConversationsThisMonth = (clone $conversationsQuery)->whereMonth('created_at', Carbon::now()->month)->count();
        $minutesSaved = $totalConversationsThisMonth * 5;
        $hoursSaved = round($minutesSaved / 60, 1);

        // 4. Créditos de IA (se houver companhia)
        $company = $user->company;
        $aiCreditsStatus = 'N/A';
        $aiCreditsColor = 'gray';
        $aiCreditsIcon = 'heroicon-o-cpu-chip';
        $aiCreditsDescription = 'Recurso desativado';

        if ($company && app(PlanService::class)->hasFeature($company, 'ai_automation')) {
            $usage = $company->ai_credits_used;
            $balance = $company->ai_credits_balance;
            if ($balance > 0) {
                $percentage = ($usage / $balance) * 100;
                $aiCreditsStatus = number_format($percentage, 0).'% consumido';
                if ($percentage >= 90) {
                    $aiCreditsColor = 'danger';
                    $aiCreditsDescription = 'Créditos quase esgotados!';
                } elseif ($percentage >= 70) {
                    $aiCreditsColor = 'warning';
                    $aiCreditsDescription = 'Consumo elevado';
                } else {
                    $aiCreditsColor = 'success';
                    $aiCreditsDescription = 'Consumo saudável';
                }
            } else {
                $aiCreditsStatus = 'Esgotado';
                $aiCreditsColor = 'danger';
                $aiCreditsDescription = 'Por favor, recarregue.';
            }
        }

        return [
            Stat::make('Conversas (Hoje)', $countToday)
                ->description(($trendConversations > 0 ? '+' : '').($countToday - $countYesterday).' em relação a ontem')
                ->descriptionIcon($trendIconConversations)
                ->color($trendColorConversations)
                ->icon('heroicon-o-chat-bubble-left-right'),

            Stat::make('Leads Capturados (Hoje)', $countLeadsToday)
                ->description(($trendLeads > 0 ? '+' : '').($countLeadsToday - $countLeadsYesterday).' em relação a ontem')
                ->descriptionIcon($trendIconLeads)
                ->color($trendColorLeads)
                ->icon('heroicon-o-users'),

            Stat::make('Economia de Tempo (Mês)', $hoursSaved.' horas')
                ->description('Tempo poupado da sua equipe')
                ->descriptionIcon('heroicon-m-clock')
                ->color('primary')
                ->icon('heroicon-o-clock'),

            Stat::make('Uso de IA', $aiCreditsStatus)
                ->description($aiCreditsDescription)
                ->color($aiCreditsColor)
                ->icon($aiCreditsIcon),
        ];
    }
}
