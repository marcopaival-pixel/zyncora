<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
