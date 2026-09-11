<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    /**
     * Inventory List
     */
    public function index(Request $request)
    {
        $query = ProductVariant::query()
            ->with([
                'product',
                'inventory',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = trim($request->search);

            $query->where(function ($q) use ($search) {

                $q->whereHas('product', function ($productQuery) use ($search) {

                    $productQuery->where(
                        'name',
                        'like',
                        "%{$search}%"
                    );

                });

                $q->orWhere(
                    'size',
                    'like',
                    "%{$search}%"
                );

                $q->orWhere(
                    'color',
                    'like',
                    "%{$search}%"
                );
            });
        }


        /*
        |--------------------------------------------------------------------------
        | Variants
        |--------------------------------------------------------------------------
        */

        $variants = $query
            ->orderByDesc('id')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Real Database Statistics
        |--------------------------------------------------------------------------
        */

        $totalVariants = ProductVariant::count();

        $inventoryRecords = Inventory::count();

        $totalStock = Inventory::sum('quantity');

        $lowStock = Inventory::query()
            ->whereColumn(
                'quantity',
                '<=',
                'low_stock_threshold'
            )
            ->where(
                'low_stock_threshold',
                '>',
                0
            )
            ->count();

        $outOfStock = Inventory::where(
            'quantity',
            '<=',
            0
        )->count();


        $stats = [

            'total_variants' => $totalVariants,

            'inventory_records' => $inventoryRecords,

            'total_stock' => $totalStock,

            'low_stock' => $lowStock,

            'out_of_stock' => $outOfStock,

        ];


        return view(
            'admin.inventory.index',
            compact(
                'variants',
                'stats'
            )
        );
    }


    /**
     * Set Exact Stock
     */
    public function update(
        Request $request,
        ProductVariant $productVariant
    ) {

        $validated = $request->validate([

            'quantity' => [
                'required',
                'integer',
                'min:0',
            ],

            'low_stock_threshold' => [
                'required',
                'integer',
                'min:0',
            ],

        ]);


        DB::transaction(function () use (
            $productVariant,
            $validated
        ) {

            $inventory = Inventory::firstOrNew([
                'product_variant_id' =>
                    $productVariant->id,
            ]);


            $oldQuantity =
                (int) ($inventory->quantity ?? 0);

            $newQuantity =
                (int) $validated['quantity'];


            $inventory->quantity =
                $newQuantity;

            $inventory->low_stock_threshold =
                (int) $validated['low_stock_threshold'];

            $inventory->save();


            /*
            |--------------------------------------------------------------------------
            | Record Movement Only When Stock Changes
            |--------------------------------------------------------------------------
            */

            if ($oldQuantity !== $newQuantity) {

                $difference =
                    $newQuantity - $oldQuantity;


                InventoryMovement::create([

                    'product_variant_id' =>
                        $productVariant->id,

                    'type' =>
                        $difference > 0
                            ? 'in'
                            : 'out',

                    'quantity' =>
                        abs($difference),

                    'reference_type' =>
                        'manual_adjustment',

                    'reference_id' =>
                        null,

                    'note' =>
                        'Stock updated from Inventory Management',

                ]);
            }
        });


        return redirect()
            ->route('admin.inventory.index')
            ->with(
                'success',
                'Inventory updated successfully.'
            );
    }


    /**
     * Add / Remove Stock
     */
    public function adjust(
        Request $request,
        ProductVariant $productVariant
    ) {

        $validated = $request->validate([

            'type' => [
                'required',
                'in:in,out',
            ],

            'quantity' => [
                'required',
                'integer',
                'min:1',
            ],

            'note' => [
                'nullable',
                'string',
                'max:500',
            ],

        ]);


        DB::transaction(function () use (
            $productVariant,
            $validated
        ) {

            $inventory = Inventory::firstOrCreate(

                [
                    'product_variant_id' =>
                        $productVariant->id,
                ],

                [
                    'quantity' => 0,

                    'low_stock_threshold' => 0,
                ]
            );


            $adjustmentQuantity =
                (int) $validated['quantity'];


            if ($validated['type'] === 'in') {

                $inventory->quantity +=
                    $adjustmentQuantity;

            } else {

                if (
                    $inventory->quantity
                    < $adjustmentQuantity
                ) {

                    abort(
                        422,
                        'Insufficient stock.'
                    );
                }


                $inventory->quantity -=
                    $adjustmentQuantity;
            }


            $inventory->save();


            /*
            |--------------------------------------------------------------------------
            | Record Movement
            |--------------------------------------------------------------------------
            */

            InventoryMovement::create([

                'product_variant_id' =>
                    $productVariant->id,

                'type' =>
                    $validated['type'],

                'quantity' =>
                    $adjustmentQuantity,

                'reference_type' =>
                    'manual_adjustment',

                'reference_id' =>
                    null,

                'note' =>
                    $validated['note']
                    ?? 'Manual stock adjustment',

            ]);
        });


        return redirect()
            ->route('admin.inventory.index')
            ->with(
                'success',
                'Stock adjusted successfully.'
            );
    }
}