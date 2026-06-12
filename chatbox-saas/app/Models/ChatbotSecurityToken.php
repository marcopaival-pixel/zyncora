<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotSecurityToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'chatbot_id',
        'public_token',
        'secret_key',
        'rotated_at',
    ];

    protected $hidden = [
        'secret_key',
    ];

    protected $casts = [
        'rotated_at' => 'datetime',
    ];

    public function chatbot(): BelongsTo
    {
        return $this->belongsTo(Chatbot::class);
    }
}
