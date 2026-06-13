<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = [
        'user_id',
        'company_id',
        'event',
        'description',
        'subject_type',
        'subject_id',
        'ip_address',
        'user_agent',
        'properties',
    ];

    protected $casts = [
        'properties' => 'json',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
