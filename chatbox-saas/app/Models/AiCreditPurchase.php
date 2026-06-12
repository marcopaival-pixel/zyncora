<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;

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
