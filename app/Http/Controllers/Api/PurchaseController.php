<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    /**
     * Display a listing of purchases.
     */
    public function index()
    {
        $purchases = Purchase::with([
            'items.productVariant.product',
        ])
        ->latest()
        ->get();

        return response()->json([
            'status' => 'success',
            'data' => $purchases,
        ]);
    }

    /**
     * Store a newly created purchase.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_name' => 'nullable|string|max:255',
            'purchase_date' => 'required|date',
            'status' => 'nullable|in:draft,received,cancelled',
            'note' => 'nullable|string',

            'items' => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.cost_price' => 'required|numeric|min:0',
        ]);

        $status = $validated['status'] ?? 'draft';

        $totalAmount = collect($validated['items'])
            ->sum(function ($item) {
                return $item['quantity'] * $item['cost_price'];
            });

        $purchase = DB::transaction(function () use (
            $validated,
            $status,
            $totalAmount
        ) {

            /*
            |--------------------------------------------------------------------------
            | Create Purchase
            |--------------------------------------------------------------------------
            */

            $purchase = Purchase::create([
                'supplier_name' => $validated['supplier_name'] ?? null,
                'purchase_date' => $validated['purchase_date'],
                'total_amount' => $totalAmount,
                'status' => $status,
                'note' => $validated['note'] ?? null,
            ]);


            /*
            |--------------------------------------------------------------------------
            | Create Purchase Items
            |--------------------------------------------------------------------------
            */

            foreach ($validated['items'] as $item) {

                $subtotal = $item['quantity'] * $item['cost_price'];

                $purchase->items()->create([
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity' => $item['quantity'],
                    'cost_price' => $item['cost_price'],
                    'subtotal' => $subtotal,
                ]);


                /*
                |--------------------------------------------------------------------------
                | Update Inventory
                |--------------------------------------------------------------------------
                */

                if ($status === 'received') {

                    $inventory = Inventory::firstOrCreate(
                        [
                            'product_variant_id' => $item['product_variant_id'],
                        ],
                        [
                            'quantity' => 0,
                            'low_stock_threshold' => 5,
                        ]
                    );

                    $inventory->quantity += $item['quantity'];
                    $inventory->save();


                    /*
                    |--------------------------------------------------------------------------
                    | Create Inventory Movement
                    |--------------------------------------------------------------------------
                    */

                    InventoryMovement::create([
                        'product_variant_id' => $item['product_variant_id'],
                        'type' => 'in',
                        'quantity' => $item['quantity'],
                        'reference_type' => 'purchase',
                        'reference_id' => $purchase->id,
                        'note' => 'Stock received from purchase',
                    ]);
                }
            }

            return $purchase;
        });


        /*
        |--------------------------------------------------------------------------
        | Load Relationships
        |--------------------------------------------------------------------------
        */

        $purchase->load([
            'items.productVariant.product',
        ]);


        return response()->json([
            'status' => 'success',
            'message' => 'Purchase created successfully',
            'data' => $purchase,
        ], 201);
    }

    /**
     * Display the specified purchase.
     */
    public function show(string $id)
    {
        $purchase = Purchase::with([
            'items.productVariant.product',
        ])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $purchase,
        ]);
    }

    /**
     * Update the specified purchase.
     */
    public function update(Request $request, string $id)
    {
        $purchase = Purchase::with('items')->findOrFail($id);

        $validated = $request->validate([
            'supplier_name' => 'nullable|string|max:255',
            'purchase_date' => 'sometimes|date',
            'status' => 'sometimes|in:draft,received,cancelled',
            'note' => 'nullable|string',
        ]);

        $oldStatus = $purchase->status;
        $newStatus = $validated['status'] ?? $oldStatus;

        DB::transaction(function () use (
            $purchase,
            $validated,
            $oldStatus,
            $newStatus
        ) {

            /*
            |--------------------------------------------------------------------------
            | Update Purchase
            |--------------------------------------------------------------------------
            */

            $purchase->update($validated);


            /*
            |--------------------------------------------------------------------------
            | Draft / Cancelled -> Received
            |--------------------------------------------------------------------------
            |
            | Add stock only once when purchase becomes received.
            |
            */

            if ($oldStatus !== 'received' && $newStatus === 'received') {

                foreach ($purchase->items as $item) {

                    $inventory = Inventory::firstOrCreate(
                        [
                            'product_variant_id' => $item->product_variant_id,
                        ],
                        [
                            'quantity' => 0,
                            'low_stock_threshold' => 5,
                        ]
                    );

                    $inventory->quantity += $item->quantity;
                    $inventory->save();


                    /*
                    |--------------------------------------------------------------------------
                    | Create Inventory Movement
                    |--------------------------------------------------------------------------
                    */

                    InventoryMovement::create([
                        'product_variant_id' => $item->product_variant_id,
                        'type' => 'in',
                        'quantity' => $item->quantity,
                        'reference_type' => 'purchase',
                        'reference_id' => $purchase->id,
                        'note' => 'Stock received from purchase',
                    ]);
                }
            }
        });


        /*
        |--------------------------------------------------------------------------
        | Load Relationships
        |--------------------------------------------------------------------------
        */

        $purchase->load([
            'items.productVariant.product',
        ]);


        return response()->json([
            'status' => 'success',
            'message' => 'Purchase updated successfully',
            'data' => $purchase,
        ]);
    }

    /**
     * Remove the specified purchase.
     */
    public function destroy(string $id)
    {
        $purchase = Purchase::findOrFail($id);

        $purchase->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Purchase deleted successfully',
        ]);
    }
}