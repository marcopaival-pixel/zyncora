<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

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
