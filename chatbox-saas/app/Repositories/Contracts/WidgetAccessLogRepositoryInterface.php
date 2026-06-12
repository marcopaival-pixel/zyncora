<?php

namespace App\Repositories\Contracts;

use App\Models\WidgetAccessLog;

interface WidgetAccessLogRepositoryInterface
{
    /**
     * Armazena um log de acesso do widget.
     */
    public function store(array $data): WidgetAccessLog|bool;

    /**
     * Retorna a quantidade de IPs distintos recentes para uma sessão.
     */
    public function getRecentDistinctIpsCount(string $sessionId, int $minutesAgo): int;
}
