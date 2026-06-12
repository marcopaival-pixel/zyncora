<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformLgpdRequest extends Model
{
    protected $fillable = [
        'protocol',
        'name',
        'email',
        'request_type',
        'status',
        'details',
    ];
}
