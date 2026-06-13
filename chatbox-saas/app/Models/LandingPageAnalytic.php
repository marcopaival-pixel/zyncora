<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingPageAnalytic extends Model
{
    protected $fillable = [
        'type',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'referer',
        'browser',
        'device',
        'os',
        'ip',
    ];
}
