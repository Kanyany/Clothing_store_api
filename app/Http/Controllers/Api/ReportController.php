<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    /**
     * Sales Summary
     */
    public function salesSummary(): JsonResponse
    {
        $totalSales = Sale::where('status', 'completed')
            ->count();

        $totalRevenue = Sale::where('status', 'completed')
            ->sum('total_amount');

        $totalDiscount = Sale::where('status', 'completed')
            ->sum('discount');

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_sales' => $totalSales,
                'total_revenue' => $totalRevenue,
                'total_discount' => $totalDiscount,
            ],
        ]);
    }
}