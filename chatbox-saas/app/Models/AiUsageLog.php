<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class AiUsageLog extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'model_used',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'estimated_cost',
    ];

    protected function casts(): array
    {
        return [
            'prompt_tokens' => 'integer',
            'completion_tokens' => 'integer',
            'total_tokens' => 'integer',
            'estimated_cost' => 'decimal:6',
        ];
    }
}
