<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformLegalDocument extends Model
{
    protected $fillable = [
        'type',
        'version',
        'content',
        'published_at',
        'is_active',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function consents()
    {
        return $this->hasMany(PlatformLegalConsent::class);
    }
}
