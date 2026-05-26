<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Traits\BelongsToCompany;

class AttendanceQueue extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'priority',
    ];

    // company() relation is provided by BelongsToCompany trait

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'attendance_queue_id');
    }
}
