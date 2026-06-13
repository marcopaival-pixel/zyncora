<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chatbot extends Model
{
    use BelongsToCompany;

    public const STATUS_INCOMPLETE = 'config_incompleta';

    public const STATUS_CONFIGURING = 'em_configuracao';

    public const STATUS_READY = 'pronto_publicar';

    public const STATUS_PUBLISHED = 'publicado';

    public const STATUS_ACTIVE = 'active'; // Keeping 'active' for backwards compatibility

    public const STATUS_PAUSED = 'pausado';

    protected $fillable = [
        'company_id',
        'channel_id',
        'name',
        'whatsapp_phone',
        'initial_message',
        'hours_start',
        'hours_end',
        'default_channel',
        'status',
        'use_ai',
        'ai_instruction',
        'flow_data',
        'published_flow_data',
        'avatar_path',
        'avatar_type',
        'primary_color',
        'secondary_color',
        'header_color',
        'message_color',
        'out_of_office_message',
        'is_menu_enabled',
        'mascot_type',
        'mascot_greeting',
    ];

    // company() relation is provided by BelongsToCompany trait

    protected function casts(): array
    {
        return [
            'use_ai' => 'boolean',
            'flow_data' => 'array',
            'published_flow_data' => 'array',
            'is_menu_enabled' => 'boolean',
        ];
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function aiAuditLogs(): HasMany
    {
        return $this->hasMany(AiAuditLog::class);
    }

    public function scriptSteps(): HasMany
    {
        return $this->hasMany(ChatbotScriptStep::class)->orderBy('step_order');
    }

    public function actionCards(): HasMany
    {
        return $this->hasMany(ChatbotActionCard::class)->orderBy('order_column');
    }

    /**
     * Escolhe um chatbot ativo da empresa de forma consistente em todo o sistema:
     * 1) ligado ao mesmo {@see Channel} da conversa;
     * 2) sem canal específico, mas com {@see self::$default_channel} igual ao tipo do canal;
     * 3) qualquer ativo da empresa (mais recente primeiro).
     *
     * @param  callable(Builder): void|null  $queryModifier  Ex.: fn (Builder $q) => $q->where('use_ai', true)
     */
    public static function resolveForConversation(Conversation $conversation, ?callable $queryModifier = null): ?self
    {
        $apply = function (Builder $q) use ($queryModifier): Builder {
            if ($queryModifier !== null) {
                $queryModifier($q);
            }

            return $q;
        };

        $base = function () use ($conversation): Builder {
            return static::withoutGlobalScope('company')
                ->where('company_id', $conversation->company_id)
                ->where('status', 'active');
        };

        if ($conversation->channel_id) {
            $channel = $conversation->relationLoaded('channel')
                ? $conversation->channel
                : Channel::query()->find($conversation->channel_id);

            $matchChannel = $apply($base())
                ->where('channel_id', $conversation->channel_id)
                ->orderByDesc('id')
                ->first();
            if ($matchChannel !== null) {
                return $matchChannel;
            }

            if ($channel && filled($channel->type)) {
                $matchType = $apply($base())
                    ->whereNull('channel_id')
                    ->where('default_channel', $channel->type)
                    ->orderByDesc('id')
                    ->first();
                if ($matchType !== null) {
                    return $matchType;
                }
            }
        }

        return $apply($base())->orderByDesc('id')->first();
    }
}
