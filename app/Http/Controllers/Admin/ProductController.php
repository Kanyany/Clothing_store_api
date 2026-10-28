<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Collection;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;



class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()->with([
            'category.parent.parent',
            'variants.inventory',
            'collections',
        ]);
        /*
         |--------------------------------------------------------------------------
         | Global Product Search
         |--------------------------------------------------------------------------
         | One search box for the admin. It searches across the product itself,
         | its category, and every variant/inventory field that is useful when
         | finding a clothing item.
         */
        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            if ($search !== '') {
                // Split the admin's search into words. Every word must match
                // somewhere in the product, category, variant, or inventory.
                // Example: "Sana Knitted Top" can match three separate fields.
                // Remove search labels such as "SKU:", "barcode:", "color:", etc.
                // before splitting into tokens. Otherwise the label itself would
                // become a required search word and cause valid results to return 0.
                $normalizedSearch = preg_replace(
                    '/\b(?:sku|barcode|product|category|size|color|stock|price|id)\s*:\s*/iu',
                    ' ',
                    $search
                );
                $normalizedSearch = trim((string) $normalizedSearch);
                $tokens = preg_split('/\s+/u', $normalizedSearch, -1, PREG_SPLIT_NO_EMPTY);

                $query->where(function ($q) use ($tokens) {
                    foreach ($tokens as $token) {
                        $like = '%' . addcslashes($token, '%_\\') . '%';

                        $q->where(function ($tokenQuery) use ($like, $token) {
                            // Product fields
                            $tokenQuery->where('products.id', 'like', $like)
                                ->orWhere('products.name', 'like', $like)
                                ->orWhere('products.description', 'like', $like)
                                ->orWhere('products.gender', 'like', $like)
                                ->orWhere('products.image', 'like', $like);

                            // Product status: support natural admin searches.
                            if (strcasecmp($token, 'active') === 0) {
                                $tokenQuery->orWhere('products.status', true);
                            } elseif (strcasecmp($token, 'inactive') === 0) {
                                $tokenQuery->orWhere('products.status', false);
                            }

                            // Category + parent + grandparent
                            $tokenQuery->orWhereHas('category', function ($categoryQuery) use ($like) {
                                $categoryQuery->where('categories.id', 'like', $like)
                                    ->orWhere('categories.name', 'like', $like)
                                    ->orWhere('categories.description', 'like', $like)
                                    ->orWhereHas('parent', function ($parentQuery) use ($like) {
                                        $parentQuery->where('categories.id', 'like', $like)
                                            ->orWhere('categories.name', 'like', $like)
                                            ->orWhere('categories.description', 'like', $like)
                                            ->orWhereHas('parent', function ($grandParentQuery) use ($like) {
                                                $grandParentQuery->where('categories.id', 'like', $like)
                                                    ->orWhere('categories.name', 'like', $like)
                                                    ->orWhere('categories.description', 'like', $like);
                                            });
                                    });
                            });

                            // Variant + its inventory
                            $tokenQuery->orWhereHas('variants', function ($variantQuery) use ($like, $token) {
                                $variantQuery->where('product_variants.id', 'like', $like)
                                    ->orWhere('product_variants.sku', 'like', $like)
                                    ->orWhere('product_variants.barcode', 'like', $like)
                                    ->orWhere('product_variants.size', 'like', $like)
                                    ->orWhere('product_variants.color', 'like', $like)
                                    ->orWhere('product_variants.cost_price', 'like', $like)
                                    ->orWhere('product_variants.selling_price', 'like', $like)
                                    ->orWhere('product_variants.discount_type', 'like', $like)
                                    ->orWhere('product_variants.discount_value', 'like', $like);

                                if (strcasecmp($token, 'active') === 0) {
                                    $variantQuery->orWhere('product_variants.status', true);
                                } elseif (strcasecmp($token, 'inactive') === 0) {
                                    $variantQuery->orWhere('product_variants.status', false);
                                }

                                $variantQuery->orWhereHas('inventory', function ($inventoryQuery) use ($like) {
                                    $inventoryQuery->where('inventory.quantity', 'like', $like)
                                        ->orWhere('inventory.low_stock_threshold', 'like', $like)
                                        ->orWhere('inventory.id', 'like', $like);
                                });
                            });
                        });
                    }
                });
            }
        }

        if ($request->filled('category_id')) {
            $selectedCategoryId = (int) $request->input('category_id');

            // A category filter includes the selected category and all of its
            // child categories. This makes parent/department filters useful
            // instead of only matching products assigned directly to the
            // selected row.
            $allCategoryRows = Category::query()
                ->get(['id', 'parent_id']);

            $categoryIds = $this->getCategoryAndDescendantIds(
                $allCategoryRows,
                $selectedCategoryId
            );

            $query->whereIn('category_id', $categoryIds);
        }

        if ($request->input('status') === 'active') {
            $query->where('status', true);
        } elseif ($request->input('status') === 'inactive') {
            $query->where('status', false);
        }

        if ($request->filled('price')) {
            $query->whereHas('variants', function ($q) use ($request) {
                match ($request->input('price')) {
                    '0-25' => $q->whereBetween('selling_price', [0, 25]),
                    '25-50' => $q->where('selling_price', '>', 25)->where('selling_price', '<=', 50),
                    '50-100' => $q->where('selling_price', '>', 50)->where('selling_price', '<=', 100),
                    '100' => $q->where('selling_price', '>', 100),
                    default => null,
                };
            });
        }

        match ($request->input('sort', 'latest')) {
            'name' => $query->orderBy('products.name'),
            'oldest' => $query->oldest('products.id'),
            default => $query->latest('products.id'),
        };

        $products = $query->paginate(12)->withQueryString();

        $allCategories = Category::query()
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id', 'status']);

        // The Product Blade uses this hierarchical structure for the category flow.
        $categoryTree = $this->buildCategoryTree($allCategories);

        // Keep edit data outside Blade so the view stays simple and reliable.
        $editData = $products->getCollection()->mapWithKeys(function (Product $product) {
            return [
                $product->id => $product->variants->map(function (ProductVariant $variant) {
                    return [
                        'id' => $variant->id,
                        'sku' => $variant->sku,
                        'barcode' => $variant->barcode,
                        'size' => $variant->size,
                        'color' => $variant->color,
                        'cost_price' => (float) $variant->cost_price,
                        'selling_price' => (float) $variant->selling_price,
                        'discount_type' => $variant->discount_type ?? 'percentage',
                        'discount_value' => (float) ($variant->discount_value ?? 0),
                        'stock' => (int) ($variant->inventory?->quantity ?? 0),
                        'low_stock_threshold' => (int) ($variant->inventory?->low_stock_threshold ?? 0),
                        'status' => (bool) $variant->status,
                    ];
                })->values()->all(),
            ];
        })->all();

        $colorImageData = $products->getCollection()->mapWithKeys(function (Product $product) {
            return [
                $product->id => is_array($product->color_images) ? $product->color_images : [],
            ];
        })->all();

        $detailImageData = $products->getCollection()->mapWithKeys(function (Product $product) {
            return [
                $product->id => is_array($product->detail_images) ? $product->detail_images : [],
            ];
        })->all();

        $collections = Collection::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'status']);

        return view('admin.products.product', [
            'products' => $products,
            'categories' => $allCategories,
            'categoryTree' => $categoryTree,
            'editData' => $editData,
            'colorImageData' => $colorImageData,
            'detailImageData' => $detailImageData,
            'collections' => $collections,
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
        $collectionIds = $validated['collection_ids'] ?? [];
        unset($validated['variants'], $validated['collection_ids']);
        $this->validateVariantCombinations($variants);

        DB::transaction(function () use ($request, $validated, $variants, $collectionIds) {
            $imagePath = $request->hasFile('image')
                ? $request->file('image')->store('products', 'public')
                : null;

            $product = Product::create([
                'category_id' => $validated['category_id'],
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'gender' => $this->normalizeGender($validated['gender'] ?? null),
                'image' => $imagePath,
                'status' => (bool) $validated['status'],
            ]);

            $product->collections()->sync($collectionIds);

            $colorImages = $this->storeColorImages($request, 'color_image_colors', 'color_images');
            $detailImages = $this->storeDetailImages($request);

            if ($colorImages) {
                $product->color_images = $colorImages;
            }

            if ($detailImages) {
                $product->detail_images = $detailImages;
            }

            if ($colorImages || $detailImages) {
                $product->save();
            }

            foreach ($variants as $variantData) {
                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => trim($variantData['sku']),
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
            ->with('success', 'Product created successfully.');
    }

    private function normalizeGender(?string $gender): string
    {
        $gender = strtolower(trim((string) $gender));

        return match ($gender) {
            'male', 'men', 'man' => 'male',
            'female', 'women', 'woman' => 'female',
            'kids', 'kid', 'children', 'child' => 'kids',
            'unisex' => 'unisex',
            default => 'unisex',
        };
    }

    public function edit(Product $product)
    {
        return redirect()->route('admin.products.index');
    }

    public function update(Request $request, Product $product)
    {
        $validated = $this->validateProductRequest($request, $product->id);
        $variants = $validated['variants'];
        $collectionIds = $validated['collection_ids'] ?? [];
        unset($validated['variants'], $validated['collection_ids']);
        $this->validateVariantCombinations($variants, $product->id);

        DB::transaction(function () use ($request, $validated, $variants, $collectionIds, $product) {
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
                'gender' => $this->normalizeGender($validated['gender'] ?? null),
                'image' => $imagePath,
                'status' => (bool) $validated['status'],
            ]);

            $product->collections()->sync($collectionIds);

            $colorImages = is_array($product->color_images) ? $product->color_images : [];
            $newColorImages = $this->storeColorImages($request, 'edit_color_image_colors', 'edit_color_images', $colorImages);

            $detailImages = is_array($product->detail_images) ? $product->detail_images : [];
            $newDetailImages = array_values(array_merge($detailImages, $this->storeDetailImages($request)));

            $existingIds = $product->variants()->pluck('id')->all();
            $submittedIds = collect($variants)->pluck('id')->filter()->map(fn ($id) => (int) $id)->all();

            foreach (array_diff($existingIds, $submittedIds) as $variantId) {
                $usedInSales = DB::table('sale_items')->where('product_variant_id', $variantId)->exists();
                $usedInPurchases = DB::table('purchase_items')->where('product_variant_id', $variantId)->exists();

                if ($usedInSales || $usedInPurchases) {
                    throw ValidationException::withMessages([
                        'variants' => 'A variant already used in sales or purchases cannot be removed. Keep it or deactivate it.',
                    ]);
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
                        'sku' => trim($variantData['sku']),
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
                        'sku' => trim($variantData['sku']),
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

            $activeColors = collect($variants)
                ->pluck('color')
                ->filter(fn ($color) => trim((string) $color) !== '')
                ->map(fn ($color) => $this->normalizeColor($color))
                ->unique()
                ->values()
                ->all();

            foreach (array_keys($newColorImages) as $colorKey) {
                if (!in_array($colorKey, $activeColors, true)) {
                    if (!empty($newColorImages[$colorKey])) {
                        Storage::disk('public')->delete($newColorImages[$colorKey]);
                    }
                    unset($newColorImages[$colorKey]);
                }
            }

            $product->color_images = $newColorImages;
            $product->detail_images = $newDetailImages;
            $product->save();
        });

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
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

            foreach ((array) $product->color_images as $colorImage) {
                if ($colorImage) {
                    Storage::disk('public')->delete($colorImage);
                }
            }

            foreach ((array) $product->detail_images as $detailImage) {
                if ($detailImage) {
                    Storage::disk('public')->delete($detailImage);
                }
            }

            $product->delete();
        });

        return back()->with('success', 'Product deleted successfully.');
    }

    private function validateProductRequest(Request $request, ?int $productId = null): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'gender' => ['nullable', 'string', 'max:50'],
            'image' => [
                'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048',
            ],
            'color_image_colors' => ['nullable', 'array'],
            'color_image_colors.*' => ['nullable', 'string', 'max:100'],
            'color_images' => ['nullable', 'array'],
            'color_images.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'edit_color_image_colors' => ['nullable', 'array'],
            'edit_color_image_colors.*' => ['nullable', 'string', 'max:100'],
            'edit_color_images' => ['nullable', 'array'],
            'edit_color_images.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'detail_images' => ['nullable', 'array'],
            'detail_images.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'status' => ['required', 'boolean'],
            'collection_ids' => ['nullable', 'array'],
            'collection_ids.*' => ['required', 'integer', 'distinct', 'exists:collections,id'],
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.sku' => ['required', 'string', 'max:255'],
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

        $validated = $request->validate($rules);

        foreach ($validated['variants'] as $index => $variant) {
            if ($variant['discount_type'] === 'percentage' && $variant['discount_value'] > 100) {
                throw ValidationException::withMessages([
                    "variants.$index.discount_value" => 'Percentage discount cannot be greater than 100%.',
                ]);
            }

            if ($variant['discount_type'] === 'fixed' && $variant['discount_value'] > $variant['selling_price']) {
                throw ValidationException::withMessages([
                    "variants.$index.discount_value" => 'Fixed discount cannot be greater than selling price.',
                ]);
            }
        }

        return $validated;
    }

    private function normalizeColor(?string $color): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', (string) $color)));
    }

    private function storeColorImages(Request $request, string $colorField, string $imageField, array $existing = []): array
    {
        $map = $existing;
        $colors = array_values((array) $request->input($colorField, []));
        $files = $request->file($imageField, []);

        foreach ($files as $index => $file) {
            if (!$file) {
                continue;
            }

            $key = $this->normalizeColor($colors[$index] ?? '');
            if ($key === '') {
                continue;
            }

            $newPath = $file->store('products/colors', 'public');

            if (!empty($map[$key])) {
                Storage::disk('public')->delete($map[$key]);
            }

            $map[$key] = $newPath;
        }

        return $map;
    }

    private function storeDetailImages(Request $request): array
    {
        $paths = [];

        foreach ((array) $request->file('detail_images', []) as $file) {
            if (!$file) {
                continue;
            }

            $paths[] = $file->store('products/details', 'public');
        }

        return $paths;
    }

    private function validateVariantCombinations(array $variants, ?int $productId = null): void
    {
        $seen = [];

        foreach ($variants as $index => $variant) {
            $color = strtolower(trim((string) ($variant['color'] ?? '')));
            $size = strtolower(trim((string) ($variant['size'] ?? '')));
            $key = $color . '|' . $size;

            if (isset($seen[$key])) {
                throw ValidationException::withMessages([
                    "variants.$index.color" => 'The same Color + Size combination is duplicated.',
                ]);
            }
            $seen[$key] = true;

            $skuQuery = ProductVariant::where('sku', $variant['sku']);
            if (!empty($variant['id'])) {
                $skuQuery->where('id', '!=', $variant['id']);
            }
            if ($skuQuery->exists()) {
                throw ValidationException::withMessages([
                    "variants.$index.sku" => 'This SKU is already used.',
                ]);
            }

            if (!empty($variant['barcode'])) {
                $barcodeQuery = ProductVariant::where('barcode', $variant['barcode']);
                if (!empty($variant['id'])) {
                    $barcodeQuery->where('id', '!=', $variant['id']);
                }
                if ($barcodeQuery->exists()) {
                    throw ValidationException::withMessages([
                        "variants.$index.barcode" => 'This barcode is already used.',
                    ]);
                }
            }
        }
    }


    private function getCategoryAndDescendantIds($categories, int $categoryId): array
    {
        $ids = [$categoryId];
        $changed = true;

        while ($changed) {
            $changed = false;

            foreach ($categories as $category) {
                $parentId = $category->parent_id !== null
                    ? (int) $category->parent_id
                    : null;

                if ($parentId !== null && in_array($parentId, $ids, true) && !in_array((int) $category->id, $ids, true)) {
                    $ids[] = (int) $category->id;
                    $changed = true;
                }
            }
        }

        return array_values(array_unique($ids));
    }

    private function buildCategoryTree($categories, ?int $parentId = null): array
    {
        $tree = [];
        foreach ($categories->where('parent_id', $parentId) as $category) {
            $tree[] = [
                'id' => $category->id,
                'name' => $category->name,
                'parent_id' => $category->parent_id,
                'status' => (bool) $category->status,
                'children' => $this->buildCategoryTree($categories, $category->id),
            ];
        }
        return $tree;
    }
}
