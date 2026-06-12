<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'interval',
        'stripe_price_id',
        'max_users',
        'max_attendants',
        'max_channels',
        'max_chatbots',
        'max_ai_conversations',
        'features',
        'is_active',
        'is_popular',
        'has_advanced_customization',
        'has_quick_replies',
        'has_contextual_ai',
        'has_chatbot_faq',
    ];

    protected $casts = [
        'features' => 'array',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_popular' => 'boolean',
        'max_users' => 'integer',
        'max_attendants' => 'integer',
        'max_channels' => 'integer',
        'max_chatbots' => 'integer',
        'max_ai_conversations' => 'integer',
        'has_advanced_customization' => 'boolean',
        'has_quick_replies' => 'boolean',
        'has_contextual_ai' => 'boolean',
        'has_chatbot_faq' => 'boolean',
    ];
}
