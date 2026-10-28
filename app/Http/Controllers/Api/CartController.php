<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display the authenticated user's cart.
     */
    public function index(Request $request)
    {
        $cart = Cart::firstOrCreate([
            'user_id' => $request->user()->id,
        ]);

        $cart->load([
            'items.productVariant.product.category',
        ]);

        $total = $cart->items->sum('subtotal');

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $cart->id,
                'items' => $cart->items,
                'total' => number_format($total, 2, '.', ''),
            ],
        ]);
    }

    /**
     * Add a product variant to the cart.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $user = $request->user();

        $variant = ProductVariant::findOrFail(
            $validated['product_variant_id']
        );

        if (!$variant->status) {
            return response()->json([
                'status' => 'error',
                'message' => 'This product variant is unavailable.',
            ], 422);
        }

        $cart = Cart::firstOrCreate([
            'user_id' => $user->id,
        ]);

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_variant_id', $variant->id)
            ->first();

        if ($cartItem) {
            $cartItem->quantity += $validated['quantity'];
        } else {
            $cartItem = new CartItem();

            $cartItem->cart_id = $cart->id;
            $cartItem->product_variant_id = $variant->id;
            $cartItem->quantity = $validated['quantity'];
        }

        $cartItem->unit_price = $variant->selling_price;
        $cartItem->subtotal =
            $cartItem->quantity * $cartItem->unit_price;

        $cartItem->save();

        $cartItem->load([
            'productVariant.product.category',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Product added to cart successfully',
            'data' => $cartItem,
        ], 201);
    }

    /**
     * Update cart item quantity.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::where('user_id', $request->user()->id)
            ->first();

        if (!$cart) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cart not found.',
            ], 404);
        }

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('id', $id)
            ->first();

        if (!$cartItem) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cart item not found.',
            ], 404);
        }

        $cartItem->quantity = $validated['quantity'];
        $cartItem->subtotal =
            $cartItem->quantity * $cartItem->unit_price;

        $cartItem->save();

        $cartItem->load([
            'productVariant.product.category',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Cart quantity updated successfully',
            'data' => $cartItem,
        ]);
    }

    /**
     * Remove an item from the cart.
     */
    public function destroy(Request $request, $id)
    {
        $cart = Cart::where('user_id', $request->user()->id)
            ->first();

        if (!$cart) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cart not found.',
            ], 404);
        }

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('id', $id)
            ->first();

        if (!$cartItem) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cart item not found.',
            ], 404);
        }

        $cartItem->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Product removed from cart successfully',
        ]);
    }
}