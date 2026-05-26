<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deal extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'contact_id',
        'pipeline_stage_id',
        'title',
        'value',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
        ];
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'pipeline_stage_id');
    }
}
