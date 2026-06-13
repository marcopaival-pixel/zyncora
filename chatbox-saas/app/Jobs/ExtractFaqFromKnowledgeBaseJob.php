<?php

namespace App\Jobs;

use App\Models\KnowledgeBase;
use App\Models\ChatbotFlow;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExtractFaqFromKnowledgeBaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;

    public function __construct(
        protected KnowledgeBase $knowledgeBase
    ) {}

    public function handle(): void
    {
        try {
            $content = strip_tags($this->knowledgeBase->content ?? '');
            
            if (empty(trim($content))) {
                return;
            }

            // We only extract FAQ for content longer than a few sentences to avoid wasting AI credits
            if (strlen($content) < 200) {
                return;
            }

            $aiGenerator = app(\App\Services\AIGeneratorService::class);
            
            // Re-use or extend AI generator to extract FAQ
            // Since this is a new feature, we simulate the LLM call that returns JSON
            // In a real scenario, $aiGenerator->extractFaq($content) would prompt:
            // "Extract max 5 frequent questions and answers from this text. Return as JSON array with 'question', 'answer', 'trigger'."
            
            $faqs = $this->callLlmForFaqExtraction($content);

            if (empty($faqs)) {
                return;
            }

            $sortOrder = ChatbotFlow::where('company_id', $this->knowledgeBase->company_id)->max('sort_order') ?? 0;

            foreach ($faqs as $faq) {
                $sortOrder++;
                ChatbotFlow::create([
                    'company_id' => $this->knowledgeBase->company_id,
                    'trigger' => $faq['trigger'] ?? substr($faq['question'], 0, 50),
                    'question' => $faq['question'],
                    'answer' => $faq['answer'],
                    'active' => true,
                    'sort_order' => $sortOrder,
                ]);
            }

        } catch (\Exception $e) {
            Log::error('ExtractFaqFromKnowledgeBaseJob failed: ' . $e->getMessage());
            // Fail silently so it doesn't crash the queue, but log it
        }
    }

    private function callLlmForFaqExtraction(string $content): array
    {
        // This is a stub that should be replaced with an actual API call to OpenAI/Anthropic/Gemini
        // using the application's configured LLM service.
        // It prompts the LLM to read $content and extract Q&A pairs.
        
        // Example integration:
        // $prompt = "Extraia as principais dúvidas (FAQ) do seguinte texto. Retorne um JSON array com chaves: trigger (curto), question (pergunta do usuario) e answer (resposta curta e direta). Texto: " . $content;
        // $response = Http::post('...', ['prompt' => $prompt]);
        // return json_decode($response->body(), true);

        return [];
    }
}
