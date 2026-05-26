<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class QuickReply extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'shortcut', 'message'];
}
