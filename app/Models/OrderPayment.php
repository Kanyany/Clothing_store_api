<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPayment extends Model
{
    protected $fillable = [
    'order_id',
    'amount',
    'amount_usd',
    'currency',
    'payment_method',
    'payment_provider',
    'reference_number',
    'qr',
    'md5',
    'transaction_hash',
    'deeplink',
    'paid_at',
    'note',
];
    protected $casts = [
        'amount' => 'decimal:2',
        'amount_usd' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}