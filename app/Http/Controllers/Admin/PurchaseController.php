<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\ProductVariant;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    /**
     * Purchase List
     */
    public function index(Request $request)
    {
        $query = Purchase::query()
            ->with([
                'items.productVariant.product',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = trim($request->search);

            $query->where(function ($q) use ($search) {

                $q->where(
                    'supplier_name',
                    'like',
                    "%{$search}%"
                );

                $q->orWhereHas(
                    'items.productVariant.product',
                    function ($productQuery) use ($search) {

                        $productQuery->where(
                            'products.name',
                            'like',
                            "%{$search}%"
                        );
                    }
                );
            });
        }


        /*
        |--------------------------------------------------------------------------
        | Status Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {

            $query->where(
                'status',
                $request->status
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Sort
        |--------------------------------------------------------------------------
        */

        switch ($request->get('sort', 'latest')) {

            case 'oldest':

                $query
                    ->orderBy('purchase_date')
                    ->orderBy('id');

                break;

            case 'highest':

                $query->orderByDesc(
                    'total_amount'
                );

                break;

            case 'lowest':

                $query->orderBy(
                    'total_amount'
                );

                break;

            default:

                $query
                    ->orderByDesc('purchase_date')
                    ->orderByDesc('id');

                break;
        }


        $purchases = $query
            ->paginate(10)
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Database Statistics
        |--------------------------------------------------------------------------
        */

        $stats = [

            'total' => Purchase::count(),

            'draft' => Purchase::where(
                'status',
                'draft'
            )->count(),

            'received' => Purchase::where(
                'status',
                'received'
            )->count(),

            'cancelled' => Purchase::where(
                'status',
                'cancelled'
            )->count(),

            'received_value' =>
                (float) Purchase::where(
                    'status',
                    'received'
                )->sum('total_amount'),
        ];


        $variants = ProductVariant::query()
            ->with(['product'])
            ->where('status', true)
            ->whereHas('product', function ($query) {
                $query->where('status', true);
            })
            ->orderBy('product_id')
            ->get();

        return view(
            'admin.purchases.index',
            compact('purchases', 'stats', 'variants')
        );
    }


    /**
     * Create Purchase
     */
    public function create()
    {
        $variants = ProductVariant::query()
            ->with([
                'product',
                'inventory',
            ])
            ->where('status', true)
            ->whereHas(
                'product',
                function ($query) {
                    $query->where(
                        'status',
                        true
                    );
                }
            )
            ->orderBy('product_id')
            ->get();


        return view(
            'admin.purchases.create',
            compact('variants')
        );
    }


    /**
     * Store Purchase
     */
    public function store(Request $request)
    {
        $validated = $request->validate([

            'supplier_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'purchase_date' => [
                'required',
                'date',
            ],

            'status' => [
                'required',
                'in:draft,received,cancelled',
            ],

            'note' => [
                'nullable',
                'string',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.product_variant_id' => [
                'required',
                'integer',
                'exists:product_variants,id',
            ],

            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],

            'items.*.cost_price' => [
                'required',
                'numeric',
                'min:0',
            ],
        ]);


        DB::transaction(function () use ($validated) {

            $totalAmount = collect(
                $validated['items']
            )->sum(function ($item) {

                return
                    $item['quantity'] *
                    $item['cost_price'];
            });


            $purchase = Purchase::create([

                'supplier_name' =>
                    $validated['supplier_name'] ?? null,

                'purchase_date' =>
                    $validated['purchase_date'],

                'total_amount' =>
                    $totalAmount,

                'status' =>
                    $validated['status'],

                'note' =>
                    $validated['note'] ?? null,
            ]);


            foreach ($validated['items'] as $item) {

                $subtotal =
                    $item['quantity'] *
                    $item['cost_price'];


                $purchase->items()->create([

                    'product_variant_id' =>
                        $item['product_variant_id'],

                    'quantity' =>
                        $item['quantity'],

                    'cost_price' =>
                        $item['cost_price'],

                    'subtotal' =>
                        $subtotal,
                ]);


                /*
                |--------------------------------------------------------------------------
                | Received At Creation
                |--------------------------------------------------------------------------
                */

                if (
                    $validated['status'] === 'received'
                ) {

                    $inventory = Inventory::firstOrCreate(
                        [
                            'product_variant_id' =>
                                $item['product_variant_id'],
                        ],
                        [
                            'quantity' => 0,
                            'low_stock_threshold' => 5,
                        ]
                    );


                    $inventory->quantity +=
                        $item['quantity'];

                    $inventory->save();


                    InventoryMovement::create([

                        'product_variant_id' =>
                            $item['product_variant_id'],

                        'type' =>
                            'in',

                        'quantity' =>
                            $item['quantity'],

                        'reference_type' =>
                            'purchase',

                        'reference_id' =>
                            $purchase->id,

                        'note' =>
                            'Stock received from purchase',
                    ]);
                }
            }
        });


        return redirect()
            ->route(
                'admin.purchases.index'
            )
            ->with(
                'success',
                'Purchase created successfully.'
            );
    }


    /**
     * Purchase Detail
     */
    public function show(Purchase $purchase)
    {
        $purchase->load([
            'items.productVariant.product',
            'items.productVariant.inventory',
        ]);


        return view(
            'admin.purchases.show',
            compact('purchase')
        );
    }


    /**
     * Receive Purchase
     */
    public function receive(Purchase $purchase)
    {
        if ($purchase->status === 'received') {

            return back()->with(
                'error',
                'This purchase has already been received.'
            );
        }


        if ($purchase->status === 'cancelled') {

            return back()->with(
                'error',
                'Cancelled purchase cannot be received.'
            );
        }


        DB::transaction(function () use ($purchase) {

            $purchase->load('items');


            foreach ($purchase->items as $item) {

                $inventory = Inventory::firstOrCreate(
                    [
                        'product_variant_id' =>
                            $item->product_variant_id,
                    ],
                    [
                        'quantity' => 0,
                        'low_stock_threshold' => 5,
                    ]
                );


                $inventory->quantity +=
                    $item->quantity;

                $inventory->save();


                InventoryMovement::create([

                    'product_variant_id' =>
                        $item->product_variant_id,

                    'type' =>
                        'in',

                    'quantity' =>
                        $item->quantity,

                    'reference_type' =>
                        'purchase',

                    'reference_id' =>
                        $purchase->id,

                    'note' =>
                        'Stock received from purchase',
                ]);
            }


            $purchase->update([
                'status' => 'received',
            ]);
        });


        return redirect()
            ->route(
                'admin.purchases.show',
                $purchase->id
            )
            ->with(
                'success',
                'Purchase received successfully. Inventory has been updated.'
            );
    }
}