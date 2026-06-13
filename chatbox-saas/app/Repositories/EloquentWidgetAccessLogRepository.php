<?php

namespace App\Repositories;

use App\Models\WidgetAccessLog;
use App\Repositories\Contracts\WidgetAccessLogRepositoryInterface;

class EloquentWidgetAccessLogRepository implements WidgetAccessLogRepositoryInterface
{
    public function store(array $data): WidgetAccessLog|bool
    {
        return WidgetAccessLog::create($data);
    }

    public function getRecentDistinctIpsCount(string $sessionId, int $minutesAgo): int
    {
        return WidgetAccessLog::where('session_id', $sessionId)
            ->where('created_at', '>=', now()->subMinutes($minutesAgo))
            ->distinct('ip_address')
            ->count('ip_address');
    }
}
