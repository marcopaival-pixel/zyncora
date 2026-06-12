<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Traits\BelongsToCompany;

class Conversation extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'department_id',
        'channel_id',
        'attendance_queue_id',
        'visitor_token',
        'client_name',
        'client_phone',
        'client_email',
        'status',
        'assignee_id',
        'started_at',
        'closed_at',
        'ai_score',
        'ai_sentiment',
        'ai_summary',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function queue(): BelongsTo
    {
        return $this->belongsTo(AttendanceQueue::class, 'attendance_queue_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
