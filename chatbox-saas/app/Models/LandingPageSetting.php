<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingPageSetting extends Model
{
    protected $fillable = [
        'hero_title',
        'hero_subtitle',
        'stats',
        'benefits',
        'primary_cta_text',
        'secondary_cta_text',
        'trial_days',
        'success_message_title',
        'success_message_subtitle',
        'contact_email',
        'contact_whatsapp',
        'contact_phone',
        'social_linkedin',
        'social_instagram',
        'social_facebook',
        'social_youtube',
    ];

    protected $casts = [
        'stats' => 'array',
        'benefits' => 'array',
    ];
}
