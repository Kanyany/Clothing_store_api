<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    protected $fillable = [
        'user_id',
        'dark_mode',
        'notifications',
        'language',

        // FIX: Order timeline duration settings.
        'processing_days',
        'shipping_days',
        'delivery_days',
    ];

    protected $casts = [
        'dark_mode' => 'boolean',
        'notifications' => 'boolean',

        // FIX: Store timeline duration values as integers.
        'processing_days' => 'integer',
        'shipping_days' => 'integer',
        'delivery_days' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
