<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'sender_type',
        'sender_id',
        'body',
        'message_type',
        'attachment_path',
        'meta',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'sent_at' => 'datetime',
            'body' => 'encrypted',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
}
