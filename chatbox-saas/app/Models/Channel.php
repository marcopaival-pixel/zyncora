<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Traits\BelongsToCompany;

class Channel extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'type',
        'external_ref',
        'token_api',
        'status',
    ];

    // company() relation is provided by BelongsToCompany trait

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }
}
