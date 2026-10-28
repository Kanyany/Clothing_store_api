<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\ProductVariant;
use App\Services\StockNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index()
    {
        $inventory = Inventory::with('productVariant.product')->get();

        return response()->json([
            'status' => 'success',
            'data' => $inventory,
        ]);
    }

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

    public function update(
        Request $request,
        string $id,
        StockNotificationService $stockNotifications
    ) {
        $validated = $request->validate([
            'quantity' => 'sometimes|required|integer|min:0',
            'low_stock_threshold' => 'sometimes|required|integer|min:0',
        ]);

        $result = DB::transaction(function () use ($id, $validated) {
            $inventory = Inventory::whereKey($id)
                ->lockForUpdate()
                ->first();

            if (!$inventory) {
                return null;
            }

            $previousQuantity = (int) $inventory->quantity;

            $inventory->update($validated);

            return [
                'inventory' => $inventory->fresh()
                    ->load('productVariant.product'),
                'previous_quantity' => $previousQuantity,
                'current_quantity' => (int) $inventory->quantity,
                'threshold' => (int) $inventory->low_stock_threshold,
                'variant_id' => (int) $inventory->product_variant_id,
            ];
        });

        if ($result === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Inventory not found',
            ], 404);
        }

        // Create notifications only after the stock update commits.
        if ($result['previous_quantity'] !== $result['current_quantity']) {
            $variant = ProductVariant::find($result['variant_id']);

            if ($variant) {
                $stockNotifications->recordTransition(
                    $variant,
                    $result['previous_quantity'],
                    $result['current_quantity'],
                    $result['threshold']
                );
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Inventory updated successfully',
            'data' => $result['inventory'],
        ]);
    }

    public function adjust(
        Request $request,
        string $id,
        StockNotificationService $stockNotifications
    ) {
        $validated = $request->validate([
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|integer|min:1',
            'note' => 'nullable|string',
        ]);

        $result = DB::transaction(function () use ($id, $validated) {
            $inventory = Inventory::whereKey($id)
                ->lockForUpdate()
                ->first();

            if (!$inventory) {
                return ['not_found' => true];
            }

            $previousQuantity = (int) $inventory->quantity;
            $threshold = (int) $inventory->low_stock_threshold;
            $amount = (int) $validated['quantity'];

            if (
                $validated['type'] === 'out'
                && $previousQuantity < $amount
            ) {
                return ['insufficient_stock' => true];
            }

            if ($validated['type'] === 'in') {
                $inventory->quantity += $amount;
            } elseif ($validated['type'] === 'out') {
                $inventory->quantity -= $amount;
            } else {
                // "adjustment" sets the exact stock quantity.
                $inventory->quantity = $amount;
            }

            $inventory->save();

            InventoryMovement::create([
                'product_variant_id' => $inventory->product_variant_id,
                'type' => $validated['type'],
                'quantity' => $amount,
                'reference_type' => 'manual',
                'reference_id' => null,
                'note' => $validated['note'] ?? null,
            ]);

            return [
                'inventory' => $inventory->fresh()
                    ->load('productVariant.product'),
                'previous_quantity' => $previousQuantity,
                'current_quantity' => (int) $inventory->quantity,
                'threshold' => $threshold,
                'variant_id' => (int) $inventory->product_variant_id,
            ];
        });

        if (!empty($result['not_found'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Inventory not found',
            ], 404);
        }

        if (!empty($result['insufficient_stock'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Insufficient stock',
            ], 422);
        }

        if ($result['previous_quantity'] !== $result['current_quantity']) {
            $variant = ProductVariant::find($result['variant_id']);

            if ($variant) {
                $stockNotifications->recordTransition(
                    $variant,
                    $result['previous_quantity'],
                    $result['current_quantity'],
                    $result['threshold']
                );
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Stock adjusted successfully',
            'data' => $result['inventory'],
        ]);
    }

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
        )->latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $movements,
        ]);
    }
}
