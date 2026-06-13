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
    public function handle(RagScraperService $scraperService, \App\Services\AiService $aiService): void
    {
        if ($this->knowledgeBase->source_type !== 'url' || empty($this->knowledgeBase->source_path)) {
            return;
        }

        $url = $this->knowledgeBase->source_path;
        Log::info("ScrapeUrlForKnowledgeBase: Iniciando extração da URL: {$url}");

        $textContent = $scraperService->scrapeUrl($url);

        if ($textContent) {
            $embedding = $aiService->generateEmbeddings($textContent);

            $this->knowledgeBase->update([
                'content' => $textContent,
                'embedding' => $embedding,
            ]);
            Log::info("ScrapeUrlForKnowledgeBase: Extração concluída. Snippet ID {$this->knowledgeBase->id} atualizado com " . ($embedding ? 'Embedding gerado' : 'Sem embedding') . ".");

            // Extrair FAQ após o conteúdo estar pronto
            \App\Jobs\ExtractFaqFromKnowledgeBaseJob::dispatch($this->knowledgeBase);
        } else {
            Log::warning("ScrapeUrlForKnowledgeBase: Nenhum conteúdo extraído da URL: {$url}");
        }
    }
}
