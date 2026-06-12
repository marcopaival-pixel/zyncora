<?php

namespace App\Filament\Pages;

use App\Services\ConversationStatsService;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Reports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static string $view = 'filament.pages.reports';

    protected static ?string $navigationGroup = 'Plataforma';

    protected static ?string $navigationLabel = 'Relatórios Analíticos';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->canViewReports() ?? false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->canViewReports() ?? false;
    }

    public int $open = 0;

    public int $waiting = 0;

    public int $closedToday = 0;

    public int $closedMonth = 0;

    public string $periodLabel = '';

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('back_to_premium')
                ->label('Recursos Avançados')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(\App\Filament\Pages\PremiumArea::getUrl()),
        ];
    }

    public function mount(ConversationStatsService $conversationStats): void
    {
        $counts = $conversationStats->dashboardCounts(auth()->user());

        $this->open = $counts['open'];
        $this->waiting = $counts['waiting'];
        $this->closedToday = $counts['closed_today'];
        $this->closedMonth = $counts['closed_this_month'];

        $this->periodLabel = Carbon::now()
            ->locale(app()->getLocale())
            ->isoFormat('MMMM [de] YYYY');
    }

    /**
     * Partilha da fila ativa: conversas abertas vs. em espera (0–100).
     */
    public function getOpenShareOfPipelineProperty(): float
    {
        $total = $this->open + $this->waiting;

        return $total > 0 ? round(100 * $this->open / $total, 1) : 0.0;
    }

    public function exportCsv(ConversationStatsService $conversationStats): StreamedResponse
    {
        $user = auth()->user();
        abort_if($user === null, 403);

        $isPlatform = $user->isPlatformAdmin();
        $filename = 'relatorio-conversas-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($conversationStats, $user, $isPlatform): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            try {
                fwrite($handle, "\xEF\xBB\xBF");

                if ($isPlatform) {
                    fputcsv($handle, [
                        'id',
                        'empresa_id',
                        'empresa',
                        'canal',
                        'estado',
                        'cliente_nome',
                        'cliente_telefone',
                        'cliente_email',
                        'iniciada_em',
                        'encerrada_em',
                        'primeira_resposta_em',
                        'tempo_resposta_s',
                        'total_mensagens',
                    ], ';');
                } else {
                    fputcsv($handle, [
                        'id',
                        'canal',
                        'estado',
                        'cliente_nome',
                        'cliente_telefone',
                        'cliente_email',
                        'iniciada_em',
                        'encerrada_em',
                        'primeira_resposta_em',
                        'tempo_resposta_s',
                        'total_mensagens',
                    ], ';');
                }

                $query = $conversationStats->scopedConversationQuery($user)
                    ->with(['channel', 'company'])
                    ->orderByDesc('started_at');

                foreach ($query->lazy(500) as $c) {
                    if ($isPlatform) {
                        fputcsv($handle, [
                            $c->id,
                            $c->company_id,
                            $c->company?->name ?? '',
                            $c->channel?->type ?? '',
                            $c->status,
                            $c->client_name ?? '',
                            $c->client_phone ?? '',
                            $c->client_email ?? '',
                            $c->started_at?->format('Y-m-d H:i:s') ?? '',
                            $c->closed_at?->format('Y-m-d H:i:s') ?? '',
                            $c->first_response_at?->format('Y-m-d H:i:s') ?? '',
                            $c->response_time_seconds ?? '',
                            $c->total_messages ?? 0,
                        ], ';');
                    } else {
                        fputcsv($handle, [
                            $c->id,
                            $c->channel?->type ?? '',
                            $c->status,
                            $c->client_name ?? '',
                            $c->client_phone ?? '',
                            $c->client_email ?? '',
                            $c->started_at?->format('Y-m-d H:i:s') ?? '',
                            $c->closed_at?->format('Y-m-d H:i:s') ?? '',
                            $c->first_response_at?->format('Y-m-d H:i:s') ?? '',
                            $c->response_time_seconds ?? '',
                            $c->total_messages ?? 0,
                        ], ';');
                    }
                }
            } finally {
                if (is_resource($handle)) {
                    fclose($handle);
                }
            }
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
