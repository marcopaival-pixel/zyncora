<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatLog extends Model
{
    protected $fillable = [
        'company_id',
        'log_type',
        'description',
        'context',
        'logged_at',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
            'logged_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
