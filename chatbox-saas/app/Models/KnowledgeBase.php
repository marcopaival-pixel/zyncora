<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;

class KnowledgeBase extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'title',
        'content',
        'source_type',
        'source_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
