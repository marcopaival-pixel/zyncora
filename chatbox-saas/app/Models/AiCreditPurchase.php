<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class AiCreditPurchase extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'package_name',
        'conversations_added',
        'price',
        'payment_method',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'conversations_added' => 'integer',
            'price' => 'decimal:2',
        ];
    }
}
