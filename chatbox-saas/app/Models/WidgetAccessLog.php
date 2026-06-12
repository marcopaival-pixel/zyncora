<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WidgetAccessLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'chatbot_id',
        'domain',
        'ip_address',
        'user_agent',
        'session_id',
        'fingerprint_hash',
        'status',
        'block_reason',
        'risk_score',
    ];

    public function chatbot(): BelongsTo
    {
        return $this->belongsTo(Chatbot::class);
    }
}
