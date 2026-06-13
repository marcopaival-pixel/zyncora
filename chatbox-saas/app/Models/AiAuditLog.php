<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class AiAuditLog extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'chatbot_id',
        'conversation_id',
        'user_message',
        'ai_response',
        'tokens_used',
        'status',
    ];

    public function chatbot()
    {
        return $this->belongsTo(Chatbot::class);
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
