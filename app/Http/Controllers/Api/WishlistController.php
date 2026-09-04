<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /**
     * Display the current user's wishlist.
     */
    public function index(Request $request)
    {
        $wishlists = Wishlist::with([
            'product.category',
            'product.variants',
        ])
        ->where('user_id', $request->user()->id)
        ->latest()
        ->get();

        return response()->json([
            'status' => 'success',
            'data' => $wishlists,
        ]);
    }

    /**
     * Add a product to the current user's wishlist.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $wishlist = Wishlist::firstOrCreate([
            'user_id' => $request->user()->id,
            'product_id' => $validated['product_id'],
        ]);

        $wishlist->load([
            'product.category',
            'product.variants',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Product added to wishlist',
            'data' => $wishlist,
        ], 201);
    }

    /**
     * Display one wishlist item.
     */
    public function show(Request $request, string $id)
    {
        $wishlist = Wishlist::with([
            'product.category',
            'product.variants',
        ])
        ->where('user_id', $request->user()->id)
        ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $wishlist,
        ]);
    }

    /**
     * Remove a product from the current user's wishlist.
     */
    public function destroy(Request $request, string $productId)
    {
        $wishlist = Wishlist::where('user_id', $request->user()->id)
            ->where('product_id', $productId)
            ->firstOrFail();

        $wishlist->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Product removed from wishlist',
        ]);
    }
}