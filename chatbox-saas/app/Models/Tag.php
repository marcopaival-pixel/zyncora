<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'name', 'color'];

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class);
    }
}
