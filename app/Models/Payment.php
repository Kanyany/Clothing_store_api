<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'sale_id',
        'amount',
        'payment_method',
        'payment_provider',
        'reference_number',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Payment belongs to a Sale
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}