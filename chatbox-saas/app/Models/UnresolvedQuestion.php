<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;

class UnresolvedQuestion extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'question',
        'frequency',
        'status',
        'suggested_draft',
    ];
}
