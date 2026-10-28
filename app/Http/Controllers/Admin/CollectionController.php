<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class CollectionController extends Controller
{
    /**
     * Display collections with product counts and search.
     */
    public function index(Request $request): View
    {
        $collections = Collection::query()
            ->withCount('products')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->input('search'));

                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.collections.index', compact('collections'));
    }

    /**
     * Show the create form.
     */
    public function create(): View
    {
        // A new collection can select active products only.
        $products = Product::query()
            ->where('status', true)
            ->orderBy('name')
            ->get(['id', 'name', 'image']);

        return view('admin.collections.create', compact('products'));
    }

    /**
     * Store a new collection.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('collections', 'slug'),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'status' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('products', 'id'),
            ],
        ]);

        $imagePath = null;

        try {
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')
                    ->store('collections', 'public');
            }

            DB::transaction(function () use ($validated, $imagePath) {
                $name = trim($validated['name']);
                $slug = $this->makeUniqueSlug(
                    $validated['slug'] ?? '',
                    $name
                );

                $collection = Collection::create([
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $validated['description'] ?? null,
                    'image' => $imagePath,
                    'status' => $validated['status'] ?? false,
                    'sort_order' => $validated['sort_order'] ?? 0,
                ]);

                $this->syncProducts(
                    $collection,
                    $validated['product_ids'] ?? []
                );
            });

            return redirect()
                ->route('admin.collections.index')
                ->with('success', 'Collection created successfully.');
        } catch (Throwable $e) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            report($e);

            return back()
                ->withInput()
                ->withErrors([
                    'collection' => 'Unable to create collection. Please check the application log.',
                ]);
        }
    }

    /**
     * Show the edit form.
     */
    public function edit(Collection $collection): View
    {
        $collection->load('products:id,name,image');

        // Keep active products available and retain already attached
        // products even if their status has since become inactive.
        $products = Product::query()
            ->where(function ($query) use ($collection) {
                $query->where('status', true)
                    ->orWhereHas('collections', function ($subQuery) use ($collection) {
                        $subQuery->where('collections.id', $collection->id);
                    });
            })
            ->orderBy('name')
            ->get(['id', 'name', 'image']);

        $selectedProductIds = $collection->products
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        return view('admin.collections.edit', compact(
            'collection',
            'products',
            'selectedProductIds'
        ));
    }

    /**
     * Update an existing collection.
     */
    public function update(
        Request $request,
        Collection $collection
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('collections', 'slug')
                    ->ignore($collection->id),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'remove_image' => ['nullable', 'boolean'],
            'status' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('products', 'id'),
            ],
        ]);

        $newImagePath = null;
        $oldImagePath = $collection->image;

        try {
            if ($request->hasFile('image')) {
                $newImagePath = $request->file('image')
                    ->store('collections', 'public');
            }

            DB::transaction(function () use (
                $validated,
                $collection,
                $newImagePath,
                $request
            ) {
                $name = trim($validated['name']);
                $slug = $this->makeUniqueSlug(
                    $validated['slug'] ?? '',
                    $name,
                    $collection->id
                );

                $data = [
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $validated['description'] ?? null,
                    'status' => $validated['status'] ?? false,
                    'sort_order' => $validated['sort_order'] ?? 0,
                ];

                if ($newImagePath !== null) {
                    $data['image'] = $newImagePath;
                } elseif ($request->boolean('remove_image')) {
                    $data['image'] = null;
                }

                $collection->update($data);

                $this->syncProducts(
                    $collection,
                    $validated['product_ids'] ?? []
                );
            });

            // Delete the old file only after the database update succeeds.
            if (
                $oldImagePath &&
                ($newImagePath !== null || $request->boolean('remove_image'))
            ) {
                Storage::disk('public')->delete($oldImagePath);
            }

            return redirect()
                ->route('admin.collections.index')
                ->with('success', 'Collection updated successfully.');
        } catch (Throwable $e) {
            if ($newImagePath !== null) {
                Storage::disk('public')->delete($newImagePath);
            }

            report($e);

            return back()
                ->withInput()
                ->withErrors([
                    'collection' => 'Unable to update collection. Please check the application log.',
                ]);
        }
    }

    /**
     * Delete a collection and detach its products.
     */
    public function destroy(Collection $collection): RedirectResponse
    {
        $imagePath = $collection->image;

        try {
            DB::transaction(function () use ($collection) {
                $collection->products()->detach();
                $collection->delete();
            });

            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            return redirect()
                ->route('admin.collections.index')
                ->with('success', 'Collection deleted successfully.');
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors([
                'collection' => 'Unable to delete collection. Please check the application log.',
            ]);
        }
    }

    /**
     * Create a unique slug without overwriting another collection's slug.
     */
    private function makeUniqueSlug(
        string $requestedSlug,
        string $name,
        ?int $ignoreId = null
    ): string {
        $slug = Str::slug(trim($requestedSlug) !== '' ? $requestedSlug : $name);

        if ($slug === '') {
            $slug = 'collection';
        }

        $baseSlug = $slug;
        $counter = 2;

        while (
            Collection::query()
                ->where('slug', $slug)
                ->when(
                    $ignoreId !== null,
                    fn ($query) => $query->where('id', '!=', $ignoreId)
                )
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Synchronize selected products and their pivot sort order.
     */
    private function syncProducts(
        Collection $collection,
        array $productIds
    ): void {
        $syncData = [];

        foreach (array_values(array_unique($productIds)) as $index => $id) {
            $syncData[$id] = [
                'sort_order' => $index,
            ];
        }

        $collection->products()->sync($syncData);
    }
}
