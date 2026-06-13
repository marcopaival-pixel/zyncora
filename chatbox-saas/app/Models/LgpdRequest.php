<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LgpdRequest extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = [
        'company_id',
        'customer_id',
        'type',
        'status',
        'request_details',
        'completed_at',
    ];

    protected $casts = [
        'request_details' => 'json',
        'completed_at' => 'datetime',
    ];
}
