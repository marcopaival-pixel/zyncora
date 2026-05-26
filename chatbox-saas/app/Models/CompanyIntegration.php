<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyIntegration extends Model
{
    protected $fillable = [
        'company_id',
        'driver',
        'credentials',
        'webhook_verify_token',
        'status',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'credentials' => 'encrypted:array',
            'meta' => 'array',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
