<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WidgetFraudAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'chatbot_id',
        'company_id',
        'risk_level',
        'trigger_reason',
        'fingerprint_data',
        'resolved_at',
    ];

    protected $casts = [
        'fingerprint_data' => 'array',
        'resolved_at' => 'datetime',
    ];

    public function chatbot(): BelongsTo
    {
        return $this->belongsTo(Chatbot::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
