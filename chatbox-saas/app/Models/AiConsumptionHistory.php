<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;

class AiConsumptionHistory extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'period_start',
        'period_end',
        'conversations_contracted',
        'conversations_used',
        'credits_purchased',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'conversations_contracted' => 'integer',
            'conversations_used' => 'integer',
            'credits_purchased' => 'integer',
        ];
    }
}
