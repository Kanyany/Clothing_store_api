<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
   public function index()
    {
        $products = Product::with([
            'category',
            'variants.inventory',
        ])->latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $products,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'gender' => 'required|in:male,female,unisex,kids',
            'brand' => 'nullable|string|max:255',
            'image' => 'nullable|string|max:255',
            'status' => 'boolean',
        ]);

        $product = Product::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Product created successfully',
            'data' => $product->load('category'),
        ], 201);
    }

   public function show(Product $product)
{
    return response()->json([
        'status' => 'success',
        'data' => $product->load([
            'category',
            'variants.inventory',
        ]),
    ]);
}

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id' => 'sometimes|required|exists:categories,id',
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'gender' => 'sometimes|required|in:male,female,unisex,kids',
            'brand' => 'nullable|string|max:255',
            'image' => 'nullable|string|max:255',
            'status' => 'boolean',
        ]);

        $product->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Product updated successfully',
            'data' => $product->fresh()->load('category'),
        ]);
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Product deleted successfully',
        ]);
    }
}