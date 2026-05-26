<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\AtendimentoProfissional;
use App\Filament\Pages\CRMBoard;
use App\Filament\Resources\ChatbotResource;
use App\Filament\Resources\CompanyIntegrationResource;
use App\Filament\Widgets\Concerns\RequiresCompanyOrPlatformAdmin;
use App\Models\Chatbot;
use App\Models\CompanyIntegration;
use App\Models\Conversation;
use App\Models\Deal;
use App\Models\Message;
use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ExecutiveGrowthSnapshot extends Widget
{
    use RequiresCompanyOrPlatformAdmin;

    protected static string $view = 'filament.widgets.executive-growth-snapshot';

    protected static bool $isLazy = true;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array{
     *     title: string,
     *     subtitle: string,
     *     metrics: list<array{label: string, value: string, description: string, tone: string}>,
     *     actions: list<array{label: string, description: string, url: string, tone: string}>,
     * }
     */
    public function getSnapshot(): array
    {
        /** @var User|null $user */
        $user = Auth::user();
        $isPlatform = $user?->isPlatformAdmin() ?? false;
        $companyId = $isPlatform ? null : $user?->company_id;

        // Cache das métricas brutas por 10 minutos para escala SaaS
        $cacheKey = "metrics_snapshot_" . ($companyId ?? 'platform');
        $data = cache()->remember($cacheKey, now()->addMinutes(10), function () use ($companyId) {
            $conversations = $this->tenantConversationQuery($companyId);
            $messages = $this->tenantMessageQuery($companyId);
            $deals = $this->tenantDealQuery($companyId);

            $totalConv = (clone $conversations)->count();
            $closedConv = (clone $conversations)->where('status', 'closed')->count();
            $waitingConv = (clone $conversations)->where('status', 'waiting')->count();

            return [
                'today' => (clone $conversations)->whereDate('created_at', today())->count(),
                'weekly' => (clone $conversations)->where('created_at', '>=', now()->subDays(7))->count(),
                'waiting' => $waitingConv,
                'total' => $totalConv,
                'closed' => $closedConv,
                'res_rate' => $totalConv > 0 ? round(($closedConv / $totalConv) * 100) : 0,
                'pipeline' => (float) (clone $deals)->sum('value'),
                'deals_count' => (clone $deals)->count(),
                'msg_vol' => (clone $messages)->where('created_at', '>=', now()->subDays(7))->count(),
            ];
        });

        return [
            'title' => $isPlatform ? 'Crescimento da plataforma' : 'Sua operação em números',
            'subtitle' => $isPlatform
                ? 'Visão executiva agregada para acompanhar adoção, operação e oportunidades.'
                : 'Resumo comercial para demonstrar resultado e orientar as próximas ações.',
            'metrics' => [
                [
                    'label' => 'Conversas hoje',
                    'value' => (string) $data['today'],
                    'description' => "{$data['weekly']} nos últimos 7 dias",
                    'tone' => 'primary',
                ],
                [
                    'label' => 'Aguardando atendimento',
                    'value' => (string) $data['waiting'],
                    'description' => $data['waiting'] > 0 ? 'Priorize estes contatos agora' : 'Fila sob controle',
                    'tone' => $data['waiting'] > 0 ? 'warning' : 'success',
                ],
                [
                    'label' => 'Taxa de resolução',
                    'value' => "{$data['res_rate']}%",
                    'description' => "{$data['closed']} de {$data['total']} conversas encerradas",
                    'tone' => $data['res_rate'] >= 70 ? 'success' : 'info',
                ],
                [
                    'label' => 'Pipeline CRM',
                    'value' => 'R$ '.number_format($data['pipeline'], 2, ',', '.'),
                    'description' => "{$data['deals_count']} oportunidades abertas · {$data['msg_vol']} mensagens/7 dias",
                    'tone' => $data['pipeline'] > 0 ? 'success' : 'gray',
                ],
            ],
            'actions' => $this->recommendedActions($companyId, $isPlatform, $data['waiting'], $data['deals_count']),
        ];
    }

    private function tenantConversationQuery(?int $companyId): Builder
    {
        return Conversation::query()
            ->when($companyId !== null, fn (Builder $query) => $query->where('company_id', $companyId));
    }

    private function tenantMessageQuery(?int $companyId): Builder
    {
        return Message::query()
            ->when($companyId !== null, function (Builder $query) use ($companyId): void {
                $query->whereHas('conversation', fn (Builder $conversation) => $conversation->where('company_id', $companyId));
            });
    }

    private function tenantDealQuery(?int $companyId): Builder
    {
        return Deal::query()
            ->when($companyId !== null, fn (Builder $query) => $query->where('company_id', $companyId));
    }

    /**
     * @return list<array{label: string, description: string, url: string, tone: string}>
     */
    private function recommendedActions(?int $companyId, bool $isPlatform, int $waitingConversations, int $dealsCount): array
    {
        /** @var User|null $user */
        $user = Auth::user();
        $actions = [];

        if ($waitingConversations > 0 && ($user?->canChat() ?? false)) {
            $actions[] = [
                'label' => 'Responder fila agora',
                'description' => 'Há conversas esperando atendimento humano.',
                'url' => AtendimentoProfissional::getUrl(),
                'tone' => 'primary',
            ];
        }

        if (($user?->canManageIntegrations() ?? false) && $this->whatsappIntegrationsCount($companyId) === 0) {
            $actions[] = [
                'label' => 'Conectar WhatsApp',
                'description' => 'Mostre ao cliente o canal mais pedido no mercado.',
                'url' => CompanyIntegrationResource::getUrl('index'),
                'tone' => 'success',
            ];
        }

        if (($user?->canManageIntegrations() ?? false) && $this->chatbotsCount($companyId) === 0) {
            $actions[] = [
                'label' => 'Criar primeiro chatbot',
                'description' => 'Automatize perguntas frequentes e capture leads 24/7.',
                'url' => ChatbotResource::getUrl('index'),
                'tone' => 'info',
            ];
        }

        if (($user?->canChat() ?? false) && $dealsCount === 0) {
            $actions[] = [
                'label' => 'Abrir funil de vendas',
                'description' => 'Transforme conversas em oportunidades visíveis.',
                'url' => CRMBoard::getUrl(),
                'tone' => 'gray',
            ];
        }

        if ($actions === [] && ($user?->canChat() ?? false)) {
            $actions[] = [
                'label' => $isPlatform ? 'Ver relatórios comerciais' : 'Acompanhar pipeline',
                'description' => 'Continue acompanhando conversão e atendimento.',
                'url' => CRMBoard::getUrl(),
                'tone' => 'primary',
            ];
        }

        return array_slice($actions, 0, 3);
    }

    private function whatsappIntegrationsCount(?int $companyId): int
    {
        return CompanyIntegration::query()
            ->where('driver', 'whatsapp_cloud')
            ->when($companyId !== null, fn (Builder $query) => $query->where('company_id', $companyId))
            ->count();
    }

    private function chatbotsCount(?int $companyId): int
    {
        return Chatbot::query()
            ->when($companyId !== null, fn (Builder $query) => $query->where('company_id', $companyId))
            ->count();
    }
}
