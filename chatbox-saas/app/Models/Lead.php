<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'message',
        'status',
        'company',
        'role',
        'whatsapp',
        'attendants_qty',
        'segment',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'referer',
        'browser',
        'device',
        'os',
        'country',
        'city',
        'ip',
        'origin',
    ];
}
