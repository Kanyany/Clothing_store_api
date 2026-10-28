<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class GlobalSearchController extends Controller
{
    /**
     * Global search for the Admin Panel.
     *
     * Uses existing database records only.
     * No fake/demo data is created.
     */
    public function index(Request $request)
    {
        $term = trim((string) $request->query('q', ''));

        if ($term === '') {
            return response()->json([
                'results' => [],
            ]);
        }

        $results = [];

        /*
        |--------------------------------------------------------------------------
        | Products
        |--------------------------------------------------------------------------
        |
        | Search the confirmed Product name column.
        | SKU/barcode is intentionally not queried here because in this
        | project SKU/barcode may belong to ProductVariant rather than Product.
        |
        */
        try {
            $products = Product::query()
                ->where('name', 'like', '%' . $term . '%')
                ->orderBy('name')
                ->limit(5)
                ->get();

            foreach ($products as $product) {
                $results[] = [
                    'type' => 'Products',
                    'title' => (string) $product->name,
                    'subtitle' => 'Product #' . $product->id,
                    'url' => route('admin.products.index'),
                ];
            }
        } catch (\Throwable $e) {
            // Keep global search working even if one optional search source fails.
        }

        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */
        try {
            $categories = Category::query()
                ->where('name', 'like', '%' . $term . '%')
                ->orderBy('name')
                ->limit(5)
                ->get();

            foreach ($categories as $category) {
                $results[] = [
                    'type' => 'Categories',
                    'title' => (string) $category->name,
                    'subtitle' => 'Category #' . $category->id,
                    'url' => route('admin.categories.index'),
                ];
            }
        } catch (\Throwable $e) {
            // Ignore this source if its schema/model is unavailable.
        }

        /*
        |--------------------------------------------------------------------------
        | Transactions
        |--------------------------------------------------------------------------
        |
        | Sale model has invoice_number and note.
        |
        */
        try {
            $sales = Sale::query()
                ->where(function ($query) use ($term) {
                    $query
                        ->where('invoice_number', 'like', '%' . $term . '%')
                        ->orWhere('note', 'like', '%' . $term . '%');
                })
                ->latest('id')
                ->limit(5)
                ->get();

            foreach ($sales as $sale) {
                $results[] = [
                    'type' => 'Transactions',
                    'title' => (string) $sale->invoice_number,
                    'subtitle' => 'Transaction #' . $sale->id,
                    'url' => route('admin.transactions.index'),
                ];
            }
        } catch (\Throwable $e) {
            // Ignore this source if its schema/model is unavailable.
        }

        /*
        |--------------------------------------------------------------------------
        | Purchases
        |--------------------------------------------------------------------------
        |
        | Purchase UI/model uses supplier_name.
        | Numeric search also supports an exact purchase ID.
        |
        */
        try {
            $purchases = Purchase::query()
                ->where(function ($query) use ($term) {
                    $query->where(
                        'supplier_name',
                        'like',
                        '%' . $term . '%'
                    );

                    if (ctype_digit($term)) {
                        $query->orWhere('id', (int) $term);
                    }
                })
                ->latest('id')
                ->limit(5)
                ->get();

            foreach ($purchases as $purchase) {
                $supplier = trim((string) ($purchase->supplier_name ?? ''));

                $results[] = [
                    'type' => 'Purchases',
                    'title' => 'Purchase #' . $purchase->id,
                    'subtitle' => $supplier !== '' ? $supplier : 'Purchase',
                    'url' => route('admin.purchases.index'),
                ];
            }
        } catch (\Throwable $e) {
            // Ignore this source if its schema/model is unavailable.
        }

        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */
        try {
            $users = User::query()
                ->with('role')
                ->where(function ($query) use ($term) {
                    $query
                        ->where('name', 'like', '%' . $term . '%')
                        ->orWhere('first_name', 'like', '%' . $term . '%')
                        ->orWhere('last_name', 'like', '%' . $term . '%')
                        ->orWhere('email', 'like', '%' . $term . '%')
                        ->orWhere('phone', 'like', '%' . $term . '%');
                })
                ->latest('id')
                ->limit(5)
                ->get();

            foreach ($users as $user) {
                $name = trim(
                    (string) ($user->name ?? '')
                );

                if ($name === '') {
                    $name = trim(
                        (string) ($user->first_name ?? '') . ' ' .
                        (string) ($user->last_name ?? '')
                    );
                }

                $results[] = [
                    'type' => 'Users',
                    'title' => $name !== '' ? $name : 'User #' . $user->id,
                    'subtitle' => (string) ($user->email ?? ''),
                    'url' => route('admin.users.index'),
                ];
            }
        } catch (\Throwable $e) {
            // Ignore this source if its schema/model is unavailable.
        }

        return response()->json([
            'results' => array_slice($results, 0, 20),
        ]);
    }
}
