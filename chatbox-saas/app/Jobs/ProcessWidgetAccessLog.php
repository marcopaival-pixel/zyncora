<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\WidgetAccessLog;
use App\Models\Chatbot;
use App\Services\RiskScoringService;

class ProcessWidgetAccessLog implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $logData;

    /**
     * Create a new job instance.
     */
    public function __construct(array $logData)
    {
        $this->logData = $logData;
    }

    /**
     * Execute the job.
     */
    public function handle(RiskScoringService $riskScoringService, \App\Repositories\Contracts\WidgetAccessLogRepositoryInterface $logRepository): void
    {
        $log = $logRepository->store($this->logData);
        
        $chatbot = Chatbot::find($this->logData['chatbot_id']);
        // Se log falso (erro no clickhouse por ex), não roda a pontuação para não crashar a queue
        if ($chatbot && $log) {
            $riskScoringService->evaluateAccess($log, $chatbot);
        }
    }
}
