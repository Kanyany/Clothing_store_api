<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    /**
     * Display all inventory.
     */
    public function index()
    {
        $inventory = Inventory::with('productVariant.product')->get();

        return response()->json([
            'status' => 'success',
            'data' => $inventory,
        ]);
    }

    /**
     * Display inventory for one product variant.
     */
    public function show(string $id)
    {
        $inventory = Inventory::with('productVariant.product')->find($id);

        if (!$inventory) {
            return response()->json([
                'status' => 'error',
                'message' => 'Inventory not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $inventory,
        ]);
    }

    /**
     * Create inventory for a product variant.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id|unique:inventory,product_variant_id',
            'quantity' => 'nullable|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
        ]);

        $inventory = Inventory::create([
            'product_variant_id' => $validated['product_variant_id'],
            'quantity' => $validated['quantity'] ?? 0,
            'low_stock_threshold' => $validated['low_stock_threshold'] ?? 5,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Inventory created successfully',
            'data' => $inventory->load('productVariant.product'),
        ], 201);
    }

    /**
     * Update inventory settings.
     */
    public function update(Request $request, string $id)
    {
        $inventory = Inventory::find($id);

        if (!$inventory) {
            return response()->json([
                'status' => 'error',
                'message' => 'Inventory not found',
            ], 404);
        }

        $validated = $request->validate([
            'quantity' => 'sometimes|required|integer|min:0',
            'low_stock_threshold' => 'sometimes|required|integer|min:0',
        ]);

        $inventory->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Inventory updated successfully',
            'data' => $inventory->fresh()->load('productVariant.product'),
        ]);
    }

    /**
     * Adjust stock quantity.
     */
    public function adjust(Request $request, string $id)
    {
        $validated = $request->validate([
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|integer|min:1',
            'note' => 'nullable|string',
        ]);

        $inventory = Inventory::find($id);

        if (!$inventory) {
            return response()->json([
                'status' => 'error',
                'message' => 'Inventory not found',
            ], 404);
        }

        if ($validated['type'] === 'out' &&
            $inventory->quantity < $validated['quantity']) {

            return response()->json([
                'status' => 'error',
                'message' => 'Insufficient stock',
            ], 422);
        }

        DB::transaction(function () use ($inventory, $validated) {

            if ($validated['type'] === 'in') {
                $inventory->quantity += $validated['quantity'];
            }

            if ($validated['type'] === 'out') {
                $inventory->quantity -= $validated['quantity'];
            }

            if ($validated['type'] === 'adjustment') {
                $inventory->quantity = $validated['quantity'];
            }

            $inventory->save();

            InventoryMovement::create([
                'product_variant_id' => $inventory->product_variant_id,
                'type' => $validated['type'],
                'quantity' => $validated['quantity'],
                'reference_type' => 'manual',
                'reference_id' => null,
                'note' => $validated['note'] ?? null,
            ]);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Stock adjusted successfully',
            'data' => $inventory->fresh()->load('productVariant.product'),
        ]);
    }

    /**
     * Display inventory movement history.
     */
    public function movements(string $id)
    {
        $inventory = Inventory::find($id);

        if (!$inventory) {
            return response()->json([
                'status' => 'error',
                'message' => 'Inventory not found',
            ], 404);
        }

        $movements = InventoryMovement::where(
            'product_variant_id',
            $inventory->product_variant_id
        )
        ->latest()
        ->get();

        return response()->json([
            'status' => 'success',
            'data' => $movements,
        ]);
    }
}