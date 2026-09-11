<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Display all parent categories with children.
     */
    public function index()
    {
        $categories = Category::with([
        'children.children',
        ])

        ->whereNull('parent_id')
        ->where('status', true)
        ->orderByRaw("FIELD(name, 'Women', 'Men', 'Kids')")
        ->get();

        return response()->json([
            'status' => 'success',
            'data' => $categories,
        ]);
    }

    /**
     * Store a new category.
     */
    public function store(Request $request)
    {
        $parentId = $request->input('parent_id');

        $validated = $request->validate([
            'parent_id' => [
                'nullable',
                'exists:categories,id',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')
                    ->where(function ($query) use ($parentId) {
                        return $query->where('parent_id', $parentId);
                    }),
            ],

            'description' => 'nullable|string',

            'image' => 'nullable|string|max:255',

            'status' => 'nullable|boolean',
        ]);

        $category = Category::create($validated);

        $category->load([
            'parent',
            'children',
            'products',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Category created successfully',
            'data' => $category,
        ], 201);
    }

    /**
     * Display one category.
     */
    public function show(Category $category)
    {
        $category->load([
            'parent',
            'children.children',
            'products',
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $category,
        ]);
    }

    /**
     * Update a category.
     */
    public function update(Request $request, Category $category)
    {
        $parentId = $request->input('parent_id');

        // Prevent category from being its own parent.
        if (
            $parentId !== null &&
            (int) $parentId === $category->id
        ) {
            return response()->json([
                'status' => 'error',
                'message' => 'A category cannot be its own parent.',
            ], 422);
        }

        $validated = $request->validate([
            'parent_id' => [
                'nullable',
                'exists:categories,id',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')
                    ->where(function ($query) use ($parentId) {
                        return $query->where('parent_id', $parentId);
                    })
                    ->ignore($category->id),
            ],

            'description' => 'nullable|string',

            'image' => 'nullable|string|max:255',

            'status' => 'nullable|boolean',
        ]);

        $category->update($validated);

        $category->load([
            'parent',
            'children',
            'products',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Category updated successfully',
            'data' => $category,
        ]);
    }

    /**
     * Delete a category.
     */
    public function destroy(Category $category)
    {
        $category->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Category deleted successfully',
        ]);
    }
}