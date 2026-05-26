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
        'features',
        'is_active',
        'is_popular',
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
    ];
}
