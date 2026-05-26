<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use App\Observers\CompanyObserver;

#[ObservedBy(CompanyObserver::class)]
class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'cnpj',
        'email',
        'phone',
        'logo_path',
        'chat_color',
        'brand_color',
        'favicon_path',
        'panel_logo_path',
        'welcome_message',
        'offline_message',
        'business_hours',
        'auto_reply_enabled',
        'status',
        'plan_id',
        'plan',
        'max_users',
        'max_attendants',
        'max_channels',
        'max_chatbots',
        'expires_at',
        'stripe_customer_id',
        'stripe_subscription_id',
        'subscription_status',
        'mercadopago_preapproval_id',
        'expiry_warning_sent_at',
        'grace_period_notified_at',
    ];

    protected function casts(): array
    {
        return [
            'business_hours' => 'array',
            'auto_reply_enabled' => 'boolean',
            'expires_at' => 'datetime',
            'expiry_warning_sent_at' => 'datetime',
            'grace_period_notified_at' => 'datetime',
            'max_users' => 'integer',
            'max_attendants' => 'integer',
            'max_channels' => 'integer',
            'max_chatbots' => 'integer',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function channels(): HasMany
    {
        return $this->hasMany(Channel::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function chatbots(): HasMany
    {
        return $this->hasMany(Chatbot::class);
    }

    public function integrations(): HasMany
    {
        return $this->hasMany(CompanyIntegration::class);
    }

    public function attendanceQueues(): HasMany
    {
        return $this->hasMany(AttendanceQueue::class);
    }

    public function chatLogs(): HasMany
    {
        return $this->hasMany(ChatLog::class);
    }

    public function chatbotFlows(): HasMany
    {
        return $this->hasMany(ChatbotFlow::class);
    }
}
