<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->buildReportData($request);

        return view('admin.reports.index', $data);
    }

    public function exportCsv(Request $request)
    {
        $data = $this->buildReportData($request);

        return response()->streamDownload(function () use ($data) {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['CLOTHING STORE - REPORT']);
            fputcsv($out, ['Date From', $data['dateFrom'] ?: 'All']);
            fputcsv($out, ['Date To', $data['dateTo'] ?: 'All']);
            fputcsv($out, []);

            fputcsv($out, ['SALES SUMMARY']);
            fputcsv($out, ['Total Sales', $data['stats']['total_sales']]);
            fputcsv($out, ['Total Orders', $data['stats']['total_orders']]);
            fputcsv($out, ['Items Sold', $data['stats']['items_sold']]);
            fputcsv($out, ['Average Order Value', $data['stats']['average_order_value']]);

            fputcsv($out, []);
            fputcsv($out, ['PROFIT SUMMARY']);
            fputcsv($out, ['Revenue', $data['profit']['revenue']]);
            fputcsv($out, ['Cost of Goods Sold', $data['profit']['cogs']]);
            fputcsv($out, ['Gross Profit', $data['profit']['gross_profit']]);
            fputcsv($out, ['Gross Profit Margin %', $data['profit']['gross_margin']]);

            fputcsv($out, []);
            fputcsv($out, ['PRODUCT REPORT']);
            fputcsv($out, ['Product', 'Quantity Sold', 'Revenue', 'Cost', 'Profit']);

            foreach ($data['products'] as $row) {
                fputcsv($out, [
                    $row->name,
                    $row->quantity,
                    $row->revenue,
                    $row->cost,
                    $row->profit,
                ]);
            }

            fputcsv($out, []);
            fputcsv($out, ['CATEGORY REPORT']);
            fputcsv($out, ['Category', 'Quantity', 'Revenue', 'Cost', 'Profit']);

            foreach ($data['categories'] as $row) {
                fputcsv($out, [
                    $row->name,
                    $row->quantity,
                    $row->revenue,
                    $row->cost,
                    $row->profit,
                ]);
            }

            fputcsv($out, []);
            fputcsv($out, ['GENDER REPORT']);
            fputcsv($out, ['Gender', 'Quantity', 'Revenue', 'Cost', 'Profit']);

            foreach ($data['genders'] as $row) {
                fputcsv($out, [
                    $row->name,
                    $row->quantity,
                    $row->revenue,
                    $row->cost,
                    $row->profit,
                ]);
            }

            fputcsv($out, []);
            fputcsv($out, ['PAYMENT REPORT']);
            fputcsv($out, ['Method', 'Orders', 'Revenue']);

            foreach ($data['payments'] as $row) {
                fputcsv($out, [
                    $row['label'],
                    $row['orders'],
                    $row['revenue'],
                ]);
            }

            fputcsv($out, []);
            fputcsv($out, ['INVENTORY SUMMARY']);
            fputcsv($out, ['Current Stock', $data['inventory']['current_stock']]);
            fputcsv($out, ['Stock Value', $data['inventory']['stock_value']]);
            fputcsv($out, ['Low Stock', $data['inventory']['low_stock']]);
            fputcsv($out, ['Out of Stock', $data['inventory']['out_of_stock']]);

            fputcsv($out, []);
            fputcsv($out, ['PURCHASE SUMMARY']);
            fputcsv($out, ['Total Purchases', $data['purchases']['total_purchases']]);
            fputcsv($out, ['Purchase Cost', $data['purchases']['purchase_cost']]);
            fputcsv($out, ['Items Purchased', $data['purchases']['items_purchased']]);

            fclose($out);
        }, 'clothing-store-report.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportExcel(Request $request)
    {
        $data = $this->buildReportData($request);

        return response()->streamDownload(function () use ($data) {
            echo '<html><head><meta charset="UTF-8"></head><body>';
            echo '<h2>CLOTHING STORE - REPORT</h2>';
            echo '<p>Date From: ' . e($data['dateFrom'] ?: 'All') . '</p>';
            echo '<p>Date To: ' . e($data['dateTo'] ?: 'All') . '</p>';

            echo '<h3>Sales Summary</h3><table border="1">';
            foreach ([
                'Total Sales' => $data['stats']['total_sales'],
                'Total Orders' => $data['stats']['total_orders'],
                'Items Sold' => $data['stats']['items_sold'],
                'Average Order Value' => $data['stats']['average_order_value'],
            ] as $label => $value) {
                echo '<tr><td>' . e($label) . '</td><td>' . e($value) . '</td></tr>';
            }
            echo '</table>';

            echo '<h3>Profit Summary</h3><table border="1">';
            foreach ([
                'Revenue' => $data['profit']['revenue'],
                'Cost of Goods Sold' => $data['profit']['cogs'],
                'Gross Profit' => $data['profit']['gross_profit'],
                'Gross Profit Margin %' => $data['profit']['gross_margin'],
            ] as $label => $value) {
                echo '<tr><td>' . e($label) . '</td><td>' . e($value) . '</td></tr>';
            }
            echo '</table>';

            echo '<h3>Product Report</h3><table border="1">';
            echo '<tr><th>Product</th><th>Quantity Sold</th><th>Revenue</th><th>Cost</th><th>Profit</th></tr>';
            foreach ($data['products'] as $row) {
                echo '<tr><td>' . e($row->name) . '</td><td>' . e($row->quantity) . '</td><td>' . e($row->revenue) . '</td><td>' . e($row->cost) . '</td><td>' . e($row->profit) . '</td></tr>';
            }
            echo '</table>';

            echo '<h3>Category Report</h3><table border="1">';
            echo '<tr><th>Category</th><th>Quantity</th><th>Revenue</th><th>Cost</th><th>Profit</th></tr>';
            foreach ($data['categories'] as $row) {
                echo '<tr><td>' . e($row->name) . '</td><td>' . e($row->quantity) . '</td><td>' . e($row->revenue) . '</td><td>' . e($row->cost) . '</td><td>' . e($row->profit) . '</td></tr>';
            }
            echo '</table>';

            echo '<h3>Gender Report</h3><table border="1">';
            echo '<tr><th>Gender</th><th>Quantity</th><th>Revenue</th><th>Cost</th><th>Profit</th></tr>';
            foreach ($data['genders'] as $row) {
                echo '<tr><td>' . e($row->name) . '</td><td>' . e($row->quantity) . '</td><td>' . e($row->revenue) . '</td><td>' . e($row->cost) . '</td><td>' . e($row->profit) . '</td></tr>';
            }
            echo '</table>';

            echo '<h3>Payment Report</h3><table border="1">';
            echo '<tr><th>Method</th><th>Orders</th><th>Revenue</th></tr>';
            foreach ($data['payments'] as $row) {
                echo '<tr><td>' . e($row['label']) . '</td><td>' . e($row['orders']) . '</td><td>' . e($row['revenue']) . '</td></tr>';
            }
            echo '</table>';

            echo '<h3>Inventory Summary</h3><table border="1">';
            foreach ([
                'Current Stock' => $data['inventory']['current_stock'],
                'Stock Value' => $data['inventory']['stock_value'],
                'Low Stock' => $data['inventory']['low_stock'],
                'Out of Stock' => $data['inventory']['out_of_stock'],
            ] as $label => $value) {
                echo '<tr><td>' . e($label) . '</td><td>' . e($value) . '</td></tr>';
            }
            echo '</table>';

            echo '<h3>Purchase Summary</h3><table border="1">';
            foreach ([
                'Total Purchases' => $data['purchases']['total_purchases'],
                'Purchase Cost' => $data['purchases']['purchase_cost'],
                'Items Purchased' => $data['purchases']['items_purchased'],
            ] as $label => $value) {
                echo '<tr><td>' . e($label) . '</td><td>' . e($value) . '</td></tr>';
            }
            echo '</table>';

            echo '</body></html>';
        }, 'clothing-store-report.xls', [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    public function exportPdf(Request $request)
    {
        $data = $this->buildReportData($request);

        return view('admin.reports.pdf', $data);
    }

    private function buildReportData(Request $request): array
    {
        $from = $request->input('date_from');
        $to = $request->input('date_to');

        $completed = DB::table('sales')
            ->where('status', 'completed')
            ->when($from, fn ($q) => $q->whereDate('sale_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('sale_date', '<=', $to));

        $stats = [
            'total_sales' => (float) (clone $completed)->sum('total_amount'),
            'total_orders' => (int) (clone $completed)->count(),
            'items_sold' => (int) DB::table('sale_items')
                ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                ->where('sales.status', 'completed')
                ->when($from, fn ($q) => $q->whereDate('sales.sale_date', '>=', $from))
                ->when($to, fn ($q) => $q->whereDate('sales.sale_date', '<=', $to))
                ->sum('sale_items.quantity'),
        ];

        $stats['average_order_value'] = $stats['total_orders'] > 0
            ? $stats['total_sales'] / $stats['total_orders']
            : 0;

        $salesItems = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('product_variants', 'product_variants.id', '=', 'sale_items.product_variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->where('sales.status', 'completed')
            ->when($from, fn ($q) => $q->whereDate('sales.sale_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('sales.sale_date', '<=', $to));

        $productRows = (clone $salesItems)
            ->selectRaw('
                products.id,
                products.name,
                SUM(sale_items.quantity) AS quantity,
                SUM(sale_items.subtotal) AS revenue,
                SUM(sale_items.quantity * product_variants.cost_price) AS cost
            ')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('quantity')
            ->get();

        $products = $productRows->map(function ($row) {
            $row->quantity = (int) $row->quantity;
            $row->revenue = (float) $row->revenue;
            $row->cost = (float) $row->cost;
            $row->profit = $row->revenue - $row->cost;
            return $row;
        });

        $categoryRows = (clone $salesItems)
            ->selectRaw('
                COALESCE(categories.name, "Uncategorized") AS name,
                SUM(sale_items.quantity) AS quantity,
                SUM(sale_items.subtotal) AS revenue,
                SUM(sale_items.quantity * product_variants.cost_price) AS cost
            ')
            ->groupBy('categories.name')
            ->orderByDesc('revenue')
            ->get();

        $categories = $categoryRows->map(function ($row) {
            $row->quantity = (int) $row->quantity;
            $row->revenue = (float) $row->revenue;
            $row->cost = (float) $row->cost;
            $row->profit = $row->revenue - $row->cost;
            return $row;
        });

        $genderRows = (clone $salesItems)
            ->selectRaw('
                COALESCE(products.gender, "Unspecified") AS name,
                SUM(sale_items.quantity) AS quantity,
                SUM(sale_items.subtotal) AS revenue,
                SUM(sale_items.quantity * product_variants.cost_price) AS cost
            ')
            ->groupBy('products.gender')
            ->orderByDesc('revenue')
            ->get();

        $genders = $genderRows->map(function ($row) {
            $row->quantity = (int) $row->quantity;
            $row->revenue = (float) $row->revenue;
            $row->cost = (float) $row->cost;
            $row->profit = $row->revenue - $row->cost;
            return $row;
        });

        $revenue = $stats['total_sales'];
        $cogs = (float) $products->sum('cost');
        $grossProfit = $revenue - $cogs;

        $profit = [
            'revenue' => $revenue,
            'cogs' => $cogs,
            'gross_profit' => $grossProfit,
            'gross_margin' => $revenue > 0 ? ($grossProfit / $revenue) * 100 : 0,
        ];

        $dailyRows = (clone $completed)
            ->selectRaw('DATE(sale_date) AS day, SUM(total_amount) AS revenue')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $dailySales = $dailyRows->map(fn ($row) => [
            'label' => Carbon::parse($row->day)->format('d M'),
            'date' => Carbon::parse($row->day)->format('d M Y'),
            'value' => (float) $row->revenue,
        ])->values();

        $weeklyRows = (clone $completed)
            ->selectRaw('YEARWEEK(sale_date, 1) AS week_key, MIN(DATE(sale_date)) AS week_start, SUM(total_amount) AS revenue')
            ->groupBy('week_key')
            ->orderBy('week_key')
            ->get();

        $weeklySales = $weeklyRows->map(fn ($row) => [
            'label' => 'W' . Carbon::parse($row->week_start)->format('W'),
            'date' => Carbon::parse($row->week_start)->format('d M Y'),
            'value' => (float) $row->revenue,
        ])->values();

        $monthlyRows = (clone $completed)
            ->selectRaw('DATE_FORMAT(sale_date, "%Y-%m") AS month_key, SUM(total_amount) AS revenue')
            ->groupBy('month_key')
            ->orderBy('month_key')
            ->get();

        $monthlySales = $monthlyRows->map(fn ($row) => [
            'label' => Carbon::createFromFormat('Y-m', $row->month_key)->format('M Y'),
            'value' => (float) $row->revenue,
        ])->values();

        $paymentsRows = (clone $completed)
            ->select(
                'payment_method',
                DB::raw('COUNT(*) AS orders'),
                DB::raw('SUM(total_amount) AS revenue')
            )
            ->groupBy('payment_method')
            ->orderByDesc('revenue')
            ->get();

        $payments = $paymentsRows->map(function ($row) {
            $labels = [
                'cash' => 'Cash',
                'qr' => 'KHQR',
                'card' => 'Card',
                'bank_transfer' => 'Bank Transfer',
            ];

            return [
                'method' => $row->payment_method,
                'label' => $labels[$row->payment_method]
                    ?? ucfirst(str_replace('_', ' ', $row->payment_method ?: 'Other')),
                'orders' => (int) $row->orders,
                'revenue' => (float) $row->revenue,
            ];
        })->values();

        $paymentMethods = collect([
            ['key' => 'cash', 'label' => 'Cash'],
            ['key' => 'qr', 'label' => 'KHQR'],
            ['key' => 'bank_transfer', 'label' => 'Bank Transfer'],
            ['key' => 'other', 'label' => 'Other'],
        ])->map(function ($item) use ($payments) {
            $match = $payments->firstWhere('method', $item['key']);

            if ($item['key'] === 'other') {
                $others = $payments->reject(fn ($p) => in_array($p['method'], ['cash', 'qr', 'bank_transfer']));
                return [
                    'label' => 'Other',
                    'orders' => (int) $others->sum('orders'),
                    'revenue' => (float) $others->sum('revenue'),
                ];
            }

            return [
                'label' => $item['label'],
                'orders' => $match['orders'] ?? 0,
                'revenue' => $match['revenue'] ?? 0,
            ];
        })->values();

        $inventoryRow = DB::table('inventory')
            ->selectRaw('
                COALESCE(SUM(quantity), 0) AS current_stock,
                COALESCE(SUM(quantity * 0), 0) AS unused_value
            ')
            ->first();

        $stockValue = (float) DB::table('inventory')
            ->join('product_variants', 'product_variants.id', '=', 'inventory.product_variant_id')
            ->sum(DB::raw('inventory.quantity * product_variants.cost_price'));

        $inventory = [
            'current_stock' => (int) ($inventoryRow->current_stock ?? 0),
            'stock_value' => $stockValue,
            'low_stock' => (int) DB::table('inventory')
                ->whereColumn('quantity', '<=', 'low_stock_threshold')
                ->where('quantity', '>', 0)
                ->count(),
            'out_of_stock' => (int) DB::table('inventory')
                ->where('quantity', '<=', 0)
                ->count(),
        ];

        $purchaseQuery = DB::table('purchases')
            ->when($from, fn ($q) => $q->whereDate('purchase_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('purchase_date', '<=', $to));

        $purchaseCost = (float) DB::table('purchase_items')
            ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->when($from, fn ($q) => $q->whereDate('purchases.purchase_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('purchases.purchase_date', '<=', $to))
            ->sum('purchase_items.subtotal');

        $itemsPurchased = (int) DB::table('purchase_items')
            ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->when($from, fn ($q) => $q->whereDate('purchases.purchase_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('purchases.purchase_date', '<=', $to))
            ->sum('purchase_items.quantity');

        $purchases = [
            'total_purchases' => (int) $purchaseQuery->count(),
            'purchase_cost' => $purchaseCost,
            'items_purchased' => $itemsPurchased,
        ];

        $lowStockRows = DB::table('inventory')
            ->join('product_variants', 'product_variants.id', '=', 'inventory.product_variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->whereColumn('inventory.quantity', '<=', 'inventory.low_stock_threshold')
            ->where('inventory.quantity', '>', 0)
            ->select([
                'products.name',
                'product_variants.size',
                'product_variants.color',
                'inventory.quantity',
                'inventory.low_stock_threshold',
            ])
            ->orderBy('inventory.quantity')
            ->limit(10)
            ->get();

        $outOfStockRows = DB::table('inventory')
            ->join('product_variants', 'product_variants.id', '=', 'inventory.product_variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->where('inventory.quantity', '<=', 0)
            ->select([
                'products.name',
                'product_variants.size',
                'product_variants.color',
                'inventory.quantity',
            ])
            ->orderBy('products.name')
            ->limit(10)
            ->get();

        $movementRows = DB::table('inventory_movements')
            ->join('product_variants', 'product_variants.id', '=', 'inventory_movements.product_variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->selectRaw('
                products.name,
                SUM(CASE WHEN inventory_movements.type = "out" THEN inventory_movements.quantity ELSE 0 END) AS sold_quantity,
                SUM(CASE WHEN inventory_movements.type = "in" THEN inventory_movements.quantity ELSE 0 END) AS in_quantity
            ')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('sold_quantity')
            ->get();

        $fastMoving = $movementRows->filter(fn ($row) => (int) $row->sold_quantity > 0)->take(10)->values();
        $slowMoving = $movementRows->filter(fn ($row) => (int) $row->sold_quantity === 0)->take(10)->values();

        return [
            'stats' => $stats,
            'profit' => $profit,
            'products' => $products,
            'bestProducts' => $products->sortByDesc('quantity')->take(8)->values(),
            'lowestProducts' => $products->sortBy('quantity')->take(8)->values(),
            'categories' => $categories,
            'genders' => $genders,
            'payments' => $payments,
            'paymentMethods' => $paymentMethods,
            'dailySales' => $dailySales,
            'weeklySales' => $weeklySales,
            'monthlySales' => $monthlySales,
            'inventory' => $inventory,
            'lowStockRows' => $lowStockRows,
            'outOfStockRows' => $outOfStockRows,
            'fastMoving' => $fastMoving,
            'slowMoving' => $slowMoving,
            'purchases' => $purchases,
            'dateFrom' => $from,
            'dateTo' => $to,
        ];
    }
}
