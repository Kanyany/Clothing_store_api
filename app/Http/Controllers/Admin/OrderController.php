<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // ========================================================================
    // ADMIN ORDERS
    // ========================================================================
    //
    // REAL DATA ONLY
    //
    // This controller reads the existing `orders` table.
    //
    // No fake orders.
    // No fake customers.
    // No fake prices.
    // No new database table.
    // ========================================================================


    /**
     * Display all customer orders.
     *
     * REAL DATA:
     * orders
     * users
     * order_items
     * product_variants
     * products
     * payments
     */
    public function index(Request $request)
    {
        // ================================================================
        // API / DATABASE:
        // Read real orders from the existing database.
        // ================================================================

        $query = Order::with([
            'user',
            'items.productVariant.product',
            'payments',
        ])->latest();

        // ================================================================
        // REAL SEARCH
        //
        // Admin can search by:
        // - customer name
        // - customer email
        // - order ID
        // ================================================================

        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where(function ($q) use ($search) {
                // Search real order ID.
                if (is_numeric($search)) {
                    $q->where('id', (int) $search);
                }

                // Search real customer.
                $q->orWhereHas('user', function ($userQuery) use ($search) {
                    $userQuery
                        ->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            });
        }

        // ================================================================
        // REAL STATUS FILTER
        // ================================================================

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->input('status')
            );
        }

        // ================================================================
        // Pagination
        // ================================================================

        $orders = $query
            ->paginate(15)
            ->withQueryString();

        return view(
            'admin.orders.index',
            compact('orders')
        );
    }


    /**
     * Display one real customer order.
     */
    public function show(Order $order)
    {
        // ================================================================
        // REAL DATA:
        // Load the complete order from database.
        // ================================================================

        $order->load([
            'user',
            'items.productVariant.product.category',
            'payments',
        ]);

        return view(
            'admin.orders.show',
            compact('order')
        );
    }


    /**
     * Update real order status.
     *
     * REAL DATABASE:
     * orders.status
     */
    public function updateStatus(
        Request $request,
        Order $order
    ) {
        // ================================================================
        // Validate status.
        // ================================================================

        $validated = $request->validate([
            'status' => [
                'required',
                'string',
                'in:pending,confirmed,processing,shipped,delivered,cancelled',
            ],
        ]);

        // ================================================================
        // REAL DATABASE UPDATE
        //
        // Only the existing `orders.status` column is changed.
        // ================================================================

        $order->update([
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('admin.orders.show', $order)
            ->with(
                'success',
                'Order status updated successfully.'
            );
    }
}