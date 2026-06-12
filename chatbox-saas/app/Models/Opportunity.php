<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Opportunity extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'conversation_id',
        'lead_name',
        'status',
        'value',
        'ai_score',
        'summary',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
}
