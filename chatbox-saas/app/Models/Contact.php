<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'name',
        'phone',
        'email',
        'avatar_url',
        'custom_fields',
    ];

    protected $casts = [
        'custom_fields' => 'array',
        'phone' => 'encrypted',
        'email' => 'encrypted',
    ];

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }
}
