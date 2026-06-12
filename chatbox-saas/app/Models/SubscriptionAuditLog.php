<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'action',
        'old_status',
        'new_status',
        'trial_start_at',
        'trial_end_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'trial_start_at' => 'datetime',
            'trial_end_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
