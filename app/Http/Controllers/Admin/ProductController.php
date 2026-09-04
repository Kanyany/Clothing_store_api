<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Product List
     */
    public function index(Request $request)
    {
        $query = Product::with([
            'category',
            'variants.inventory',
        ]);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('category_id')) {
            $query->where(
                'category_id',
                $request->category_id
            );
        }

        // Status filter
        if ($request->status === 'active') {
            $query->where('status', true);
        }

        if ($request->status === 'inactive') {
            $query->where('status', false);
        }

        // Sort
        switch ($request->sort) {

            case 'name':
                $query->orderBy('name');
                break;

            case 'oldest':
                $query->oldest();
                break;

            default:
                $query->latest();
                break;
        }

        $products = $query
            ->paginate(10)
            ->withQueryString();

        return view(
            'admin.products.index',
            compact('products')
        );
    }


    /**
     * Show Add Product Page
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view(
            'admin.products.create',
            compact('categories')
        );
    }


    /**
     * Store Product
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

            'brand' => [
                'nullable',
                'string',
                'max:255',
            ],

            'image' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'nullable',
                'boolean',
            ],
        ]);

        $validated['status'] =
            $request->boolean('status', true);

        $product = Product::create($validated);

        return redirect()
            ->route('admin.products.index')
            ->with(
                'success',
                'Product created successfully.'
            );
    }
}