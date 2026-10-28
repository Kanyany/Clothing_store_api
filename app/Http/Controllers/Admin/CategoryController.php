<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Category List
     */
    public function index(Request $request)
    {
        $query = Category::query()
            ->with('parent')
            ->withCount('products');

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */
        if ($request->filled('search')) {

            $search = trim($request->search);

            $query->where(function ($q) use ($search) {

                $q->where(
                    'name',
                    'like',
                    "%{$search}%"
                );

                $q->orWhere(
                    'description',
                    'like',
                    "%{$search}%"
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
                (int) $request->status
            );

        }

        /*
        |--------------------------------------------------------------------------
        | Parent Filter
        |--------------------------------------------------------------------------
        */
        if ($request->filled('parent_id')) {

            if ($request->parent_id === 'root') {

                $query->whereNull('parent_id');

            } else {

                $query->where(
                    'parent_id',
                    (int) $request->parent_id
                );

            }
        }

        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */
        $categories = $query
            ->orderByRaw(
                'CASE WHEN parent_id IS NULL THEN 0 ELSE 1 END'
            )
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */
        $stats = [
            'total' => Category::count(),

            'active' => Category::where(
                'status',
                true
            )->count(),

            'inactive' => Category::where(
                'status',
                false
            )->count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | Root Parent Categories
        |--------------------------------------------------------------------------
        */
        $parentCategories = Category::query()
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        /*
        |--------------------------------------------------------------------------
        | ALL Category Options
        |
        | Used by Add Category Dialog
        |--------------------------------------------------------------------------
        */
        $allCategories = Category::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'parent_id',
            ]);

        $categoryOptions =
            $this->buildOptions(
                $allCategories
            );

        /*
        |--------------------------------------------------------------------------
        | Edit Options
        |
        | Each category gets its own parent options.
        | Current category and descendants are removed.
        |--------------------------------------------------------------------------
        */
        $editCategoryOptions = [];

        foreach ($allCategories as $category) {

            $descendantIds =
                $this->getDescendantIds(
                    $category
                );

            $availableCategories =
                $allCategories
                    ->where('id', '!=', $category->id)
                    ->whereNotIn(
                        'id',
                        $descendantIds
                    )
                    ->values();

            $editCategoryOptions[$category->id] =
                $this->buildOptions(
                    $availableCategories
                );
        }

        // Build a small, nested tree for the visual department-first UI.
        // All values come directly from the categories table.
        // Build the visual tree from the REAL filtered result so Search/Status
        // controls actually change what is displayed. Keep ancestors visible
        // when a matching child/subcategory is found.
        $treeSource = Category::query()
            ->withCount('products')
            ->orderBy('name')
            ->get();

        $categoryTree = $this->buildTree($treeSource);

        $searchTerm = trim((string) $request->input('search', ''));
        $statusFilter = $request->input('status');
        if ($searchTerm !== '' || ($statusFilter !== null && $statusFilter !== '')) {
            $categoryTree = $this->filterTree($categoryTree, $searchTerm, $statusFilter);
        }

        $parentOptions = $categoryOptions;
        $stats['root'] = Category::whereNull('parent_id')->count();

        return view(
            'admin.categories.index',
            compact(
                'categories',
                'stats',
                'parentCategories',
                'categoryOptions',
                'editCategoryOptions',
                'categoryTree',
                'parentOptions'
            )
        );
    }

    /**
     * Create page
     *
     * Kept for compatibility.
     * Main UI now uses Dialog.
     */
    public function create()
    {
        $categories = Category::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'parent_id',
            ]);

        $categoryOptions =
            $this->buildOptions(
                $categories
            );

        return view(
            'admin.categories.create',
            compact('categoryOptions')
        );
    }

    /**
     * Store Category
     */
    public function store(Request $request)
    {
        $parentId =
            $request->input('parent_id');

        $parentId =
            $parentId !== null &&
            $parentId !== ''
                ? (int) $parentId
                : null;

        $validated =
            $request->validate([

                'parent_id' => [
                    'nullable',
                    'integer',
                    'exists:categories,id',
                ],

                'name' => [
                    'required',
                    'string',
                    'max:255',

                    Rule::unique(
                        'categories',
                        'name'
                    )->where(
                        function ($query) use ($parentId) {

                            if ($parentId === null) {

                                return $query->whereNull(
                                    'parent_id'
                                );
                            }

                            return $query->where(
                                'parent_id',
                                $parentId
                            );
                        }
                    ),
                ],

                'description' => [
                    'nullable',
                    'string',
                ],

                'image' => [
                    'nullable',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:2048',
                ],

                'status' => [
                    'required',
                    'boolean',
                ],

            ]);

        $imagePath = null;

        if ($request->hasFile('image')) {

            $imagePath =
                $request
                    ->file('image')
                    ->store(
                        'categories',
                        'public'
                    );
        }

        Category::create([

            'parent_id' => $parentId,

            'name' => $validated['name'],

            'description' => $validated['description']
                ?? null,

            'image' => $imagePath,

            'status' => (bool) $validated['status'],

        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with(
                'success',
                'Category created successfully.'
            );
    }

    /**
     * Edit page
     *
     * Kept for compatibility.
     * Main UI now uses Dialog.
     */
    public function edit(Category $category)
    {
        $descendantIds =
            $this->getDescendantIds(
                $category
            );

        $categories =
            Category::query()
                ->where(
                    'id',
                    '!=',
                    $category->id
                )
                ->whereNotIn(
                    'id',
                    $descendantIds
                )
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                    'parent_id',
                ]);

        $categoryOptions =
            $this->buildOptions(
                $categories
            );

        return view(
            'admin.categories.edit',
            compact(
                'category',
                'categoryOptions'
            )
        );
    }

    /**
     * Update Category
     */
    public function update(
        Request $request,
        Category $category
    ) {

        $parentId =
            $request->input('parent_id');

        $parentId =
            $parentId !== null &&
            $parentId !== ''
                ? (int) $parentId
                : null;

        $descendantIds =
            $this->getDescendantIds(
                $category
            );

        $validated =
            $request->validate([

                'parent_id' => [
                    'nullable',
                    'integer',
                    'exists:categories,id',

                    Rule::notIn(
                        $descendantIds
                    ),

                    Rule::notIn([
                        $category->id,
                    ]),
                ],

                'name' => [
                    'required',
                    'string',
                    'max:255',

                    Rule::unique(
                        'categories',
                        'name'
                    )
                        ->ignore($category->id)
                        ->where(
                            function ($query) use ($parentId) {

                                if ($parentId === null) {

                                    return $query->whereNull(
                                        'parent_id'
                                    );
                                }

                                return $query->where(
                                    'parent_id',
                                    $parentId
                                );
                            }
                        ),
                ],

                'description' => [
                    'nullable',
                    'string',
                ],

                'image' => [
                    'nullable',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:2048',
                ],

                'status' => [
                    'required',
                    'boolean',
                ],

            ]);

        $imagePath =
            $category->image;

        if ($request->hasFile('image')) {

            if ($category->image) {

                Storage::disk('public')
                    ->delete(
                        $category->image
                    );
            }

            $imagePath =
                $request
                    ->file('image')
                    ->store(
                        'categories',
                        'public'
                    );
        }

        $category->update([

            'parent_id' => $parentId,

            'name' => $validated['name'],

            'description' => $validated['description']
                ?? null,

            'image' => $imagePath,

            'status' => (bool) $validated['status'],

        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with(
                'success',
                'Category updated successfully.'
            );
    }

    /**
     * Toggle Active / Inactive
     */
    public function toggleStatus(
        Category $category
    ) {

        $category->status =
            ! $category->status;

        $category->save();

        return redirect()
            ->route('admin.categories.index')
            ->with(
                'success',
                'Category status updated successfully.'
            );
    }

    /**
     * Delete Category
     */
    public function destroy(
        Category $category
    ) {

        if (
            $category
                ->products()
                ->exists()
        ) {

            return redirect()
                ->route(
                    'admin.categories.index'
                )
                ->with(
                    'error',
                    'Cannot delete this category because it has products.'
                );
        }

        if (
            $category
                ->children()
                ->exists()
        ) {

            return redirect()
                ->route(
                    'admin.categories.index'
                )
                ->with(
                    'error',
                    'Cannot delete this category because it has child categories.'
                );
        }

        if ($category->image) {

            Storage::disk('public')
                ->delete(
                    $category->image
                );
        }

        $category->delete();

        return redirect()
            ->route(
                'admin.categories.index'
            )
            ->with(
                'success',
                'Category deleted successfully.'
            );
    }

    /**
     * Build the department -> category -> subcategory tree used by the UI.
     */
    private function buildTree($categories, $parentId = null): array
    {
        $nodes = [];

        foreach ($categories as $category) {
            if (($category->parent_id === null ? null : (int) $category->parent_id) !== $parentId) {
                continue;
            }

            $nodes[] = [
                'id' => $category->id,
                'name' => $category->name,
                'parent_id' => $category->parent_id,
                'description' => $category->description,
                'image' => $category->image,
                'status' => (bool) $category->status,
                'products_count' => (int) $category->products_count,
                'children' => $this->buildTree($categories, (int) $category->id),
            ];
        }

        return $nodes;
    }

    /**
     * Filter the nested tree while keeping the matching category's ancestors.
     * This keeps the Department -> Category -> Subcategory UI intact while
     * making the toolbar filters functional.
     */
    private function filterTree(array $nodes, string $search, $status): array
    {
        $result = [];
        $search = mb_strtolower($search);

        foreach ($nodes as $node) {
            $children = $this->filterTree($node['children'] ?? [], $search, $status);

            $haystack = mb_strtolower(
                trim((string) ($node['name'] ?? '').' '.(string) ($node['description'] ?? ''))
            );

            $matchesSearch = $search === '' || str_contains($haystack, $search);
            $matchesStatus = $status === null || $status === '' || (int) $node['status'] === (int) $status;

            // Keep an ancestor when a descendant matches, so the user can see
            // where the matching category sits in the hierarchy.
            if (($matchesSearch && $matchesStatus) || count($children) > 0) {
                $node['children'] = $children;
                $result[] = $node;
            }
        }

        return $result;
    }

    /**
     * Build Dropdown Options
     */
    private function buildOptions(
        $categories,
        $parentId = null,
        $level = 0
    ) {

        $options = [];

        $children =
            $categories->where(
                'parent_id',
                $parentId
            );

        foreach ($children as $category) {

            $options[] = [

                'id' => $category->id,

                'name' => str_repeat(
                    '— ',
                    $level
                )
                    .$category->name,

            ];

            $options =
                array_merge(
                    $options,
                    $this->buildOptions(
                        $categories,
                        $category->id,
                        $level + 1
                    )
                );
        }

        return $options;
    }

    /**
     * Get all descendant IDs
     */
    private function getDescendantIds(
        Category $category
    ): array {

        $ids = [];

        $children =
            Category::query()
                ->where(
                    'parent_id',
                    $category->id
                )
                ->get([
                    'id',
                ]);

        foreach ($children as $child) {

            $ids[] =
                $child->id;

            $ids =
                array_merge(
                    $ids,
                    $this->getDescendantIds(
                        $child
                    )
                );
        }

        return $ids;
    }
}
