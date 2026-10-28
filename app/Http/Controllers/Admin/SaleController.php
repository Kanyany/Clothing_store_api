<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SaleController extends Controller
{
    /**
     * ================================================================
     * SALE
     * ================================================================
     *
     * REAL DATA ONLY
     *
     * This page reads real data from:
     * 1. sales
     * 2. orders
     * 3. users
     * 4. order_items
     * 5. product_variants
     * 6. products
     * 7. payments
     *
     * IMPORTANT:
     * - No fake/demo/sample data.
     * - sales and orders remain separate database tables.
     * - Both are prepared for ONE Sale admin page.
     */

    /**
     * ================================================================
     * INDEX
     * ================================================================
     *
     * Show:
     * - Manual sales
     * - Real online customer orders
     * - Real customers
     * - Real product variants
     *
     * View:
     * resources/views/admin/sale/index.blade.php
     */
    /**
     * ================================================================
     * SALE INDEX
     * ================================================================
     *
     * ONLINE CLOTHING STORE FLOW
     *
     * Customer App
     *   -> Product
     *   -> Cart
     *   -> Checkout
     *   -> Order
     *   -> Payment
     *   -> Order Status
     *   -> Sale
     *
     * REAL DATABASE ONLY.
     *
     * The Sale page reads online customer orders from:
     * orders
     * order_items
     * product_variants
     * products
     * order_payments
     * users
     *
     * NOTE:
     * orders.id is the real order identifier.
     * There is no order_number column, so no order number is invented.
     */
    public function index(Request $request)
    {
        // ============================================================
        // REAL ONLINE ORDERS
        // ============================================================

        $ordersQuery = Order::query()
            ->with([
                'user',
                'items.productVariant.product',
                'payments',
            ])
            ->latest('created_at')
            ->latest('id');

        // ============================================================
        // SEARCH
        // Search real order/customer/product/payment data.
        // ============================================================

        if ($request->filled('search')) {

            $search = trim((string) $request->input('search'));

            if ($search !== '') {

                $like = '%' . addcslashes($search, '%_\\') . '%';

                $ordersQuery->where(function ($q) use ($like, $search) {

                    // REAL orders.id
                    if (is_numeric($search)) {

                        $q->where(
                            'orders.id',
                            (int) $search
                        );
                    }

                    // REAL customer data
                    $q->orWhereHas(
                        'user',
                        function ($userQuery) use ($like) {

                            $userQuery
                                ->where('users.name', 'like', $like)
                                ->orWhere('users.email', 'like', $like);
                        }
                    );

                    // REAL order fields
                    $q->orWhere('orders.status', 'like', $like);
                    $q->orWhere('orders.payment_status', 'like', $like);
                    $q->orWhere('orders.shipping_address', 'like', $like);
                    $q->orWhere('orders.notes', 'like', $like);
                    $q->orWhere('orders.total', 'like', $like);

                    // REAL payment fields
                    $q->orWhereHas(
                        'payments',
                        function ($paymentQuery) use ($like) {

                            $paymentQuery
                                ->where('payment_method', 'like', $like)
                                ->orWhere('payment_provider', 'like', $like)
                                ->orWhere('reference_number', 'like', $like);
                        }
                    );

                    // REAL product fields
                    $q->orWhereHas(
                        'items.productVariant.product',
                        function ($productQuery) use ($like) {

                            $productQuery
                                ->where('products.name', 'like', $like)
                                ->orWhere('products.description', 'like', $like)
                                ->orWhere('products.gender', 'like', $like)
                                ->orWhere('products.brand', 'like', $like);
                        }
                    );

                    // REAL variant fields
                    $q->orWhereHas(
                        'items.productVariant',
                        function ($variantQuery) use ($like) {

                            $variantQuery
                                ->where('product_variants.sku', 'like', $like)
                                ->orWhere('product_variants.barcode', 'like', $like)
                                ->orWhere('product_variants.size', 'like', $like)
                                ->orWhere('product_variants.color', 'like', $like);
                        }
                    );
                });
            }
        }

        // ============================================================
        // STATUS FILTER
        // ============================================================

        if ($request->filled('status')) {

            $allowedStatuses = [
                'pending',
                'confirmed',
                'processing',
                'shipped',
                'delivered',
                'cancelled',
            ];

            if (in_array($request->status, $allowedStatuses, true)) {

                $ordersQuery->where(
                    'orders.status',
                    $request->status
                );
            }
        }

        // ============================================================
        // PAYMENT FILTER
        // ============================================================

        if ($request->filled('payment_method')) {

            $ordersQuery->whereHas(
                'payments',
                function ($paymentQuery) use ($request) {

                    $paymentQuery->where(
                        'payment_method',
                        $request->payment_method
                    );
                }
            );
        }

        // ============================================================
        // DATE FILTER
        // ============================================================

        if ($request->filled('date_from')) {

            $ordersQuery->whereDate(
                'orders.created_at',
                '>=',
                $request->date_from
            );
        }

        if ($request->filled('date_to')) {

            $ordersQuery->whereDate(
                'orders.created_at',
                '<=',
                $request->date_to
            );
        }

        // ============================================================
        // SORT
        // ============================================================

        switch ($request->get('sort', 'latest')) {

            case 'oldest':

                $ordersQuery
                    ->orderBy('orders.created_at')
                    ->orderBy('orders.id');

                break;

            case 'highest':

                $ordersQuery
                    ->orderByDesc('orders.total')
                    ->orderByDesc('orders.id');

                break;

            case 'lowest':

                $ordersQuery
                    ->orderBy('orders.total')
                    ->orderBy('orders.id');

                break;

            default:

                $ordersQuery
                    ->orderByDesc('orders.created_at')
                    ->orderByDesc('orders.id');

                break;
        }

        // ============================================================
        // PAGINATION
        // ============================================================

        $orders = $ordersQuery
            ->paginate(
                15,
                ['*'],
                'orders_page'
            )
            ->withQueryString();

        // ============================================================
        // REAL SALE STATISTICS
        // ============================================================
        //
        // IMPORTANT:
        // Do NOT use Sale::query() here.
        //
        // Online customer purchases are stored in orders.
        //
        // Total Revenue:
        //   paid + not cancelled
        //
        // Paid Orders:
        //   payment_status = paid
        //   and status != cancelled
        //
        // Completed Sales:
        //   status = delivered OR completed
        //
        // Cancelled Orders:
        //   status = cancelled
        //
        // Date filters are applied to the statistics too.
        // ============================================================

        $statsQuery = Order::query();

        if ($request->filled('date_from')) {

            $statsQuery->whereDate(
                'orders.created_at',
                '>=',
                $request->date_from
            );
        }

        if ($request->filled('date_to')) {

            $statsQuery->whereDate(
                'orders.created_at',
                '<=',
                $request->date_to
            );
        }

        $completedStatuses = [
            'delivered',
            'completed',
        ];

        $stats = [

            // ========================================================
            // REAL TOTAL REVENUE
            // ========================================================

            'total_sales' => (float) (
                clone $statsQuery
            )
                ->whereRaw(
                    "LOWER(TRIM(COALESCE(payment_status, ''))) = ?",
                    ['paid']
                )
                ->whereRaw(
                    "LOWER(TRIM(COALESCE(status, ''))) != ?",
                    ['cancelled']
                )
                ->sum('orders.total'),

            // ========================================================
            // REAL PAID ORDERS
            // ========================================================

            'paid_orders' => (int) (
                clone $statsQuery
            )
                ->whereRaw(
                    "LOWER(TRIM(COALESCE(payment_status, ''))) = ?",
                    ['paid']
                )
                ->whereRaw(
                    "LOWER(TRIM(COALESCE(status, ''))) != ?",
                    ['cancelled']
                )
                ->count(),

            // ========================================================
            // REAL COMPLETED SALES
            // ========================================================

            'completed' => (int) (
                clone $statsQuery
            )
                ->whereIn(
                    DB::raw("LOWER(TRIM(COALESCE(status, '')))"),
                    $completedStatuses
                )
                ->count(),

            // ========================================================
            // REAL CANCELLED ORDERS
            // ========================================================

            'cancelled' => (int) (
                clone $statsQuery
            )
                ->whereRaw(
                    "LOWER(TRIM(COALESCE(status, ''))) = ?",
                    ['cancelled']
                )
                ->count(),
        ];

        // ============================================================
        // REAL PAYMENT METHODS
        // ============================================================
        //
        // The filter is populated from the actual order_payments table.
        // No Cash/Card/QR demo options are inserted.
        // ============================================================

        $paymentMethods = \App\Models\OrderPayment::query()
            ->whereNotNull('payment_method')
            ->where('payment_method', '!=', '')
            ->distinct()
            ->orderBy('payment_method')
            ->pluck('payment_method')
            ->values();

        // ============================================================
        // RETURN SALE PAGE
        // ============================================================

        return view(
            'admin.sale.index',
            compact(
                'orders',
                'stats',
                'paymentMethods'
            )
        );
    }


    /**
     * ================================================================
     * UPDATE ONLINE CUSTOMER ORDER STATUS
     * ================================================================
     *
     * REAL ONLINE STORE FLOW:
     * Pending -> Confirmed -> Processing -> Shipped -> Delivered
     *
     * This updates the real orders.status column.
     * No new table, migration, or fake data.
     */
    public function updateOrderStatus(
        Request $request,
        Order $order
    ) {
        // ============================================================
        // VALIDATE REAL ORDER STATUS
        // ============================================================
        $validated = $request->validate([
            'status' => [
                'required',
                'string',
                Rule::in([
                    'pending',
                    'confirmed',
                    'processing',
                    'shipped',
                    'delivered',
                    'cancelled',
                ]),
            ],
        ]);

        // ============================================================
        // FIX: SAVE REAL STATUS TRANSITION TIMESTAMP
        // ============================================================
        // These values come from the real moment the admin changes
        // the order status. We never invent historical dates and we
        // never overwrite an already recorded transition timestamp.
        $newStatus = $validated['status'];

        $updates = [
            'status' => $newStatus,
        ];

        $timestampColumn = match ($newStatus) {
            'confirmed' => 'confirmed_at',
            'processing' => 'processing_at',
            'shipped' => 'shipped_at',
            'delivered' => 'delivered_at',
            default => null,
        };

        if (
            $timestampColumn !== null &&
            $order->{$timestampColumn} === null
        ) {
            $updates[$timestampColumn] = now();
        }

        $order->update($updates);

        return redirect()
            ->route('admin.sale.index')
            ->with(
                'success',
                'Order #' . $order->id . ' status updated successfully.'
            );
    }


    public function store(Request $request)
    {
        $validated = $request->validate([

            'sale_date' => [
                'required',
                'date',
            ],

            'payment_method' => [
                'required',
                Rule::in([
                    'cash',
                    'card',
                    'qr',
                ]),
            ],

            'discount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'note' => [
                'nullable',
                'string',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.product_variant_id' => [
                'required',
                'integer',
                'exists:product_variants,id',
            ],

            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],

            'items.*.selling_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ]);

        DB::transaction(
            function () use ($validated) {

                // =====================================================
                // CALCULATE REAL SUBTOTAL
                // =====================================================

                $subtotal = 0;

                foreach (
                    $validated['items']
                    as $item
                ) {

                    $variant =
                        ProductVariant::query()
                            ->findOrFail(
                                $item['product_variant_id']
                            );

                    $subtotal +=
                        (int) $item['quantity']
                        *
                        (float) $variant->selling_price;
                }

                // =====================================================
                // REAL DISCOUNT
                // =====================================================

                $discount =
                    (float) (
                        $validated['discount']
                        ?? 0
                    );

                if ($discount > $subtotal) {

                    abort(
                        422,
                        'Discount cannot be greater than the subtotal.'
                    );
                }

                // =====================================================
                // CREATE REAL SALE
                // =====================================================

                $sale = Sale::create([

                    'invoice_number' =>
                        $this->generateInvoiceNumber(),

                    'sale_date' =>
                        $validated['sale_date'],

                    'subtotal' =>
                        $subtotal,

                    'discount' =>
                        $discount,

                    'total_amount' =>
                        $subtotal - $discount,

                    'payment_method' =>
                        $validated['payment_method'],

                    'status' =>
                        'completed',

                    'note' =>
                        $validated['note']
                        ?? null,
                ]);

                // =====================================================
                // CREATE SALE ITEMS + UPDATE INVENTORY
                // =====================================================

                foreach (
                    $validated['items']
                    as $item
                ) {

                    $variant =
                        ProductVariant::query()
                            ->with('product')
                            ->findOrFail(
                                $item['product_variant_id']
                            );

                    // =================================================
                    // LOCK REAL INVENTORY ROW
                    // =================================================

                    $inventory =
                        Inventory::query()
                            ->where(
                                'product_variant_id',
                                $variant->id
                            )
                            ->lockForUpdate()
                            ->first();

                    if (! $inventory) {

                        abort(
                            422,
                            'Inventory is not set for product variant: '
                            . $variant->product->name
                        );
                    }

                    // =================================================
                    // CHECK REAL STOCK
                    // =================================================

                    if (
                        $inventory->quantity
                        <
                        (int) $item['quantity']
                    ) {

                        abort(
                            422,
                            'Insufficient stock for product variant: '
                            . $variant->product->name
                        );
                    }

                    // =================================================
                    // REAL SELLING PRICE
                    // =================================================

                    $itemSellingPrice =
                        (float) $variant->selling_price;

                    $itemSubtotal =
                        (int) $item['quantity']
                        *
                        $itemSellingPrice;

                    // =================================================
                    // CREATE REAL SALE ITEM
                    // =================================================

                    $sale->items()->create([

                        'product_variant_id' =>
                            $item['product_variant_id'],

                        'quantity' =>
                            $item['quantity'],

                        'selling_price' =>
                            $itemSellingPrice,

                        'subtotal' =>
                            $itemSubtotal,
                    ]);

                    // =================================================
                    // REAL INVENTORY UPDATE
                    // =================================================

                    $inventory->quantity -=
                        (int) $item['quantity'];

                    $inventory->save();

                    // =================================================
                    // REAL INVENTORY MOVEMENT
                    // =================================================

                    InventoryMovement::create([

                        'product_variant_id' =>
                            $item['product_variant_id'],

                        'type' =>
                            'out',

                        'quantity' =>
                            $item['quantity'],

                        'reference_type' =>
                            'sale',

                        'reference_id' =>
                            $sale->id,

                        'note' =>
                            'Stock sold',
                    ]);
                }
            }
        );

        // ============================================================
        // FIX:
        // Redirect to Sale page.
        //
        // OLD:
        // admin.transactions.index
        //
        // NEW:
        // admin.sale.index
        // ============================================================

        return redirect()
            ->route('admin.sale.index')
            ->with(
                'success',
                'Sale created successfully.'
            );
    }


    /**
     * ================================================================
     * UPDATE MANUAL SALE
     * ================================================================
     */
    public function update(
        Request $request,
        Sale $sale
    ) {

        $validated = $request->validate([

            'sale_date' => [
                'required',
                'date',
            ],

            'payment_method' => [
                'required',
                Rule::in([
                    'cash',
                    'card',
                    'qr',
                ]),
            ],

            'discount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'status' => [
                'required',
                'in:completed,cancelled',
            ],

            'note' => [
                'nullable',
                'string',
            ],
        ]);

        DB::transaction(
            function () use (
                $validated,
                $sale
            ) {

                $sale->load('items');

                $oldStatus =
                    $sale->status;

                $newStatus =
                    $validated['status'];

                // =====================================================
                // COMPLETED -> CANCELLED
                // Return stock.
                // =====================================================

                if (
                    $oldStatus === 'completed'
                    &&
                    $newStatus === 'cancelled'
                ) {

                    foreach (
                        $sale->items
                        as $item
                    ) {

                        $inventory =
                            Inventory::query()
                                ->where(
                                    'product_variant_id',
                                    $item->product_variant_id
                                )
                                ->lockForUpdate()
                                ->first();

                        if (! $inventory) {

                            abort(
                                422,
                                'Inventory is not set for product variant ID: '
                                . $item->product_variant_id
                            );
                        }

                        $inventory->quantity +=
                            $item->quantity;

                        $inventory->save();

                        // =================================================
                        // REAL INVENTORY MOVEMENT
                        // =================================================

                        InventoryMovement::create([

                            'product_variant_id' =>
                                $item->product_variant_id,

                            'type' =>
                                'in',

                            'quantity' =>
                                $item->quantity,

                            'reference_type' =>
                                'sale',

                            'reference_id' =>
                                $sale->id,

                            'note' =>
                                'Stock returned from cancelled sale',
                        ]);
                    }
                }

                // =====================================================
                // CANCELLED -> COMPLETED
                // Deduct stock again.
                // =====================================================

                if (
                    $oldStatus === 'cancelled'
                    &&
                    $newStatus === 'completed'
                ) {

                    foreach (
                        $sale->items
                        as $item
                    ) {

                        $inventory =
                            Inventory::query()
                                ->where(
                                    'product_variant_id',
                                    $item->product_variant_id
                                )
                                ->lockForUpdate()
                                ->first();

                        if (! $inventory) {

                            abort(
                                422,
                                'Inventory is not set for product variant ID: '
                                . $item->product_variant_id
                            );
                        }

                        if (
                            $inventory->quantity
                            <
                            $item->quantity
                        ) {

                            abort(
                                422,
                                'Insufficient stock to complete this sale.'
                            );
                        }

                        $inventory->quantity -=
                            $item->quantity;

                        $inventory->save();

                        // =================================================
                        // REAL INVENTORY MOVEMENT
                        // =================================================

                        InventoryMovement::create([

                            'product_variant_id' =>
                                $item->product_variant_id,

                            'type' =>
                                'out',

                            'quantity' =>
                                $item->quantity,

                            'reference_type' =>
                                'sale',

                            'reference_id' =>
                                $sale->id,

                            'note' =>
                                'Stock sold after sale reactivation',
                        ]);
                    }
                }

                // =====================================================
                // REAL DISCOUNT
                // =====================================================

                $discount =
                    (float) (
                        $validated['discount']
                        ?? 0
                    );

                $subtotal =
                    (float) $sale->subtotal;

                if ($discount > $subtotal) {

                    abort(
                        422,
                        'Discount cannot be greater than the subtotal.'
                    );
                }

                // =====================================================
                // UPDATE REAL SALE
                // =====================================================

                $sale->update([

                    'sale_date' =>
                        $validated['sale_date'],

                    'payment_method' =>
                        $validated['payment_method'],

                    'discount' =>
                        $discount,

                    'total_amount' =>
                        $subtotal - $discount,

                    'status' =>
                        $newStatus,

                    'note' =>
                        $validated['note']
                        ?? null,
                ]);
            }
        );

        // ============================================================
        // FIX:
        // Redirect to Sale page.
        // ============================================================

        return redirect()
            ->route('admin.sale.index')
            ->with(
                'success',
                'Sale updated successfully.'
            );
    }


    /**
     * ================================================================
     * DELETE MANUAL SALE
     * ================================================================
     */
    public function destroy(Sale $sale)
    {
        // ============================================================
        // DO NOT DELETE SALE WITH PAYMENT RECORDS
        // ============================================================

        if ($sale->payments()->exists()) {

            return redirect()
                ->route('admin.sale.index')
                ->with(
                    'error',
                    'This sale cannot be deleted because payment records exist.'
                );
        }

        DB::transaction(
            function () use ($sale) {

                $sale->load('items');

                // =====================================================
                // RETURN INVENTORY FOR COMPLETED SALE
                // =====================================================

                if ($sale->status === 'completed') {

                    foreach (
                        $sale->items
                        as $item
                    ) {

                        $inventory =
                            Inventory::query()
                                ->where(
                                    'product_variant_id',
                                    $item->product_variant_id
                                )
                                ->lockForUpdate()
                                ->first();

                        if (! $inventory) {

                            abort(
                                422,
                                'Inventory is not set for product variant ID: '
                                . $item->product_variant_id
                            );
                        }

                        $inventory->quantity +=
                            $item->quantity;

                        $inventory->save();

                        // =================================================
                        // REAL INVENTORY MOVEMENT
                        // =================================================

                        InventoryMovement::create([

                            'product_variant_id' =>
                                $item->product_variant_id,

                            'type' =>
                                'in',

                            'quantity' =>
                                $item->quantity,

                            'reference_type' =>
                                'sale',

                            'reference_id' =>
                                $sale->id,

                            'note' =>
                                'Stock returned from deleted sale',
                        ]);
                    }
                }

                // =====================================================
                // DELETE REAL SALE ITEMS
                // =====================================================

                $sale->items()->delete();

                // =====================================================
                // DELETE REAL SALE
                // =====================================================

                $sale->delete();
            }
        );

        // ============================================================
        // FIX:
        // Redirect to Sale page.
        // ============================================================

        return redirect()
            ->route('admin.sale.index')
            ->with(
                'success',
                'Sale deleted successfully.'
            );
    }


    /**
     * ================================================================
     * GENERATE INVOICE NUMBER
     * ================================================================
     *
     * This is for MANUAL SALES only.
     *
     * Online orders use their real `orders.id`.
     * We do NOT create fake order_number.
     */
    private function generateInvoiceNumber(): string
    {
        $prefix =
            'INV-' .
            now()->format('Ymd') .
            '-';

        $lastInvoice =
            Sale::query()
                ->where(
                    'invoice_number',
                    'like',
                    $prefix . '%'
                )
                ->orderByDesc('id')
                ->value('invoice_number');

        $nextNumber = 1;

        if ($lastInvoice) {

            $nextNumber =
                (int) str_replace(
                    $prefix,
                    '',
                    $lastInvoice
                ) + 1;
        }

        return $prefix
            . str_pad(
                $nextNumber,
                4,
                '0',
                STR_PAD_LEFT
            );
    }
}