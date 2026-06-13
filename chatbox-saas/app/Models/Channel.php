<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Channel extends Model
{
    use BelongsToCompany;

    /**
     * Verifica se a empresa deste canal já atingiu o limite contratado no plano.
     */
    public static function canAddMoreChannels(Company $company): bool
    {
        $limit = $company->max_channels ?? ($company->plan ? $company->plan->max_channels : 0);
        if ($limit === null || $limit === 0) {
            return false;
        }

        return $company->channels()->count() < $limit;
    }

    protected $fillable = [
        'company_id',
        'type',
        'external_ref',
        'token_api',
        'status',
    ];

    // company() relation is provided by BelongsToCompany trait

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function scopeConnected($query)
    {
        return $query->where('status', 'connected');
    }

    public function scopeDisconnected($query)
    {
        return $query->where('status', 'disconnected');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
