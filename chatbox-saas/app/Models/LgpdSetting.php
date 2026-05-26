<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LgpdSetting extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'privacy_policy',
        'consent_term',
        'retention_days',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'retention_days' => 'integer',
    ];
}
