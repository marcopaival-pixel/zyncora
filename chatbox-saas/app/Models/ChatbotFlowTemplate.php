<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class ChatbotFlowTemplate extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'flow_data',
        'category',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'flow_data' => 'array',
            'is_public' => 'boolean',
        ];
    }
}
