<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KnowledgeSource extends Model
{
    use BelongsToCompany;
    use HasFactory;

    protected $fillable = [
        'company_id',
        'source_type',
        'is_active',
        'config',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'config' => 'array',
    ];
}
