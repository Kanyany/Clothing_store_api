<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CurrencyRate;
use Illuminate\Http\Request;

class CurrencyRateController extends Controller
{
    /**
     * Get the active USD -> KHR exchange rate.
     */
    public function show()
    {
        $currencyRate = CurrencyRate::where('base_currency', 'USD')
            ->where('target_currency', 'KHR')
            ->where('is_active', true)
            ->first();

        if (!$currencyRate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Currency rate is not configured.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'base_currency' => $currencyRate->base_currency,
                'target_currency' => $currencyRate->target_currency,
                'rate' => (float) $currencyRate->rate,
                'updated_at' => $currencyRate->updated_at,
            ],
        ]);
    }

    /**
     * Create or update the USD -> KHR exchange rate.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'rate' => ['required', 'numeric', 'gt:0'],
        ]);

        $currencyRate = CurrencyRate::updateOrCreate(
            [
                'base_currency' => 'USD',
                'target_currency' => 'KHR',
            ],
            [
                'rate' => $validated['rate'],
                'is_active' => true,
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Currency rate updated successfully.',
            'data' => [
                'base_currency' => $currencyRate->base_currency,
                'target_currency' => $currencyRate->target_currency,
                'rate' => (float) $currencyRate->rate,
                'updated_at' => $currencyRate->updated_at,
            ],
        ]);
    }
}