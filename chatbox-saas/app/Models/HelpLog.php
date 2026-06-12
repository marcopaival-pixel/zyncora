<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToCompany;

class HelpLog extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'help_article_id',
        'user_id',
        'company_id',
        'action',
        'search_term',
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
