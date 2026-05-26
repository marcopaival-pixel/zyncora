<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Traits\BelongsToCompany;

class Chatbot extends Model
{
    use BelongsToCompany;

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
    ];

    // company() relation is provided by BelongsToCompany trait

    protected function casts(): array
    {
        return [
            'use_ai' => 'boolean',
            'flow_data' => 'array',
            'published_flow_data' => 'array',
        ];
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function scriptSteps(): HasMany
    {
        return $this->hasMany(ChatbotScriptStep::class)->orderBy('step_order');
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
