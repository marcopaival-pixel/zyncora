<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotFlowExecution extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'conversation_id',
        'current_node_id',
        'variables',
        'execution_log',
        'is_completed',
    ];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'execution_log' => 'array',
            'is_completed' => 'boolean',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
}
