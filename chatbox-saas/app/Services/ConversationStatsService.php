<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ConversationStatsService
{
    /**
     * Consulta base de conversas respeitando o contexto do utilizador (multi-empresa).
     */
    public function scopedConversationQuery(?User $user): Builder
    {
        $query = Conversation::query();

        if ($user && ! $user->isPlatformAdmin()) {
            $query->where('company_id', $user->company_id);
        }

        return $query;
    }

    /**
     * Contagens partilhadas entre dashboard e relatórios.
     *
     * @return array{open: int, waiting: int, closed_today: int, closed_this_month: int}
     */
    public function dashboardCounts(?User $user): array
    {
        $base = $this->scopedConversationQuery($user);
        $todayStart = today()->startOfDay();
        $todayEnd = today()->endOfDay();
        $monthStart = now()->startOfMonth();

        $row = (clone $base)
            ->toBase()
            ->selectRaw(
                'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as c_open,
                 SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as c_waiting,
                 SUM(CASE WHEN status = ? AND closed_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as c_closed_today,
                 SUM(CASE WHEN status = ? AND closed_at >= ? THEN 1 ELSE 0 END) as c_closed_month',
                ['open', 'waiting', 'closed', $todayStart, $todayEnd, 'closed', $monthStart]
            )
            ->first();

        return [
            'open' => (int) ($row->c_open ?? 0),
            'waiting' => (int) ($row->c_waiting ?? 0),
            'closed_today' => (int) ($row->c_closed_today ?? 0),
            'closed_this_month' => (int) ($row->c_closed_month ?? 0),
        ];
    }
}
