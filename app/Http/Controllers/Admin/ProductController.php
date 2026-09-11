<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()->with(['category', 'variants.inventory']);

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('variants', function ($vq) use ($search) {
                        $vq->where('sku', 'like', "%{$search}%")
                            ->orWhere('barcode', 'like', "%{$search}%")
                            ->orWhere('color', 'like', "%{$search}%")
                            ->orWhere('size', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->status === 'active') {
            $query->where('status', true);
        } elseif ($request->status === 'inactive') {
            $query->where('status', false);
        }

        if ($request->filled('price')) {
            $query->whereHas('variants', function ($q) use ($request) {
                match ($request->price) {
                    '0-25' => $q->whereBetween('selling_price', [0, 25]),
                    '25-50' => $q->where('selling_price', '>', 25)->where('selling_price', '<=', 50),
                    '50-100' => $q->where('selling_price', '>', 50)->where('selling_price', '<=', 100),
                    '100' => $q->where('selling_price', '>', 100),
                    default => null,
                };
            });
        }

        match ($request->input('sort', 'latest')) {
            'name' => $query->orderBy('name'),
            'oldest' => $query->oldest('id'),
            default => $query->latest('id'),
        };

        $products = $query->paginate(10)->withQueryString();
        $categories = Category::query()->orderBy('name')->get();

        return view('admin.products.product', [
            'products' => $products,
            'categories' => $categories,
            'totalProducts' => Product::count(),
            'activeProducts' => Product::where('status', true)->count(),
            'inactiveProducts' => Product::where('status', false)->count(),
            'totalVariants' => ProductVariant::count(),
        ]);
    }

    public function create()
    {
        return redirect()->route('admin.products.index');
    }

    public function store(Request $request)
    {
        $validated = $this->validateProductRequest($request);

        $variants = $validated['variants'];
        unset($validated['variants']);

        $this->validateVariantCombinations($variants);

        DB::transaction(function () use ($request, $validated, $variants) {
            $imagePath = $request->hasFile('image')
                ? $request->file('image')->store('products', 'public')
                : null;

            $product = Product::create([
                'category_id' => $validated['category_id'],
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'image' => $imagePath,
                'status' => (bool) $validated['status'],
            ]);

            foreach ($variants as $variantData) {
                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $variantData['sku'],
                    'barcode' => $variantData['barcode'] ?? null,
                    'size' => $variantData['size'] ?? null,
                    'color' => $variantData['color'] ?? null,
                    'cost_price' => $variantData['cost_price'],
                    'selling_price' => $variantData['selling_price'],
                    'discount_type' => $variantData['discount_type'] ?? 'percentage',
                    'discount_value' => $variantData['discount_value'] ?? 0,
                    'status' => (bool) ($variantData['status'] ?? true),
                ]);

                DB::table('inventory')->insert([
                    'product_variant_id' => $variant->id,
                    'quantity' => (int) ($variantData['stock'] ?? 0),
                    'low_stock_threshold' => (int) ($variantData['low_stock_threshold'] ?? 0),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return redirect()->route('admin.products.index')
            ->with('success', 'Product and variants created successfully.');
    }

    public function edit(Product $product)
    {
        return redirect()->route('admin.products.index');
    }

    public function update(Request $request, Product $product)
    {
        $validated = $this->validateProductRequest($request, $product->id);
        $variants = $validated['variants'];
        unset($validated['variants']);

        $this->validateVariantCombinations($variants, $product->id);

        DB::transaction(function () use ($request, $validated, $variants, $product) {
            $imagePath = $product->image;

            if ($request->hasFile('image')) {
                $newPath = $request->file('image')->store('products', 'public');
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }
                $imagePath = $newPath;
            }

            $product->update([
                'category_id' => $validated['category_id'],
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'image' => $imagePath,
                'status' => (bool) $validated['status'],
            ]);

            $existingIds = $product->variants()->pluck('id')->all();
            $submittedIds = collect($variants)->pluck('id')->filter()->map(fn ($id) => (int) $id)->all();

            foreach (array_diff($existingIds, $submittedIds) as $variantId) {
                $usedInSales = DB::table('sale_items')->where('product_variant_id', $variantId)->exists();
                $usedInPurchases = DB::table('purchase_items')->where('product_variant_id', $variantId)->exists();

                if ($usedInSales || $usedInPurchases) {
                    abort(422, 'A variant already used in sales or purchases cannot be removed. Keep it or deactivate it.');
                }

                DB::table('inventory')->where('product_variant_id', $variantId)->delete();
                DB::table('inventory_movements')->where('product_variant_id', $variantId)->delete();
                DB::table('cart_items')->where('product_variant_id', $variantId)->delete();
                ProductVariant::where('id', $variantId)->where('product_id', $product->id)->delete();
            }

            foreach ($variants as $variantData) {
                $variantId = !empty($variantData['id']) ? (int) $variantData['id'] : null;

                if ($variantId) {
                    $variant = ProductVariant::where('product_id', $product->id)->findOrFail($variantId);
                    $variant->update([
                        'sku' => $variantData['sku'],
                        'barcode' => $variantData['barcode'] ?? null,
                        'size' => $variantData['size'] ?? null,
                        'color' => $variantData['color'] ?? null,
                        'cost_price' => $variantData['cost_price'],
                        'selling_price' => $variantData['selling_price'],
                        'discount_type' => $variantData['discount_type'] ?? 'percentage',
                        'discount_value' => $variantData['discount_value'] ?? 0,
                        'status' => (bool) ($variantData['status'] ?? true),
                    ]);
                } else {
                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $variantData['sku'],
                        'barcode' => $variantData['barcode'] ?? null,
                        'size' => $variantData['size'] ?? null,
                        'color' => $variantData['color'] ?? null,
                        'cost_price' => $variantData['cost_price'],
                        'selling_price' => $variantData['selling_price'],
                        'discount_type' => $variantData['discount_type'] ?? 'percentage',
                        'discount_value' => $variantData['discount_value'] ?? 0,
                        'status' => (bool) ($variantData['status'] ?? true),
                    ]);
                }

                $inventory = DB::table('inventory')->where('product_variant_id', $variant->id)->first();

                $newStock = (int) ($variantData['stock'] ?? 0);
                $oldStock = $inventory ? (int) $inventory->quantity : 0;

                if ($inventory) {
                    DB::table('inventory')->where('product_variant_id', $variant->id)->update([
                        'quantity' => $newStock,
                        'low_stock_threshold' => (int) ($variantData['low_stock_threshold'] ?? 0),
                        'updated_at' => now(),
                    ]);
                } else {
                    DB::table('inventory')->insert([
                        'product_variant_id' => $variant->id,
                        'quantity' => $newStock,
                        'low_stock_threshold' => (int) ($variantData['low_stock_threshold'] ?? 0),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Keep inventory history when stock is manually changed from Product Edit.
                if ($newStock !== $oldStock) {
                    DB::table('inventory_movements')->insert([
                        'product_variant_id' => $variant->id,
                        'type' => $newStock > $oldStock ? 'in' : 'out',
                        'quantity' => abs($newStock - $oldStock),
                        'reference_type' => 'product_edit',
                        'reference_id' => $product->id,
                        'note' => 'Stock adjusted from product management',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });

        return redirect()->route('admin.products.index')
            ->with('success', 'Product and variants updated successfully.');
    }

    public function toggleStatus(Product $product)
    {
        $product->update(['status' => ! $product->status]);

        return back()->with('success', 'Product status updated successfully.');
    }

    public function destroy(Product $product)
    {
        $variantIds = $product->variants()->pluck('id');

        $hasSales = DB::table('sale_items')->whereIn('product_variant_id', $variantIds)->exists();
        $hasPurchases = DB::table('purchase_items')->whereIn('product_variant_id', $variantIds)->exists();

        if ($hasSales || $hasPurchases) {
            return back()->with('error', 'This product cannot be deleted because it is already used in sales or purchases. Deactivate it instead.');
        }

        DB::transaction(function () use ($product, $variantIds) {
            DB::table('inventory')->whereIn('product_variant_id', $variantIds)->delete();
            DB::table('inventory_movements')->whereIn('product_variant_id', $variantIds)->delete();
            DB::table('cart_items')->whereIn('product_variant_id', $variantIds)->delete();
            DB::table('wishlists')->where('product_id', $product->id)->delete();
            DB::table('product_variants')->whereIn('id', $variantIds)->delete();

            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            $product->delete();
        });

        return back()->with('success', 'Product deleted successfully.');
    }

    private function validateProductRequest(Request $request, ?int $productId = null): array
    {
        $variantRules = [
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.sku' => [
                'required', 'string', 'max:255',
            ],
            'variants.*.barcode' => ['nullable', 'string', 'max:255'],
            'variants.*.size' => ['nullable', 'string', 'max:100'],
            'variants.*.color' => ['nullable', 'string', 'max:100'],
            'variants.*.cost_price' => ['required', 'numeric', 'min:0'],
            'variants.*.selling_price' => ['required', 'numeric', 'min:0'],
            'variants.*.discount_type' => ['required', Rule::in(['percentage', 'fixed'])],
            'variants.*.discount_value' => ['required', 'numeric', 'min:0'],
            'variants.*.stock' => ['required', 'integer', 'min:0'],
            'variants.*.low_stock_threshold' => ['required', 'integer', 'min:0'],
            'variants.*.status' => ['required', 'boolean'],
        ];

        $validated = $request->validate(array_merge([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'gender' => ['nullable', 'string', 'max:50'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['required', 'boolean'],
        ], $variantRules));

        foreach ($validated['variants'] as $index => $variant) {
            if ($variant['discount_type'] === 'percentage' && $variant['discount_value'] > 100) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "variants.$index.discount_value" => 'Percentage discount cannot be greater than 100%.',
                ]);
            }

            if ($variant['discount_type'] === 'fixed' && $variant['discount_value'] > $variant['selling_price']) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "variants.$index.discount_value" => 'Fixed discount cannot be greater than selling price.',
                ]);
            }
        }

        return $validated;
    }

    private function validateVariantCombinations(array $variants, ?int $productId = null): void
    {
        $seen = [];

        foreach ($variants as $index => $variant) {
            $color = strtolower(trim((string) ($variant['color'] ?? '')));
            $size = strtolower(trim((string) ($variant['size'] ?? '')));
            $key = $color . '|' . $size;

            if (isset($seen[$key])) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "variants.$index.color" => 'The same Color + Size combination is duplicated.',
                ]);
            }

            $seen[$key] = true;

            $skuQuery = ProductVariant::where('sku', $variant['sku']);
            if (!empty($variant['id'])) {
                $skuQuery->where('id', '!=', $variant['id']);
            }
            if ($skuQuery->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "variants.$index.sku" => 'This SKU is already used.',
                ]);
            }

            if (!empty($variant['barcode'])) {
                $barcodeQuery = ProductVariant::where('barcode', $variant['barcode']);
                if (!empty($variant['id'])) {
                    $barcodeQuery->where('id', '!=', $variant['id']);
                }
                if ($barcodeQuery->exists()) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        "variants.$index.barcode" => 'This barcode is already used.',
                    ]);
                }
            }
        }
    }
}
