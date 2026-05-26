<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotScriptStep extends Model
{
    protected $fillable = [
        'chatbot_id',
        'step_order',
        'prompt',
        'response',
        'response_type',
        'next_step_key',
    ];

    public function chatbot(): BelongsTo
    {
        return $this->belongsTo(Chatbot::class);
    }
}
