<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    /**
     * Display a listing of sales.
     */
    public function index()
    {
        $sales = Sale::with([
            'items.productVariant.product',
        ])
        ->latest()
        ->get();

        return response()->json([
            'status' => 'success',
            'data' => $sales,
        ]);
    }


    /**
     * Store a newly created sale.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_number' =>
                'required|string|max:255|unique:sales,invoice_number',

            'sale_date' =>
                'required|date',

            'discount' =>
                'nullable|numeric|min:0',

            'payment_method' => [
                'required',
                'in:cash,aba,acleda,wing,chip_mong,card,bank_transfer,cash_on_delivery',
            ],

            'status' =>
                'nullable|in:completed,cancelled',

            'note' =>
                'nullable|string',

            'items' =>
                'required|array|min:1',

            'items.*.product_variant_id' =>
                'required|exists:product_variants,id',

            'items.*.quantity' =>
                'required|integer|min:1',

            'items.*.selling_price' =>
                'required|numeric|min:0',
        ]);

        $discount = $validated['discount'] ?? 0;

        $sale = DB::transaction(function () use (
            $validated,
            $discount
        ) {

            /*
            |--------------------------------------------------------------------------
            | Calculate Subtotal
            |--------------------------------------------------------------------------
            */

            $subtotal = collect($validated['items'])
                ->sum(function ($item) {
                    return $item['quantity'] * $item['selling_price'];
                });


            /*
            |--------------------------------------------------------------------------
            | Calculate Total
            |--------------------------------------------------------------------------
            */

            $totalAmount = max(0, $subtotal - $discount);


            /*
            |--------------------------------------------------------------------------
            | Sale Status
            |--------------------------------------------------------------------------
            */

            $status = $validated['status'] ?? 'completed';


            /*
            |--------------------------------------------------------------------------
            | Create Sale
            |--------------------------------------------------------------------------
            */

            $sale = Sale::create([
                'invoice_number' =>
                    $validated['invoice_number'],

                'sale_date' =>
                    $validated['sale_date'],

                'subtotal' =>
                    $subtotal,

                'discount' =>
                    $discount,

                'total_amount' =>
                    $totalAmount,

                'payment_method' =>
                    $validated['payment_method'],

                'status' =>
                    $status,

                'note' =>
                    $validated['note'] ?? null,
            ]);


            /*
            |--------------------------------------------------------------------------
            | Create Sale Items + Reduce Inventory
            |--------------------------------------------------------------------------
            */

            foreach ($validated['items'] as $item) {

                $itemSubtotal =
                    $item['quantity'] * $item['selling_price'];


                /*
                |--------------------------------------------------------------------------
                | Create Sale Item
                |--------------------------------------------------------------------------
                */

                $sale->items()->create([
                    'product_variant_id' =>
                        $item['product_variant_id'],

                    'quantity' =>
                        $item['quantity'],

                    'selling_price' =>
                        $item['selling_price'],

                    'subtotal' =>
                        $itemSubtotal,
                ]);


                /*
                |--------------------------------------------------------------------------
                | Reduce Stock
                |--------------------------------------------------------------------------
                */

                if ($status === 'completed') {

                    $inventory = Inventory::where(
                        'product_variant_id',
                        $item['product_variant_id']
                    )
                    ->lockForUpdate()
                    ->first();


                    /*
                    |--------------------------------------------------------------------------
                    | Inventory Must Exist
                    |--------------------------------------------------------------------------
                    */

                    if (!$inventory) {
                        abort(
                            422,
                            'Inventory not found for product variant ID: '
                            . $item['product_variant_id']
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Check Stock
                    |--------------------------------------------------------------------------
                    */

                    if ($inventory->quantity < $item['quantity']) {
                        abort(
                            422,
                            'Insufficient stock for product variant ID: '
                            . $item['product_variant_id']
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Reduce Stock
                    |--------------------------------------------------------------------------
                    */

                    $inventory->quantity -= $item['quantity'];

                    $inventory->save();


                    /*
                    |--------------------------------------------------------------------------
                    | Create Inventory Movement
                    |--------------------------------------------------------------------------
                    */

                    InventoryMovement::create([
                        'product_variant_id' =>
                            $item['product_variant_id'],

                        'type' =>
                            'out',

                        'quantity' =>
                            $item['quantity'],

                        'reference_type' =>
                            'sale',

                        'reference_id' =>
                            $sale->id,

                        'note' =>
                            'Stock sold',
                    ]);
                }
            }


            return $sale;
        });


        /*
        |--------------------------------------------------------------------------
        | Load Relationships
        |--------------------------------------------------------------------------
        */

        $sale->load([
            'items.productVariant.product',
        ]);


        return response()->json([
            'status' =>
                'success',

            'message' =>
                'Sale created successfully',

            'data' =>
                $sale,
        ], 201);
    }


    /**
     * Display the specified sale.
     */
    public function show(string $id)
    {
        $sale = Sale::with([
            'items.productVariant.product',
        ])
        ->findOrFail($id);


        return response()->json([
            'status' =>
                'success',

            'data' =>
                $sale,
        ]);
    }


    /**
     * Update the specified sale.
     */
    public function update(Request $request, string $id)
    {
        $sale = Sale::with('items')->findOrFail($id);

        $validated = $request->validate([
            'invoice_number' =>
                'sometimes|string|max:255|unique:sales,invoice_number,' . $id,

            'sale_date' =>
                'sometimes|date',

            'discount' =>
                'sometimes|numeric|min:0',

            'payment_method' => [
                'sometimes',
                'in:cash,aba,acleda,wing,chip_mong,bank_transfer,cash_on_delivery,card',
            ],

            'status' =>
                'sometimes|in:completed,cancelled',

            'note' =>
                'nullable|string',
        ]);


        DB::transaction(function () use (
            $sale,
            $validated
        ) {

            /*
            |--------------------------------------------------------------------------
            | Handle Status Change
            |--------------------------------------------------------------------------
            */

            if (
                isset($validated['status']) &&
                $validated['status'] === 'cancelled' &&
                $sale->status === 'completed'
            ) {

                /*
                |--------------------------------------------------------------------------
                | Restore Stock
                |--------------------------------------------------------------------------
                */

                foreach ($sale->items as $item) {

                    $inventory = Inventory::where(
                        'product_variant_id',
                        $item->product_variant_id
                    )
                    ->lockForUpdate()
                    ->first();


                    /*
                    |--------------------------------------------------------------------------
                    | Inventory Must Exist
                    |--------------------------------------------------------------------------
                    */

                    if (!$inventory) {
                        abort(
                            422,
                            'Inventory not found for product variant ID: '
                            . $item->product_variant_id
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Return Stock
                    |--------------------------------------------------------------------------
                    */

                    $inventory->quantity += $item->quantity;

                    $inventory->save();


                    /*
                    |--------------------------------------------------------------------------
                    | Create Return Movement
                    |--------------------------------------------------------------------------
                    */

                    InventoryMovement::create([
                        'product_variant_id' =>
                            $item->product_variant_id,

                        'type' =>
                            'in',

                        'quantity' =>
                            $item->quantity,

                        'reference_type' =>
                            'sale',

                        'reference_id' =>
                            $sale->id,

                        'note' =>
                            'Stock returned from cancelled sale',
                    ]);
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Prevent Cancelled Sale From Being Completed Again
            |--------------------------------------------------------------------------
            */

            if (
                isset($validated['status']) &&
                $validated['status'] === 'completed' &&
                $sale->status === 'cancelled'
            ) {
                abort(
                    422,
                    'A cancelled sale cannot be changed back to completed.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Update Sale
            |--------------------------------------------------------------------------
            */

            $sale->update($validated);


            /*
            |--------------------------------------------------------------------------
            | Recalculate Total When Discount Changes
            |--------------------------------------------------------------------------
            */

            if (array_key_exists('discount', $validated)) {

                $discount = $validated['discount'];

                $totalAmount = max(
                    0,
                    $sale->subtotal - $discount
                );

                $sale->update([
                    'total_amount' => $totalAmount,
                ]);
            }
        });


        /*
        |--------------------------------------------------------------------------
        | Reload Sale
        |--------------------------------------------------------------------------
        */

        $sale->refresh();

        $sale->load([
            'items.productVariant.product',
        ]);


        return response()->json([
            'status' =>
                'success',

            'message' =>
                'Sale updated successfully',

            'data' =>
                $sale,
        ]);
    }


    /**
     * Remove the specified sale.
     */
    public function destroy(string $id)
    {
        $sale = Sale::with('items')->findOrFail($id);


        DB::transaction(function () use ($sale) {

            /*
            |--------------------------------------------------------------------------
            | Restore Stock
            |--------------------------------------------------------------------------
            |
            | If the sale is completed, its quantity has already
            | been removed from inventory.
            |
            | Therefore, when deleting it, we must return
            | that quantity back to inventory.
            |
            */

            if ($sale->status === 'completed') {

                foreach ($sale->items as $item) {

                    $inventory = Inventory::where(
                        'product_variant_id',
                        $item->product_variant_id
                    )
                    ->lockForUpdate()
                    ->first();


                    /*
                    |--------------------------------------------------------------------------
                    | Inventory Must Exist
                    |--------------------------------------------------------------------------
                    */

                    if (!$inventory) {
                        abort(
                            422,
                            'Inventory not found for product variant ID: '
                            . $item->product_variant_id
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Return Stock
                    |--------------------------------------------------------------------------
                    */

                    $inventory->quantity += $item->quantity;

                    $inventory->save();


                    /*
                    |--------------------------------------------------------------------------
                    | Create Inventory Movement
                    |--------------------------------------------------------------------------
                    */

                    InventoryMovement::create([
                        'product_variant_id' =>
                            $item->product_variant_id,

                        'type' =>
                            'in',

                        'quantity' =>
                            $item->quantity,

                        'reference_type' =>
                            'sale',

                        'reference_id' =>
                            $sale->id,

                        'note' =>
                            'Stock returned from deleted sale',
                    ]);
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Delete Sale Items
            |--------------------------------------------------------------------------
            */

            $sale->items()->delete();


            /*
            |--------------------------------------------------------------------------
            | Delete Sale
            |--------------------------------------------------------------------------
            */

            $sale->delete();
        });


        return response()->json([
            'status' =>
                'success',

            'message' =>
                'Sale deleted successfully',
        ]);
    }
}