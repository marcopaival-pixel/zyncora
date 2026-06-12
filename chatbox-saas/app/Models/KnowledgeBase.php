<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;

class KnowledgeBase extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'title',
        'content',
        'category',
        'embedding',
        'source_type',
        'source_path',
        'is_active',
        'tokens_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'tokens_count' => 'integer',
        'embedding' => 'array',
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            if ($model->isDirty('content') && !empty($model->content)) {
                // Estimativa de tokens
                $cleanText = strip_tags($model->content);
                $wordCount = str_word_count($cleanText);
                $model->tokens_count = (int) ceil($wordCount / 0.75);

                // Geração de Embedding se o conteúdo foi alterado manualmente e não via Job
                // O Job sobrescreve isso depois se for de URL, mas para texto puro funciona na hora.
                try {
                    $aiService = app(\App\Services\AiService::class);
                    $embedding = $aiService->generateEmbeddings($cleanText);
                    if ($embedding) {
                        $model->embedding = $embedding;
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Erro ao gerar embedding no model event: ' . $e->getMessage());
                }
            }
        });
    }
}
