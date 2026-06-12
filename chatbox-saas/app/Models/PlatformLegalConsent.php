<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformLegalConsent extends Model
{
    protected $fillable = [
        'user_id',
        'platform_legal_document_id',
        'ip_address',
        'user_agent',
        'accepted_at',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function document()
    {
        return $this->belongsTo(PlatformLegalDocument::class, 'platform_legal_document_id');
    }
}
