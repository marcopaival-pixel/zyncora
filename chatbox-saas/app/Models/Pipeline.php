<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Pipeline extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'name',
    ];

    public function stages(): HasMany
    {
        return $this->hasMany(PipelineStage::class)->orderBy('sort_order');
    }

    /**
     * Todos os negócios associados a etapas deste funil.
     */
    public function deals(): HasManyThrough
    {
        return $this->hasManyThrough(
            Deal::class,
            PipelineStage::class,
            'pipeline_id',
            'pipeline_stage_id',
            'id',
            'id'
        );
    }
}
