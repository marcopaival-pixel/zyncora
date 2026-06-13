<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LgpdConsent extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = [
        'company_id',
        'customer_id',
        'name',
        'email',
        'ip_address',
        'user_agent',
        'consent_given',
        'consent_at',
    ];

    protected $casts = [
        'consent_given' => 'boolean',
        'consent_at' => 'datetime',
    ];
}
