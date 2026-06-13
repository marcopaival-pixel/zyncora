<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotFlowNodeStat extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'chatbot_id',
        'node_id',
        'date',
        'views',
        'transfers',
        'dropoffs',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function chatbot(): BelongsTo
    {
        return $this->belongsTo(Chatbot::class);
    }
}
