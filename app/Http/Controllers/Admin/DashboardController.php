<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Date Range
        |--------------------------------------------------------------------------
        | Quick presets are available, but users can also choose any
        | start/end date from the calendar inputs.
        */
        $today = Carbon::today();

        $allowedPeriods = [3, 7, 14, 30];

        $selectedPeriod = (int) $request->input('period', 7);

        if (! in_array($selectedPeriod, $allowedPeriods, true)) {
            $selectedPeriod = 7;
        }

        $requestedStart = $request->input('start_date');
        $requestedEnd = $request->input('end_date');

        $hasCustomRange = filled($requestedStart) || filled($requestedEnd);

        if ($hasCustomRange) {
            try {
                $customStart = Carbon::createFromFormat(
                    'Y-m-d',
                    (string) $requestedStart
                )->startOfDay();

                $customEnd = Carbon::createFromFormat(
                    'Y-m-d',
                    (string) $requestedEnd
                )->endOfDay();

                if (
                    $customStart->gt($customEnd) ||
                    $customEnd->gt($today->copy()->endOfDay())
                ) {
                    throw new \Exception('Invalid custom range.');
                }

                $startDate = $customStart;
                $endDate = $customEnd;
                $selectedPeriod = null;
                $periodLabel = 'Custom Date Range';
            } catch (\Throwable $e) {
                $endDate = $today->copy()->endOfDay();
                $startDate = $today
                    ->copy()
                    ->subDays(6)
                    ->startOfDay();

                $selectedPeriod = 7;
                $periodLabel = 'Last 7 Days';
                $requestedStart = null;
                $requestedEnd = null;
                $hasCustomRange = false;
            }
        } else {
            $endDate = $today->copy()->endOfDay();

            $startDate = $today
                ->copy()
                ->subDays($selectedPeriod - 1)
                ->startOfDay();

            $periodLabel = match ($selectedPeriod) {
                3 => 'Last 3 Days',
                7 => 'Last 7 Days',
                14 => 'Last 2 Weeks',
                30 => 'Last 1 Month',
                default => 'Last 7 Days',
            };

            $requestedStart = $startDate->format('Y-m-d');
            $requestedEnd = $endDate->format('Y-m-d');
        }

        $rangeDays = $startDate
            ->copy()
            ->startOfDay()
            ->diffInDays($endDate->copy()->startOfDay()) + 1;

        /*
        |--------------------------------------------------------------------------
        | Basic Catalog / Inventory
        |--------------------------------------------------------------------------
        */
        $totalProducts = Product::count();
        $activeProducts = Product::where('status', true)->count();
        $inactiveProducts = Product::where('status', false)->count();
        $totalVariants = ProductVariant::count();

        $totalStock = (int) Inventory::sum('quantity');

        $lowStockCount = (int) Inventory::query()
            ->whereColumn('quantity', '<=', 'low_stock_threshold')
            ->where('quantity', '>', 0)
            ->count();

        $outOfStockCount = (int) Inventory::where('quantity', '<=', 0)->count();

        /*
        |--------------------------------------------------------------------------
        | Sales In Selected Period
        |--------------------------------------------------------------------------
        */
        $salesQuery = Sale::query()
            ->where('status', 'completed')
            ->whereBetween('sale_date', [$startDate, $endDate]);

        $totalSales = (float) (clone $salesQuery)->sum('total_amount');
        $totalOrders = (int) (clone $salesQuery)->count();

        /*
        |--------------------------------------------------------------------------
        | Cost + Profit
        |--------------------------------------------------------------------------
        | Profit = completed sales total - actual cost of sold variants.
        | No fake/default sales values are used.
        */
        $totalCost = (float) DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join(
                'product_variants',
                'product_variants.id',
                '=',
                'sale_items.product_variant_id'
            )
            ->where('sales.status', 'completed')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->selectRaw(
                'COALESCE(SUM(sale_items.quantity * product_variants.cost_price), 0) as total_cost'
            )
            ->value('total_cost');

        $totalProfit = $totalSales - $totalCost;

        $averageOrderValue = $totalOrders > 0
            ? $totalSales / $totalOrders
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Daily Sales Overview
        |--------------------------------------------------------------------------
        */
        $salesByDate = Sale::query()
            ->where('status', 'completed')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->selectRaw('DATE(sale_date) as sale_day')
            ->selectRaw('COALESCE(SUM(total_amount), 0) as total_sales')
            ->selectRaw('COUNT(*) as total_orders')
            ->groupBy('sale_day')
            ->orderBy('sale_day')
            ->get()
            ->keyBy('sale_day');

        $salesOverview = [];

        for ($i = 0; $i < $rangeDays; $i++) {
            $date = $startDate->copy()->addDays($i);
            $dateKey = $date->format('Y-m-d');

            $day = $salesByDate->get($dateKey);

            $salesOverview[] = [
                'date' => $dateKey,
                'label' => $rangeDays <= 7
                    ? $date->format('D')
                    : $date->format('d M'),
                'short_label' => $date->format('d'),
                'sales' => $day ? (float) $day->total_sales : 0,
                'orders' => $day ? (int) $day->total_orders : 0,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Top Selling Products
        |--------------------------------------------------------------------------
        */
        $topSellingProducts = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join(
                'product_variants',
                'product_variants.id',
                '=',
                'sale_items.product_variant_id'
            )
            ->join(
                'products',
                'products.id',
                '=',
                'product_variants.product_id'
            )
            ->where('sales.status', 'completed')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->select(
                'products.id',
                'products.name',
                'products.image'
            )
            ->selectRaw('SUM(sale_items.quantity) as total_quantity')
            ->selectRaw('SUM(sale_items.subtotal) as total_sales')
            ->selectRaw(
                'SUM(sale_items.quantity * product_variants.cost_price) as total_cost'
            )
            ->selectRaw(
                'COUNT(DISTINCT sale_items.sale_id) as total_orders'
            )
            ->groupBy(
                'products.id',
                'products.name',
                'products.image'
            )
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get()
            ->map(function ($product) {
                $product->total_quantity = (int) $product->total_quantity;
                $product->total_sales = (float) $product->total_sales;
                $product->total_cost = (float) $product->total_cost;
                $product->profit = $product->total_sales - $product->total_cost;
                $product->total_orders = (int) $product->total_orders;

                return $product;
            });

        /*
        |--------------------------------------------------------------------------
        | Sales By Category
        |--------------------------------------------------------------------------
        */
        $salesByCategory = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join(
                'product_variants',
                'product_variants.id',
                '=',
                'sale_items.product_variant_id'
            )
            ->join(
                'products',
                'products.id',
                '=',
                'product_variants.product_id'
            )
            ->leftJoin(
                'categories',
                'categories.id',
                '=',
                'products.category_id'
            )
            ->where('sales.status', 'completed')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->select(
                'categories.id',
                DB::raw("COALESCE(categories.name, 'Uncategorized') as name")
            )
            ->selectRaw('SUM(sale_items.subtotal) as total_sales')
            ->selectRaw('SUM(sale_items.quantity) as total_quantity')
            ->groupBy(
                'categories.id',
                'categories.name'
            )
            ->orderByDesc('total_sales')
            ->get();

        $categorySalesTotal = (float) $salesByCategory->sum('total_sales');

        $salesByCategory = $salesByCategory->map(
            function ($category) use ($categorySalesTotal) {
                $category->total_sales = (float) $category->total_sales;
                $category->total_quantity = (int) $category->total_quantity;
                $category->percentage = $categorySalesTotal > 0
                    ? round(
                        ($category->total_sales / $categorySalesTotal) * 100,
                        1
                    )
                    : 0;

                return $category;
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Recent Sales
        |--------------------------------------------------------------------------
        */
        $recentSales = Sale::query()
            ->where('status', 'completed')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->latest('sale_date')
            ->latest('id')
            ->limit(6)
            ->get([
                'id',
                'invoice_number',
                'sale_date',
                'total_amount',
                'payment_method',
            ]);

        $stats = [
            'total_sales' => $totalSales,
            'total_orders' => $totalOrders,
            'total_profit' => $totalProfit,
            'total_cost' => $totalCost,
            'average_order_value' => $averageOrderValue,

            'total_products' => $totalProducts,
            'active_products' => $activeProducts,
            'inactive_products' => $inactiveProducts,
            'total_variants' => $totalVariants,

            'total_stock' => $totalStock,
            'low_stock' => $lowStockCount,
            'out_of_stock' => $outOfStockCount,
        ];

        return view('admin.dashboard.index', [
            'stats' => $stats,
            'salesOverview' => $salesOverview,
            'topSellingProducts' => $topSellingProducts,
            'salesByCategory' => $salesByCategory,
            'categorySalesTotal' => $categorySalesTotal,
            'recentSales' => $recentSales,
            'selectedPeriod' => $selectedPeriod,
            'periodLabel' => $periodLabel,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'rangeDays' => $rangeDays,
            'hasCustomRange' => $hasCustomRange,
            'customStartDate' => $requestedStart,
            'customEndDate' => $requestedEnd,
        ]);
    }
}
