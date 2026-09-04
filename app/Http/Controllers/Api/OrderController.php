<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display orders of current user.
     */
    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
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
     * Display a specific order of current user.
     */
    public function show(Request $request, Order $order)
    {
        // Prevent user from viewing another user's order
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to view this order.',
            ], 403);
        }

        $order->load([
            'items.productVariant.product',
            'payments',
            'user',
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $order,
        ]);
    }


    /**
     * Create order from current user's cart.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'shipping_address' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();

        $cart = Cart::with('items.productVariant')
            ->where('user_id', $user->id)
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cart is empty',
            ], 422);
        }

        $order = DB::transaction(function () use (
            $cart,
            $user,
            $validated
        ) {

            $subtotal = $cart->items->sum(function ($item) {
                return $item->subtotal;
            });

            $discount = 0;

            $total = $subtotal - $discount;

            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'pending',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'payment_status' => 'pending',
                'shipping_address' =>
                    $validated['shipping_address'],
                'notes' =>
                    $validated['notes'] ?? null,
            ]);

            foreach ($cart->items as $cartItem) {

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

            // Clear cart after order creation
            $cart->items()->delete();

            return $order;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Order created successfully',
            'data' => $order->load([
                'items.productVariant.product.category',
                'payments',
            ]),
        ], 201);
    }
}