<?php

namespace App\Jobs;

use App\Models\KnowledgeBase;
use App\Services\RagScraperService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ScrapeUrlForKnowledgeBase implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $knowledgeBase;

    /**
     * Create a new job instance.
     */
    public function __construct(KnowledgeBase $knowledgeBase)
    {
        $this->knowledgeBase = $knowledgeBase;
    }

    /**
     * Execute the job.
     */
    public function handle(RagScraperService $scraperService): void
    {
        if ($this->knowledgeBase->source_type !== 'url' || empty($this->knowledgeBase->source_path)) {
            return;
        }

        $url = $this->knowledgeBase->source_path;
        Log::info("ScrapeUrlForKnowledgeBase: Iniciando extração da URL: {$url}");

        $textContent = $scraperService->scrapeUrl($url);

        if ($textContent) {
            $this->knowledgeBase->update([
                'content' => $textContent,
                // Opcional: registrar a última data de sincronização se o model tiver o campo
            ]);
            Log::info("ScrapeUrlForKnowledgeBase: Extração concluída. Snippet ID {$this->knowledgeBase->id} atualizado.");
        } else {
            Log::warning("ScrapeUrlForKnowledgeBase: Nenhum conteúdo extraído da URL: {$url}");
        }
    }
}
