<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * ================================================================
     * GET PRODUCTS
     * ================================================================
     *
     * Normal products:
     * GET /api/products
     *
     * Filter by category:
     * GET /api/products?category_id=1
     *
     * New Arrivals:
     * GET /api/products?sort=newest
     *
     * Best Sellers:
     * GET /api/products?sort=best_seller
     *
     * On Sale:
     * GET /api/products?on_sale=1
     *
     * On Sale + newest:
     * GET /api/products?on_sale=1&sort=newest
     *
     * No fake/demo data is used.
     */
    public function index(Request $request)
    {
        
       $query = Product::query()
        ->with([
            'category',
            'variants.inventory',
            'collections',
        ]);
            /*
        |--------------------------------------------------------------------------
        | CATEGORY FILTER
        |--------------------------------------------------------------------------
        |
        | Keep the existing category filter working.
        |
        | Example:
        | GET /api/products?category_id=1
        |
        */

        if ($request->filled('category_id')) {
            $query->where(
                'products.category_id',
                $request->category_id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | NEW ARRIVALS FILTER
        |--------------------------------------------------------------------------
        | Only products created within the last 7 days are returned when
        | new_arrivals=1 is supplied. Uses the real products.created_at field.
        */
        if ($request->boolean('new_arrivals')) {
            $query->where('products.created_at', '>=', now()->subDays(7));
        }

        /*
        |--------------------------------------------------------------------------
        | ON SALE FILTER
        |--------------------------------------------------------------------------
        |
        | A product is considered "On Sale" only when at least one
        | ACTIVE product variant has a REAL discount_value > 0.
        |
        | We do not create fake sale products.
        |
        | Example:
        | GET /api/products?on_sale=1
        |
        */

        if ($request->boolean('on_sale')) {
            $query->whereHas('variants', function ($variantQuery) {
                $variantQuery
                    ->where('status', true)
                    ->where('selling_price', '>', 0)
                    ->where('discount_value', '>', 0)
                    ->where(function ($discountQuery) {
                        $discountQuery
                            ->where(function ($percentageQuery) {
                                $percentageQuery
                                    ->where('discount_type', 'percentage')
                                    ->where('discount_value', '<', 100);
                            })
                            ->orWhere('discount_type', 'fixed');
                    });
            });
        }

        /*
        |--------------------------------------------------------------------------
        | SORT
        |--------------------------------------------------------------------------
        |
        | Supported:
        |
        | newest
        | best_seller
        |
        | Default = newest
        |
        */

        $sort = $request->get('sort', 'newest');

        /*
        |--------------------------------------------------------------------------
        | BEST SELLERS
        |--------------------------------------------------------------------------
        |
        | Best Seller is calculated automatically from REAL online orders.
        |
        | Database relationship:
        |
        | products
        |     ↓
        | product_variants
        |     ↓
        | order_items
        |     ↓
        | orders
        |
        | We calculate:
        |
        | SUM(order_items.quantity)
        |
        | Only:
        |
        | payment_status = paid
        | status != cancelled
        |
        | are counted.
        |
        | Therefore we do NOT need:
        |
        | - best_seller column
        | - manual best seller checkbox
        | - fake ranking
        |
        */

        if ($sort === 'best_seller') {
            $query
                ->leftJoin(
                    'product_variants',
                    'product_variants.product_id',
                    '=',
                    'products.id'
                )
                ->leftJoin(
                    'order_items',
                    'order_items.product_variant_id',
                    '=',
                    'product_variants.id'
                )
                ->leftJoin(
                    'orders',
                    'orders.id',
                    '=',
                    'order_items.order_id'
                )
                ->select('products.*')
                ->selectRaw(
                    "
                    COALESCE(
                        SUM(
                            CASE
                                WHEN
                                    LOWER(
                                        TRIM(
                                            COALESCE(
                                                orders.payment_status,
                                                ''
                                            )
                                        )
                                    ) = 'paid'

                                    AND

                                    LOWER(
                                        TRIM(
                                            COALESCE(
                                                orders.status,
                                                ''
                                            )
                                        )
                                    ) != 'cancelled'

                                THEN order_items.quantity

                                ELSE 0
                            END
                        ),
                        0
                    ) AS total_sold
                    "
                )
                ->groupBy('products.id')
                ->orderByDesc('total_sold')
                ->orderByDesc('products.created_at')
                ->orderByDesc('products.id');
        } else {
            /*
            |--------------------------------------------------------------------------
            | NEW ARRIVALS / DEFAULT
            |--------------------------------------------------------------------------
            |
            | New Arrivals are based on the REAL products.created_at.
            |
            | Newest products appear first.
            |
            */

            $query
                ->orderByDesc('products.created_at')
                ->orderByDesc('products.id');
        }

        /*
        |--------------------------------------------------------------------------
        | GET PRODUCTS
        |--------------------------------------------------------------------------
        */

        $products = $query->get();

        /*
        |--------------------------------------------------------------------------
        | RETURN JSON
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'status' => 'success',
            'data' => $products,
        ]);
    }


    /**
     * ================================================================
     * CREATE PRODUCT
     * ================================================================
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => [
                'required',
                'exists:categories,id',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'gender' => [
                'required',
                'in:male,female,unisex,kids',
            ],

            'image' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'boolean',
            ],
        ]);

        $product = Product::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Product created successfully',

            'data' => $product->load([
                'category',
            ]),
        ], 201);
    }


    /**
     * ================================================================
     * SHOW PRODUCT
     * ================================================================
     *
     * GET /api/products/{product}
     */
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


    /**
     * ================================================================
     * UPDATE PRODUCT
     * ================================================================
     */
    public function update(
        Request $request,
        Product $product
    ) {
        $validated = $request->validate([
            'category_id' => [
                'sometimes',
                'required',
                'exists:categories,id',
            ],

            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'gender' => [
                'sometimes',
                'required',
                'in:male,female,unisex,kids',
            ],

            'image' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'boolean',
            ],
        ]);

        $product->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Product updated successfully',

            'data' => $product
                ->fresh()
                ->load([
                    'category',
                    'variants.inventory',
                ]),
        ]);
    }


    /**
     * ================================================================
     * DELETE PRODUCT
     * ================================================================
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Product deleted successfully',
        ]);
    }
}