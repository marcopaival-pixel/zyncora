<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class ChatbotFlow extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'trigger',
        'question',
        'answer',
        'next_flow_key',
        'action',
        'active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    // company() relation is provided by BelongsToCompany trait
}
