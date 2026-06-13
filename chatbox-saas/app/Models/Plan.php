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
        'price_yearly',
        'sort_order',
        'update_behavior',
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
        'has_contextual_ai',
        'has_chatbot_faq',
        'max_messages',
        'max_integrations',
        'has_whatsapp',
        'has_telegram',
        'has_instagram',
        'has_facebook',
        'has_webchat',
        'has_openai',
        'has_rag',
        'has_inbox',
        'has_flow_builder',
        'has_api',
        'has_whitelabel',
        'has_webhooks',
    ];

    protected $casts = [
        'features' => 'array',
        'price' => 'decimal:2',
        'price_yearly' => 'decimal:2',
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
        'max_messages' => 'integer',
        'max_integrations' => 'integer',
        'has_whatsapp' => 'boolean',
        'has_telegram' => 'boolean',
        'has_instagram' => 'boolean',
        'has_facebook' => 'boolean',
        'has_webchat' => 'boolean',
        'has_openai' => 'boolean',
        'has_rag' => 'boolean',
        'has_inbox' => 'boolean',
        'has_flow_builder' => 'boolean',
        'has_api' => 'boolean',
        'has_whitelabel' => 'boolean',
        'has_webhooks' => 'boolean',
    ];

    public function auditLogs()
    {
        return $this->hasMany(PlanAuditLog::class);
    }
}
