<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiAnswerAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'user_message',
        'source_used',
        'tokens_saved_estimated',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
