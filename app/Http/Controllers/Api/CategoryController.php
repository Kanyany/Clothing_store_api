<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display all parent categories with children.
     */
    public function index()
    {
        $categories = Category::with('children')
            ->whereNull('parent_id')
            ->latest()
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
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:categories,id',

            'name' => 'required|string|max:255|unique:categories,name',

            'description' => 'nullable|string',

            'image' => 'nullable|string|max:255',

            'status' => 'nullable|boolean',
        ]);

        // Prevent category from being its own parent
        if (
            isset($validated['parent_id']) &&
            $validated['parent_id'] === null
        ) {
            $validated['parent_id'] = null;
        }

        $category = Category::create($validated);

        // Load parent and children
        $category->load([
            'parent',
            'children',
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
            'children',
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
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:categories,id',

            'name' =>
                'required|string|max:255|unique:categories,name,' . $category->id,

            'description' => 'nullable|string',

            'image' => 'nullable|string|max:255',

            'status' => 'nullable|boolean',
        ]);

        // Prevent category from being its own parent
        if (
            isset($validated['parent_id']) &&
            (int) $validated['parent_id'] === $category->id
        ) {
            return response()->json([
                'status' => 'error',
                'message' => 'A category cannot be its own parent.',
            ], 422);
        }

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