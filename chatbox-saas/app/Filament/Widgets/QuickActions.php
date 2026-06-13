<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\AtendimentoProfissional;
use App\Filament\Pages\Billing;
use App\Filament\Pages\CRMBoard;
use App\Filament\Pages\Reports;
use App\Filament\Resources\ChatbotResource;
use App\Filament\Resources\CompanyIntegrationResource;
use App\Filament\Resources\ContactResource;
use App\Filament\Resources\ConversationResource;
use App\Filament\Resources\DealResource;
use App\Filament\Resources\UserResource;
use App\Filament\SuperAdmin\Resources\CompanyResource;
use App\Filament\Widgets\Concerns\RequiresCompanyOrPlatformAdmin;
use Filament\Widgets\Widget;

class QuickActions extends Widget
{
    protected static ?string $pollingInterval = null;

    use RequiresCompanyOrPlatformAdmin;

    protected static string $view = 'filament.widgets.quick-actions';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<int, array{label: string, description: string, url: string, icon: string}>
     */
    public function getActions(): array
    {
        $user = auth()->user();

        // 1) Operação (fila e tempo real)
        $actions = [
            [
                'label' => 'Atendimento ao vivo',
                'description' => 'Responder conversas em tempo real',
                'url' => AtendimentoProfissional::getUrl(),
                'icon' => 'heroicon-o-chat-bubble-left-right',
            ],
            [
                'label' => 'Fila de conversas',
                'description' => 'Lista, prioridade e estado dos tickets',
                'url' => ConversationResource::getUrl('index'),
                'icon' => 'heroicon-o-queue-list',
            ],
        ];

        if ($user && Billing::canAccess()) {
            $actions[] = [
                'label' => 'Assinatura e planos',
                'description' => 'Plano, limites e renovação',
                'url' => Billing::getUrl(),
                'icon' => 'heroicon-o-credit-card',
            ];
        }

        // 2) CRM
        array_push(
            $actions,
            [
                'label' => 'Pipeline visual',
                'description' => 'Quadro Kanban por etapa do funil',
                'url' => CRMBoard::getUrl(),
                'icon' => 'heroicon-o-view-columns',
            ],
            [
                'label' => 'Novo negócio',
                'description' => 'Registar oportunidade no funil',
                'url' => DealResource::getUrl('create'),
                'icon' => 'heroicon-o-currency-dollar',
            ],
            [
                'label' => 'Contatos CRM',
                'description' => 'Base de clientes e etiquetas',
                'url' => ContactResource::getUrl('index'),
                'icon' => 'heroicon-o-user-group',
            ],
        );

        // 3) Dados e automação
        array_push(
            $actions,
            [
                'label' => 'Relatórios',
                'description' => 'Indicadores e exportação CSV',
                'url' => Reports::getUrl(),
                'icon' => 'heroicon-o-chart-bar',
            ],
            [
                'label' => 'Robôs / Chatbots',
                'description' => 'Configurar e testar assistentes IA',
                'url' => ChatbotResource::getUrl('index'),
                'icon' => 'heroicon-o-sparkles',
            ],
            [
                'label' => 'Integrações',
                'description' => 'Canais, tokens e webhooks',
                'url' => CompanyIntegrationResource::getUrl('index'),
                'icon' => 'heroicon-o-puzzle-piece',
            ],
        );

        if ($user?->isPlatformAdmin()) {
            $actions[] = [
                'label' => 'Organizações',
                'description' => 'Contas e limites por empresa',
                'url' => CompanyResource::getUrl('index'),
                'icon' => 'heroicon-o-building-office',
            ];
            $actions[] = [
                'label' => 'Utilizadores',
                'description' => 'Contas e permissões globais',
                'url' => UserResource::getUrl('index'),
                'icon' => 'heroicon-o-users',
            ];
        }

        return $actions;
    }
}
