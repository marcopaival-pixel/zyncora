<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'payment_history_id',
        'external_id',
        'status',
        'amount',
        'pdf_url',
        'xml_url',
        'error_message',
        'issued_at',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function paymentHistory()
    {
        return $this->belongsTo(PaymentHistory::class);
    }
}
