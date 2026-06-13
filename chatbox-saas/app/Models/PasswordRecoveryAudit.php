<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordRecoveryAudit extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'ip_address',
        'user_agent',
        'action',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
