<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Services\StockNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    /**
     * Display inventory with real database statistics and search.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $query = ProductVariant::query()->with([
            'product.category.parent',
            'inventory',
        ]);

        if ($search !== '') {
            $normalizedSearch = preg_replace(
                '/\b(?:sku|barcode|product|category|size|color|stock|price|id)\s*:\s*/iu',
                ' ',
                $search
            );

            $normalizedSearch = trim((string) $normalizedSearch);
            $tokens = preg_split(
                '/\s+/u',
                $normalizedSearch,
                -1,
                PREG_SPLIT_NO_EMPTY
            );

            $query->where(function ($q) use ($tokens) {
                foreach ($tokens as $token) {
                    $like = '%' . addcslashes($token, '%_\\') . '%';

                    $q->where(function ($tokenQuery) use ($like, $token) {
                        $tokenQuery
                            ->where('product_variants.id', 'like', $like)
                            ->orWhere('product_variants.sku', 'like', $like)
                            ->orWhere('product_variants.barcode', 'like', $like)
                            ->orWhere('product_variants.size', 'like', $like)
                            ->orWhere('product_variants.color', 'like', $like)
                            ->orWhere('product_variants.cost_price', 'like', $like)
                            ->orWhere('product_variants.selling_price', 'like', $like)
                            ->orWhere('product_variants.discount_type', 'like', $like)
                            ->orWhere('product_variants.discount_value', 'like', $like);

                        if (strcasecmp($token, 'active') === 0) {
                            $tokenQuery->orWhere('product_variants.status', true);
                        } elseif (strcasecmp($token, 'inactive') === 0) {
                            $tokenQuery->orWhere('product_variants.status', false);
                        }

                        $tokenQuery->orWhereHas(
                            'product',
                            function ($productQuery) use ($like, $token) {
                                $productQuery
                                    ->where('products.id', 'like', $like)
                                    ->orWhere('products.name', 'like', $like)
                                    ->orWhere('products.description', 'like', $like)
                                    ->orWhere('products.gender', 'like', $like)
                                    ->orWhere('products.image', 'like', $like);

                                if (strcasecmp($token, 'active') === 0) {
                                    $productQuery->orWhere('products.status', true);
                                } elseif (strcasecmp($token, 'inactive') === 0) {
                                    $productQuery->orWhere('products.status', false);
                                }

                                $productQuery->orWhereHas(
                                    'category',
                                    function ($categoryQuery) use ($like) {
                                        $categoryQuery
                                            ->where('categories.id', 'like', $like)
                                            ->orWhere('categories.name', 'like', $like)
                                            ->orWhere('categories.description', 'like', $like)
                                            ->orWhereHas(
                                                'parent',
                                                function ($parentQuery) use ($like) {
                                                    $parentQuery
                                                        ->where('categories.id', 'like', $like)
                                                        ->orWhere('categories.name', 'like', $like)
                                                        ->orWhere('categories.description', 'like', $like)
                                                        ->orWhereHas(
                                                            'parent',
                                                            function ($grandParentQuery) use ($like) {
                                                                $grandParentQuery
                                                                    ->where('categories.id', 'like', $like)
                                                                    ->orWhere('categories.name', 'like', $like)
                                                                    ->orWhere('categories.description', 'like', $like);
                                                            }
                                                        );
                                                }
                                            );
                                    }
                                );
                            }
                        );

                        $tokenQuery->orWhereHas(
                            'inventory',
                            function ($inventoryQuery) use ($like) {
                                $inventoryQuery
                                    ->where('inventory.id', 'like', $like)
                                    ->orWhere('inventory.quantity', 'like', $like)
                                    ->orWhere('inventory.low_stock_threshold', 'like', $like);
                            }
                        );
                    });
                }
            });
        }

        $variants = $query
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'total_variants' => ProductVariant::count(),
            'inventory_records' => (int) DB::table('inventory')->count(),
            'total_stock' => (int) DB::table('inventory')->sum('quantity'),
            'low_stock' => (int) DB::table('inventory')
                ->where('quantity', '>', 0)
                ->whereColumn('quantity', '<=', 'low_stock_threshold')
                ->count(),
            'out_of_stock' => (int) DB::table('inventory')
                ->where('quantity', '<=', 0)
                ->count(),
        ];

        return view('admin.inventory.index', compact(
            'variants',
            'stats',
            'search'
        ));
    }

    /**
     * Set the exact stock quantity and low-stock threshold.
     */
    public function update(
        Request $request,
        ProductVariant $productVariant,
        StockNotificationService $stockNotifications
    ) {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
        ]);

        $result = DB::transaction(function () use ($validated, $productVariant) {
            $inventory = DB::table('inventory')
                ->where('product_variant_id', $productVariant->id)
                ->lockForUpdate()
                ->first();

            $previousQuantity = $inventory
                ? (int) $inventory->quantity
                : null;

            $previousThreshold = $inventory
                ? (int) $inventory->low_stock_threshold
                : null;

            $oldQuantity = $previousQuantity ?? 0;
            $newQuantity = (int) $validated['quantity'];
            $newThreshold = (int) $validated['low_stock_threshold'];

            if ($inventory) {
                DB::table('inventory')
                    ->where('product_variant_id', $productVariant->id)
                    ->update([
                        'quantity' => $newQuantity,
                        'low_stock_threshold' => $newThreshold,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('inventory')->insert([
                    'product_variant_id' => $productVariant->id,
                    'quantity' => $newQuantity,
                    'low_stock_threshold' => $newThreshold,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $difference = $newQuantity - $oldQuantity;

            if ($difference !== 0) {
                DB::table('inventory_movements')->insert([
                    'product_variant_id' => $productVariant->id,
                    'type' => $difference > 0 ? 'in' : 'out',
                    'quantity' => abs($difference),
                    'reference_type' => 'manual_adjustment',
                    'reference_id' => null,
                    'note' => 'Exact stock updated from Admin Inventory.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return [
                'previous_quantity' => $previousQuantity,
                'previous_threshold' => $previousThreshold,
                'current_quantity' => $newQuantity,
                'current_threshold' => $newThreshold,
            ];
        });

        // Notify after the inventory transaction has committed.
        // This call uses the current StockNotificationService signature.
        if ($result['previous_quantity'] !== $result['current_quantity']) {
            $previousQuantity = $result['previous_quantity'];

            // A new inventory record has no previous stock state.
            // Use a value above the threshold as its normal-state baseline.
            if ($previousQuantity === null) {
                $previousQuantity = max(
                    $result['current_threshold'] + 1,
                    1
                );
            }

            $stockNotifications->recordTransition(
                $productVariant,
                $previousQuantity,
                $result['current_quantity'],
                $result['current_threshold']
            );
        }

        return back()->with('success', 'Inventory updated successfully.');
    }

    /**
     * Increase or decrease existing stock.
     */
    public function adjust(
        Request $request,
        ProductVariant $productVariant,
        StockNotificationService $stockNotifications
    ) {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'type' => ['required', 'in:in,out'],
        ]);

        $result = DB::transaction(function () use ($validated, $productVariant) {
            $inventory = DB::table('inventory')
                ->where('product_variant_id', $productVariant->id)
                ->lockForUpdate()
                ->first();

            if (!$inventory) {
                DB::table('inventory')->insert([
                    'product_variant_id' => $productVariant->id,
                    'quantity' => 0,
                    'low_stock_threshold' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $inventory = DB::table('inventory')
                    ->where('product_variant_id', $productVariant->id)
                    ->lockForUpdate()
                    ->first();
            }

            $previousQuantity = (int) $inventory->quantity;
            $threshold = (int) $inventory->low_stock_threshold;
            $amount = (int) $validated['quantity'];

            if (
                $validated['type'] === 'out'
                && $amount > $previousQuantity
            ) {
                abort(422, 'Cannot remove more stock than the current stock.');
            }

            $newQuantity = $validated['type'] === 'in'
                ? $previousQuantity + $amount
                : $previousQuantity - $amount;

            DB::table('inventory')
                ->where('product_variant_id', $productVariant->id)
                ->update([
                    'quantity' => $newQuantity,
                    'updated_at' => now(),
                ]);

            DB::table('inventory_movements')->insert([
                'product_variant_id' => $productVariant->id,
                'type' => $validated['type'],
                'quantity' => $amount,
                'reference_type' => 'manual_adjustment',
                'reference_id' => null,
                'note' => $validated['type'] === 'in'
                    ? 'Stock added from Admin Inventory.'
                    : 'Stock removed from Admin Inventory.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'previous_quantity' => $previousQuantity,
                'current_quantity' => $newQuantity,
                'threshold' => $threshold,
            ];
        });

        if ($result['previous_quantity'] !== $result['current_quantity']) {
            $stockNotifications->recordTransition(
                $productVariant,
                $result['previous_quantity'],
                $result['current_quantity'],
                $result['threshold']
            );
        }

        return back()->with('success', 'Stock adjusted successfully.');
    }
}
