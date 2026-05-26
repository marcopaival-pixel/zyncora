<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sector extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'name', 'color', 'is_active'];

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
