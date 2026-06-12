<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HelpFeedback extends Model
{
    protected $table = 'help_feedback';

    protected $fillable = [
        'help_article_id',
        'user_id',
        'is_helpful',
        'comment',
    ];

    protected $casts = [
        'is_helpful' => 'boolean',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(HelpArticle::class, 'help_article_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
