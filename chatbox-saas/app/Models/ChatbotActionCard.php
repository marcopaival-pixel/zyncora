<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotActionCard extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'chatbot_id',
        'title',
        'icon',
        'action_type',
        'action_payload',
        'order_column',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order_column' => 'integer',
    ];

    public function chatbot(): BelongsTo
    {
        return $this->belongsTo(Chatbot::class);
    }
}
