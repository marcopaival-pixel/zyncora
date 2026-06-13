<?php

namespace App\Repositories;

use App\Models\WidgetAccessLog;
use App\Repositories\Contracts\WidgetAccessLogRepositoryInterface;
use Illuminate\Support\Facades\Http;

class ClickHouseWidgetAccessLogRepository implements WidgetAccessLogRepositoryInterface
{
    protected string $endpoint;

    protected string $database;

    public function __construct()
    {
        $this->endpoint = config('database.connections.clickhouse.host', 'http://localhost:8123');
        $this->database = config('database.connections.clickhouse.database', 'default');
    }

    public function store(array $data): WidgetAccessLog|bool
    {
        // Aqui simula a inserção via HTTP para o ClickHouse.
        // Em um cenário real de alta volumetria, faríamos um Insert Async (Batching) no Go/Node ou via filas longas.

        try {
            $query = sprintf(
                'INSERT INTO %s.widget_access_logs FORMAT JSONEachRow',
                $this->database
            );

            // Para manter o contrato da aplicação simulamos o retorno do Model,
            // mas no mundo real ClickHouse retornaria true/false ou um objeto genérico.
            Http::post($this->endpoint.'/?query='.urlencode($query), [
                json_encode($data),
            ]);

            $log = new WidgetAccessLog($data);
            $log->id = rand(100000, 999999); // Mock do ID apenas para fluxo do código

            return $log;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getRecentDistinctIpsCount(string $sessionId, int $minutesAgo): int
    {
        try {
            // Consulta agregada de alta performance no banco colunar
            $query = sprintf(
                "SELECT count(DISTINCT ip_address) as total FROM %s.widget_access_logs WHERE session_id = '%s' AND created_at >= now() - INTERVAL %d MINUTE FORMAT JSON",
                $this->database,
                addslashes($sessionId),
                $minutesAgo
            );

            $response = Http::get($this->endpoint.'/?query='.urlencode($query));
            $result = $response->json();

            return $result['data'][0]['total'] ?? 0;
        } catch (\Exception $e) {
            return 0; // Fallback
        }
    }
}
