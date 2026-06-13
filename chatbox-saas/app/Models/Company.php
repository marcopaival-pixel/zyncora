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
    use HasFactory, \App\Traits\HasHealthScore;

    protected $fillable = [
        'name',
        'legal_name',
        'responsible_name',
        'slug',
        'custom_domain',
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
        'is_onboarding_completed',
        'plan_id',
        'plan',
        'max_users',
        'max_attendants',
        'max_channels',
        'max_chatbots',
        'stripe_id',
        'stripe_subscription_id',
        'stripe_price_id',
        'stripe_status',
        'mp_preapproval_id',
        'has_advanced_customization',
        'has_quick_replies',
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
        'expires_at',
        'stripe_customer_id',
        'subscription_status',
        'mercadopago_preapproval_id',
        'expiry_warning_sent_at',
        'grace_period_notified_at',
        'segment',
        'ai_credits_balance',
        'ai_credits_used',
        'ai_conversations_used',
        'ai_limit_action',
        'auto_buy_package',
        'trial_start_at',
        'trial_end_at',
        'whatsapp',
        'address',
        'social_networks',
    ];

    protected function casts(): array
    {
        return [
            'business_hours' => 'array',
            'auto_reply_enabled' => 'boolean',
            'is_onboarding_completed' => 'boolean',
            'expires_at' => 'datetime',
            'expiry_warning_sent_at' => 'datetime',
            'grace_period_notified_at' => 'datetime',
            'trial_start_at' => 'datetime',
            'trial_end_at' => 'datetime',
            'max_users' => 'integer',
            'max_attendants' => 'integer',
            'max_channels' => 'integer',
            'max_chatbots' => 'integer',
            'ai_credits_balance' => 'integer',
            'ai_credits_used' => 'integer',
            'ai_conversations_used' => 'integer',
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
            'social_networks' => 'array',
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

    public function chatLogs()
    {
        return $this->hasMany(ChatLog::class);
    }

    public function webhookDeliveries()
    {
        return $this->hasMany(WebhookDelivery::class);
    }

    public function paymentHistories()
    {
        return $this->hasMany(PaymentHistory::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class);
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function chatbotFlows(): HasMany
    {
        return $this->hasMany(ChatbotFlow::class);
    }

    public function knowledgeBases(): HasMany
    {
        return $this->hasMany(KnowledgeBase::class);
    }

    /**
     * Calcula os dias restantes do Trial com base na data do servidor
     */
    public function calcularDiasRestantes(): int
    {
        if (!$this->trial_end_at || $this->subscription_status !== 'trial') {
            return 0;
        }

        $diasRestantes = now()->diffInDays($this->trial_end_at, false);
        return max(0, (int) $diasRestantes);
    }

    /**
     * Verifica o status do Trial. Se expirou, atualiza o banco e registra o log.
     */
    public function verificarStatusAssinatura(): string
    {
        if ($this->subscription_status === 'trial' && $this->trial_end_at && now()->greaterThanOrEqualTo($this->trial_end_at)) {
            $this->update(['subscription_status' => 'expired']);

            SubscriptionAuditLog::create([
                'company_id' => $this->id,
                'action' => 'trial_expired',
                'old_status' => 'trial',
                'new_status' => 'expired',
                'trial_start_at' => $this->trial_start_at,
                'trial_end_at' => $this->trial_end_at,
                'notes' => 'Trial expirou automaticamente.',
            ]);

            return 'expired';
        }

        return $this->subscription_status ?? 'active';
    }
}
