<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotFlowSuggestion extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'chatbot_id',
        'suggested_intent',
        'message_count',
        'examples',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'examples' => 'array',
        ];
    }

    public function chatbot(): BelongsTo
    {
        return $this->belongsTo(Chatbot::class);
    }
}
