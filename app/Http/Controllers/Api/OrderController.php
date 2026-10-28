<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Services\TelegramPaymentNotifier;

class OrderController extends Controller
{
    /**
     * ================================================================
     * DISPLAY ORDERS OF CURRENT USER
     * ================================================================
     *
     * API:
     * GET /api/orders
     *
     * REAL DATA ONLY
     */
    public function index(Request $request)
    {
        // ============================================================
        // API / DATABASE:
        // Get orders belonging to the authenticated customer.
        // ============================================================

        $orders = Order::where(
                'user_id',
                $request->user()->id
            )
            ->with([
                'items.productVariant.product',
                'payments',
            ])
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $orders,
        ]);
    }


    /**
     * ================================================================
     * DISPLAY ONE ORDER
     * ================================================================
     *
     * API:
     * GET /api/orders/{order}
     *
     * REAL DATA ONLY
     */
    public function show(
        Request $request,
        Order $order
    ) {
        // ============================================================
        // SECURITY:
        // Customer can only view their own order.
        // ============================================================

        if (
            $order->user_id !==
            $request->user()->id
        ) {
            return response()->json([
                'status' => 'error',
                'message' =>
                    'Unauthorized to view this order.',
            ], 403);
        }

        // ============================================================
        // LOAD REAL ORDER DATA
        // ============================================================

        $order->load([
            'items.productVariant.product',
            'payments',
            'user',
        ]);

        // ==================================================================
        // FIX: REAL ORDER TIMELINE FOR FLUTTER
        //
        // Actual dates come from orders.confirmed_at / processing_at /
        // shipped_at / delivered_at. Expected dates are calculated from
        // the existing admin/staff settings table.
        // No fake actual dates are created.
        // ==================================================================
        $order->setAttribute(
            'timeline',
            $this->buildOrderTimeline($order)
        );

        $order->setAttribute(
            'timeline_settings',
            $this->getTimelineSettings()
        );

        return response()->json([
            'status' => 'success',
            'data' => $order,
        ]);
    }


    /**
     * ================================================================
     * CREATE ORDER FROM CUSTOMER CART
     * ================================================================
     *
     * API:
     * POST /api/orders
     *
     * ================================================================
     *
     * CUSTOMER FLOW:
     *
     * Flutter Customer
     *       ↓
     * Add product to cart
     *       ↓
     * Checkout
     *       ↓
     * POST /api/orders
     *       ↓
     * Check real inventory
     *       ↓
     * Create real order
     *       ↓
     * DEDUCT REAL STOCK
     *       ↓
     * Create inventory movement OUT
     *       ↓
     * Create order items
     *       ↓
     * Clear cart
     *
     * ================================================================
     *
     * IMPORTANT FIX:
     *
     * Previously:
     *
     * Customer buys Qty 1
     * Inventory = 10
     * Order created
     * Inventory remained = 10
     *
     * NOW:
     *
     * Customer buys Qty 1
     * Inventory = 10
     * Order created
     * Inventory becomes = 9
     *
     * ================================================================
     */
    public function store(
        Request $request,
        TelegramPaymentNotifier $telegram
    ) {
        // ============================================================
        // VALIDATE CUSTOMER INPUT
        // ============================================================

        $validated = $request->validate([
            'shipping_address' => [
                'required',
                'string',
                'max:1000',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        // ============================================================
        // REAL AUTHENTICATED CUSTOMER
        // ============================================================

        $user = $request->user();

        // ============================================================
        // LOAD CUSTOMER'S REAL CART
        // ============================================================

        $cart = Cart::with(
                'items.productVariant'
            )
            ->where(
                'user_id',
                $user->id
            )
            ->first();

        // ============================================================
        // REAL DATA ONLY:
        // Do not create order if cart is empty.
        // ============================================================

        if (
            ! $cart ||
            $cart->items->isEmpty()
        ) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cart is empty',
            ], 422);
        }

        // ============================================================
        // DATABASE TRANSACTION
        //
        // Order creation + inventory deduction + order items +
        // cart clearing must succeed together.
        //
        // If something fails:
        // the database transaction will rollback.
        // ============================================================

        $order = DB::transaction(
            function () use (
                $user,
                $validated
            ) {
                // ====================================================
                // RELOAD REAL CART INSIDE TRANSACTION
                // ====================================================

                $cart = Cart::with(
                        'items.productVariant'
                    )
                    ->where(
                        'user_id',
                        $user->id
                    )
                    ->lockForUpdate()
                    ->first();

                // ====================================================
                // REAL CART CHECK
                // ====================================================

                if (
                    ! $cart ||
                    $cart->items->isEmpty()
                ) {
                    abort(
                        422,
                        'Cart is empty'
                    );
                }

                // ====================================================
                // CALCULATE REAL SUBTOTAL
                //
                // Uses the existing cart item subtotal.
                // No fake price.
                // ====================================================

                $subtotal = $cart->items->sum(
                    function ($item) {
                        return $item->subtotal;
                    }
                );

                // ====================================================
                // DISCOUNT
                //
                // Current order flow does not calculate a customer
                // discount here.
                //
                // Keep the existing real value as 0.
                // ====================================================

                $discount = 0;

                $total =
                    $subtotal -
                    $discount;


                // ====================================================
                // ====================================================
                // FIX #1:
                // CHECK REAL INVENTORY BEFORE CREATING ORDER
                // ====================================================
                //
                // Example:
                //
                // Product Variant:
                // WILLOW-TOPS-002
                //
                // Current Stock:
                // 10
                //
                // Customer buys:
                // 1
                //
                // 10 >= 1
                // → Customer can buy
                //
                // If:
                //
                // Stock = 0
                // Customer buys = 1
                //
                // → Order is rejected.
                //
                // ====================================================

                foreach (
                    $cart->items as $cartItem
                ) {
                    // =================================================
                    // REAL QUANTITY
                    // =================================================

                    $quantity =
                        (int) $cartItem->quantity;

                    // =================================================
                    // Quantity must be greater than zero.
                    // =================================================

                    if ($quantity <= 0) {
                        abort(
                            422,
                            'Invalid product quantity in cart.'
                        );
                    }

                    // =================================================
                    // FIX:
                    // Get the REAL inventory row for this variant.
                    //
                    // Existing database:
                    //
                    // inventory.product_variant_id
                    // inventory.quantity
                    //
                    // =================================================

                    $inventory = Inventory::query()
                        ->where(
                            'product_variant_id',
                            $cartItem->product_variant_id
                        )
                        ->lockForUpdate()
                        ->first();

                    // =================================================
                    // Inventory record must exist.
                    // =================================================

                    if (! $inventory) {
                        abort(
                            422,
                            'Inventory is not set for product variant ID: '
                            . $cartItem->product_variant_id
                        );
                    }

                    // =================================================
                    // FIX:
                    // Prevent customer from buying more than stock.
                    // =================================================

                    if (
                        (int) $inventory->quantity <
                        $quantity
                    ) {
                        abort(
                            422,
                            'Insufficient stock for product variant ID: '
                            . $cartItem->product_variant_id
                        );
                    }
                }


                // ====================================================
                // CREATE REAL ORDER
                // ====================================================
                //
                // Existing orders table:
                //
                // user_id
                // status
                // subtotal
                // discount
                // total
                // payment_status
                // shipping_address
                // notes
                //
                // No fake order number.
                // ====================================================

                $order = Order::create([
                    'user_id' =>
                        $user->id,

                    'status' =>
                        'pending',

                    'subtotal' =>
                        $subtotal,

                    'discount' =>
                        $discount,

                    'total' =>
                        $total,

                    'payment_status' =>
                        'pending',

                    'shipping_address' =>
                        $validated[
                            'shipping_address'
                        ],

                    'notes' =>
                        $validated[
                            'notes'
                        ] ?? null,
                ]);


                // ====================================================
                // ====================================================
                // FIX #2:
                // DEDUCT REAL INVENTORY AFTER ORDER CREATION
                // ====================================================
                //
                // This is the MAIN FIX.
                //
                // Customer buys:
                //
                // Quantity = 1
                //
                // Inventory:
                //
                // 10 - 1 = 9
                //
                // ====================================================
                //
                // IMPORTANT:
                //
                // We deduct stock here only.
                //
                // We DO NOT deduct stock again when:
                //
                // pending
                // confirmed
                // processing
                // shipped
                // delivered
                //
                // This prevents double deduction.
                // ====================================================

                foreach (
                    $cart->items as $cartItem
                ) {
                    $quantity =
                        (int) $cartItem->quantity;

                    // =================================================
                    // Get and lock the REAL inventory row.
                    // =================================================

                    $inventory = Inventory::query()
                        ->where(
                            'product_variant_id',
                            $cartItem->product_variant_id
                        )
                        ->lockForUpdate()
                        ->first();

                    // =================================================
                    // Safety check.
                    // =================================================

                    if (! $inventory) {
                        abort(
                            422,
                            'Inventory is not set for product variant ID: '
                            . $cartItem->product_variant_id
                        );
                    }

                    // =================================================
                    // Safety stock check again before deduction.
                    // =================================================

                    if (
                        (int) $inventory->quantity <
                        $quantity
                    ) {
                        abort(
                            422,
                            'Insufficient stock for product variant ID: '
                            . $cartItem->product_variant_id
                        );
                    }

                    // =================================================
                    // =================================================
                    // MAIN STOCK FIX
                    // =================================================
                    //
                    // REAL DATABASE:
                    //
                    // inventory.quantity
                    //
                    // Example:
                    //
                    // Before = 10
                    // Buy    = 1
                    // After  = 9
                    //
                    // =================================================

                    $inventory->quantity -=
                        $quantity;

                    $inventory->save();


                    // =================================================
                    // =================================================
                    // FIX #3:
                    // CREATE REAL INVENTORY MOVEMENT
                    // =================================================
                    //
                    // This records that the stock went OUT because
                    // of an ONLINE CUSTOMER ORDER.
                    //
                    // Existing InventoryMovement fields:
                    //
                    // product_variant_id
                    // type
                    // quantity
                    // reference_type
                    // reference_id
                    // note
                    //
                    // =================================================

                    InventoryMovement::create([
                        'product_variant_id' =>
                            $cartItem->product_variant_id,

                        'type' =>
                            'out',

                        'quantity' =>
                            $quantity,

                        'reference_type' =>
                            'order',

                        'reference_id' =>
                            $order->id,

                        'note' =>
                            'Stock sold from customer online order',
                    ]);
                }


                // ====================================================
                // CREATE REAL ORDER ITEMS
                // ====================================================

                foreach (
                    $cart->items as $cartItem
                ) {
                    $order->items()->create([
                        'product_variant_id' =>
                            $cartItem->product_variant_id,

                        'quantity' =>
                            $cartItem->quantity,

                        'unit_price' =>
                            $cartItem->unit_price,

                        'subtotal' =>
                            $cartItem->subtotal,
                    ]);
                }


                // ====================================================
                // CLEAR CUSTOMER'S REAL CART
                // ====================================================
                //
                // Cart is cleared only after:
                //
                // 1. Inventory checked
                // 2. Order created
                // 3. Stock deducted
                // 4. Inventory movement created
                // 5. Order items created
                //
                // If something fails, DB transaction rolls back.
                // ====================================================

                $cart->items()->delete();


                // ====================================================
                // RETURN REAL ORDER
                // ====================================================

                return $order;
            }
        );

        // ============================================================
        // ADMIN DATABASE NOTIFICATION: NEW CUSTOMER ORDER
        //
        // Run only AFTER the order transaction has committed.
        // If notification creation fails, do not undo the real order.
        // Uses the existing Laravel database notifications table.
        // ============================================================
        try {
            $adminUsers = User::query()
                ->whereHas('role', function ($query) {
                    $query->whereIn('name', ['Admin', 'Staff']);
                })
                ->get();

            foreach ($adminUsers as $adminUser) {
                $adminUser->notifications()->create([
                    'id' => (string) Str::uuid(),
                    'type' => 'order',
                    'data' => [
                        'category' => 'order',
                        'type' => 'order',
                        'title' => 'New Order Received',
                        'message' => 'A customer placed Order #' . $order->id
                            . ' for $' . number_format((float) $order->total, 2),
                        'order_id' => $order->id,
                        'url' => route('admin.orders.show', ['order' => $order->id]),
                    ],
                ]);
            }
            } catch (\Throwable $e) {     
               Log::warning('Database new-order notification failed.', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);
        }

        // ============================================================
        // TELEGRAM: NEW ORDER NOTIFICATION
        // Send only after the database transaction has committed.
        // Telegram failures must not undo a successfully created order.
        // ============================================================
        try {
            $telegram->newOrder($order);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning(
                'Telegram new-order notification failed.',
                [
                    'order_id' => $order->id,
                    'message' => $e->getMessage(),
                ]
            );
        }

        // ============================================================
        // RETURN REAL CREATED ORDER
        // ============================================================

        return response()->json([
            'status' =>
                'success',

            'message' =>
                'Order created successfully',

            'data' =>
                $order->load([
                    'items.productVariant.product.category',
                    'payments',
                ]),
        ], 201);
    }


    /**
     * ================================================================
     * UPDATE ORDER STATUS
     * ================================================================
     *
     * API:
     * PUT/PATCH /api/orders/{order}/status
     *
     * ================================================================
     *
     * IMPORTANT:
     *
     * Inventory is ALREADY deducted when customer creates the order.
     *
     * Therefore this method ONLY changes:
     *
     * orders.status
     *
     * It does NOT deduct inventory.
     *
     * ================================================================
     */
    public function updateStatus(
        Request $request,
        Order $order
    ) {
        // ============================================================
        // VALIDATE STATUS
        // ============================================================

        $validated = $request->validate([
            'status' => [
                'required',
                'string',
                'in:pending,confirmed,processing,shipped,delivered,cancelled',
            ],
        ]);

        // ============================================================
        // GET REAL AUTHENTICATED USER
        // ============================================================

        $user = $request->user();

        // ============================================================
        // LOAD REAL ROLE
        // ============================================================

        $user->loadMissing('role');

        $roleName =
            $user->role?->name;

        // ============================================================
        // SECURITY:
        // Only Admin or Staff can update order status.
        // ============================================================

        if (
            ! in_array(
                $roleName,
                ['Admin', 'Staff'],
                true
            )
        ) {
            return response()->json([
                'status' => 'error',
                'message' =>
                    'You do not have permission to update order status.',
            ], 403);
        }

        // ============================================================
        // FIX: SAVE REAL STATUS TRANSITION TIMESTAMP
        //
        // We only write the timestamp the first time a status is reached.
        // This preserves the real historical transition date.
        //
        // DO NOT TOUCH INVENTORY HERE.
        // Inventory is deducted when the order is created.
        // ============================================================

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
            ! $order->{$timestampColumn}
        ) {
            $updates[$timestampColumn] = now();
        }

        $order->update($updates);

        // ============================================================
        // LOAD REAL UPDATED ORDER
        // ============================================================

        $order->load([
            'items.productVariant.product',
            'payments',
        ]);

        // ============================================================
        // RETURN REAL UPDATED ORDER
        // ============================================================

        // FIX: Return the updated timeline to Flutter immediately.
        $order->setAttribute(
            'timeline',
            $this->buildOrderTimeline($order)
        );

        $order->setAttribute(
            'timeline_settings',
            $this->getTimelineSettings()
        );

        return response()->json([
            'status' =>
                'success',

            'message' =>
                'Order status updated successfully.',

            'data' =>
                $order,
        ]);
    }


    // ========================================================================
    // FIX: TIMELINE SETTINGS
    //
    // Timeline duration settings are stored in the existing settings table.
    // The customer app reads the Admin/Staff settings for delivery estimates.
    // ========================================================================

    private function getTimelineSettings(): array
    {
        $setting = Setting::query()
            ->whereHas('user.role', function ($query) {
                $query->whereIn('name', ['Admin', 'Staff']);
            })
            ->latest('id')
            ->first();

        return [
            'processing_days' => (int) ($setting?->processing_days ?? 1),
            'shipping_days' => (int) ($setting?->shipping_days ?? 2),
            'delivery_days' => (int) ($setting?->delivery_days ?? 3),
        ];
    }


    // ========================================================================
    // FIX: BUILD REAL + EXPECTED ORDER TIMELINE
    //
    // Actual stages use the real timestamp stored on the order.
    // Future stages use the configured duration from settings.
    // ========================================================================

    private function buildOrderTimeline(Order $order): array
    {
        $settings = $this->getTimelineSettings();

        // New *_at columns are not automatically cast by Eloquent.
        // Parse them explicitly so timeline calculations use real dates.
        $confirmedAt = $order->confirmed_at
            ? Carbon::parse($order->confirmed_at)
            : null;

        $processingAt = $order->processing_at
            ? Carbon::parse($order->processing_at)
            : null;

        $shippedAt = $order->shipped_at
            ? Carbon::parse($order->shipped_at)
            : null;

        $deliveredAt = $order->delivered_at
            ? Carbon::parse($order->delivered_at)
            : null;

        $processingExpected = null;
        $shippingExpected = null;
        $deliveryExpected = null;

        // FIX:
        // If an older order was created before the new *_at columns were
        // introduced, we still calculate EXPECTED dates from the real
        // order created_at value.
        //
        // This does NOT turn created_at into a fake confirmed_at.
        // Actual dates remain actual only when the database contains them.
        $expectedBase = $confirmedAt
            ?? $order->created_at;

        if ($expectedBase) {
            $processingExpected = $expectedBase->copy()->addDays(
                $settings['processing_days']
            );
        }

        if ($processingAt) {
            $shippingExpected = $processingAt->copy()->addDays(
                $settings['shipping_days']
            );
        } elseif ($processingExpected) {
            $shippingExpected = $processingExpected->copy()->addDays(
                $settings['shipping_days']
            );
        }

        if ($shippedAt) {
            $deliveryExpected = $shippedAt->copy()->addDays(
                $settings['delivery_days']
            );
        } elseif ($shippingExpected) {
            $deliveryExpected = $shippingExpected->copy()->addDays(
                $settings['delivery_days']
            );
        }

        return [
            'confirmed' => [
                'label' => 'Confirmed',
                'date' => $confirmedAt?->toIso8601String(),
                'actual' => $confirmedAt !== null,
            ],

            'processing' => [
                'label' => 'Processing',
                'date' => ($processingAt ?? $processingExpected)?->toIso8601String(),
                'actual' => $processingAt !== null,
            ],

            'shipped' => [
                'label' => 'Shipped',
                'date' => ($shippedAt ?? $shippingExpected)?->toIso8601String(),
                'actual' => $shippedAt !== null,
            ],

            'delivered' => [
                'label' => 'Delivered',
                'date' => ($deliveredAt ?? $deliveryExpected)?->toIso8601String(),
                'actual' => $deliveredAt !== null,
            ],
        ];
    }
}