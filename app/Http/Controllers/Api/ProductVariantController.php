<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $variants = ProductVariant::with('product')->get();

        return response()->json([
            'status' => 'success',
            'data' => $variants,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'sku' => 'required|string|max:255|unique:product_variants,sku',
            'barcode' => 'nullable|string|max:255|unique:product_variants,barcode',
            'size' => 'required|string|max:100',
            'color' => 'required|string|max:100',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'status' => 'boolean',
        ]);

        $variant = ProductVariant::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Product variant created successfully',
            'data' => $variant->load('product'),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $variant = ProductVariant::with('product')->find($id);

        if (!$variant) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product variant not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $variant,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $variant = ProductVariant::find($id);

        if (!$variant) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product variant not found',
            ], 404);
        }

        $validated = $request->validate([
            'product_id' => 'sometimes|required|exists:products,id',
            'sku' => 'sometimes|required|string|max:255|unique:product_variants,sku,' . $id,
            'barcode' => 'nullable|string|max:255|unique:product_variants,barcode,' . $id,
            'size' => 'sometimes|required|string|max:100',
            'color' => 'sometimes|required|string|max:100',
            'cost_price' => 'sometimes|required|numeric|min:0',
            'selling_price' => 'sometimes|required|numeric|min:0',
            'status' => 'sometimes|boolean',
        ]);

        $variant->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Product variant updated successfully',
            'data' => $variant->fresh()->load('product'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $variant = ProductVariant::find($id);

        if (!$variant) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product variant not found',
            ], 404);
        }

        $variant->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Product variant deleted successfully',
        ]);
    }
}