<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HelpArticle extends Model
{
    protected $fillable = [
        'help_category_id',
        'title',
        'slug',
        'description',
        'content',
        'examples_by_segment',
        'module',
        'order_column',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'examples_by_segment' => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(HelpCategory::class, 'help_category_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(HelpLog::class);
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(HelpFeedback::class);
    }
}
