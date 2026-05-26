<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageSchedule extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'contact_id',
        'user_id',
        'message',
        'send_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'send_at' => 'datetime',
        ];
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
