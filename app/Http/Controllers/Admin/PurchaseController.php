<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\ProductVariant;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $rawSearch = trim((string) $request->input('search', ''));
        $search = $this->normalizeSearch($rawSearch);

        $query = Purchase::query()
            ->with(['items.productVariant.product'])
            ->withCount('items');

        if ($search !== '') {
            $terms = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY);

            foreach ($terms as $term) {
                $escaped = addcslashes($term, "%_\\");
                $like = "%{$escaped}%";

                $query->where(function ($q) use ($like, $term) {
                    $q->where('purchases.supplier_name', 'like', $like)
                        ->orWhere('purchases.note', 'like', $like)
                        ->orWhere('purchases.status', 'like', $like)
                        ->orWhere('purchases.purchase_date', 'like', $like)
                        ->orWhere('purchases.total_amount', 'like', $like)
                        ->orWhere('purchases.id', 'like', $like)
                        ->orWhereHas('items', function ($itemQuery) use ($like) {
                            $itemQuery->where('quantity', 'like', $like)
                                ->orWhere('cost_price', 'like', $like)
                                ->orWhere('subtotal', 'like', $like)
                                ->orWhereHas('productVariant', function ($variantQuery) use ($like) {
                                    $variantQuery->where('sku', 'like', $like)
                                        ->orWhere('barcode', 'like', $like)
                                        ->orWhere('size', 'like', $like)
                                        ->orWhere('color', 'like', $like)
                                        ->orWhere('cost_price', 'like', $like)
                                        ->orWhere('selling_price', 'like', $like)
                                        ->orWhereHas('product', function ($productQuery) use ($like) {
                                            $productQuery->where('name', 'like', $like)
                                                ->orWhere('description', 'like', $like)
                                                ->orWhere('gender', 'like', $like)
                                                ->orWhere('status', 'like', $like);
                                        });
                                });
                        });

                    // Exact purchase number support: #PO-00003 / PO-00003 / 00003.
                    $purchaseId = ltrim($term, '#');
                    if (preg_match('/^PO-(\d+)$/i', $purchaseId, $matches)) {
                        $q->orWhere('purchases.id', (int) $matches[1]);
                    } elseif (ctype_digit($purchaseId)) {
                        $q->orWhere('purchases.id', (int) $purchaseId);
                    }
                });
            }
        }

        if ($request->filled('status') && in_array($request->input('status'), ['draft', 'received', 'cancelled'], true)) {
            $query->where('purchases.status', $request->input('status'));
        }

        switch ($request->input('sort', 'latest')) {
            case 'oldest':
                $query->orderBy('purchases.purchase_date')->orderBy('purchases.id');
                break;
            case 'highest':
                $query->orderByDesc('purchases.total_amount')->orderByDesc('purchases.id');
                break;
            case 'lowest':
                $query->orderBy('purchases.total_amount')->orderBy('purchases.id');
                break;
            default:
                $query->orderByDesc('purchases.purchase_date')->orderByDesc('purchases.id');
                break;
        }

        $purchases = $query->paginate(10)->withQueryString();

        $stats = [
            'total' => Purchase::count(),
            'draft' => Purchase::where('status', 'draft')->count(),
            'received' => Purchase::where('status', 'received')->count(),
            'cancelled' => Purchase::where('status', 'cancelled')->count(),
            'received_value' => (float) Purchase::where('status', 'received')->sum('total_amount'),
        ];

        $variants = ProductVariant::query()
            ->with(['product'])
            ->where('status', true)
            ->whereHas('product', fn ($query) => $query->where('status', true))
            ->orderBy('product_id')
            ->get();

        return view('admin.purchases.index', compact('purchases', 'stats', 'variants'));
    }

    private function normalizeSearch(string $search): string
    {
        $search = trim($search);

        // Allow friendly labels in the search box without requiring those labels to exist in DB.
        $search = preg_replace('/\b(?:sku|barcode|product|category|size|color|stock|price|supplier|purchase|id)\s*:\s*/i', ' ', $search);
        $search = preg_replace('/\s+/', ' ', $search);

        return trim((string) $search);
    }

    public function create()
    {
        $variants = ProductVariant::query()
            ->with(['product', 'inventory'])
            ->where('status', true)
            ->whereHas('product', fn ($query) => $query->where('status', true))
            ->orderBy('product_id')
            ->get();

        return view('admin.purchases.create', compact('variants'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_name' => ['nullable', 'string', 'max:255'],
            'purchase_date' => ['required', 'date'],
            'status' => ['required', 'in:draft,received,cancelled'],
            'note' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.cost_price' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated) {
            $totalAmount = collect($validated['items'])->sum(
                fn ($item) => $item['quantity'] * $item['cost_price']
            );

            $purchase = Purchase::create([
                'supplier_name' => $validated['supplier_name'] ?? null,
                'purchase_date' => $validated['purchase_date'],
                'total_amount' => $totalAmount,
                'status' => $validated['status'],
                'note' => $validated['note'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $subtotal = $item['quantity'] * $item['cost_price'];

                $purchase->items()->create([
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity' => $item['quantity'],
                    'cost_price' => $item['cost_price'],
                    'subtotal' => $subtotal,
                ]);

                if ($validated['status'] === 'received') {
                    $inventory = Inventory::firstOrCreate(
                        ['product_variant_id' => $item['product_variant_id']],
                        ['quantity' => 0, 'low_stock_threshold' => 5]
                    );

                    $inventory->quantity += $item['quantity'];
                    $inventory->save();

                    InventoryMovement::create([
                        'product_variant_id' => $item['product_variant_id'],
                        'type' => 'in',
                        'quantity' => $item['quantity'],
                        'reference_type' => 'purchase',
                        'reference_id' => $purchase->id,
                        'note' => 'Stock received from purchase',
                    ]);
                }
            }
        });

        return redirect()->route('admin.purchases.index')
            ->with('success', 'Purchase created successfully.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['items.productVariant.product', 'items.productVariant.inventory']);

        return view('admin.purchases.show', compact('purchase'));
    }

    public function receive(Purchase $purchase)
    {
        if ($purchase->status === 'received') {
            return back()->with('error', 'This purchase has already been received.');
        }

        if ($purchase->status === 'cancelled') {
            return back()->with('error', 'Cancelled purchase cannot be received.');
        }

        DB::transaction(function () use ($purchase) {
            $purchase->load('items');

            foreach ($purchase->items as $item) {
                $inventory = Inventory::firstOrCreate(
                    ['product_variant_id' => $item->product_variant_id],
                    ['quantity' => 0, 'low_stock_threshold' => 5]
                );

                $inventory->quantity += $item->quantity;
                $inventory->save();

                InventoryMovement::create([
                    'product_variant_id' => $item->product_variant_id,
                    'type' => 'in',
                    'quantity' => $item->quantity,
                    'reference_type' => 'purchase',
                    'reference_id' => $purchase->id,
                    'note' => 'Stock received from purchase',
                ]);
            }

            $purchase->update(['status' => 'received']);
        });

        return redirect()->route('admin.purchases.show', $purchase->id)
            ->with('success', 'Purchase received successfully. Inventory has been updated.');
    }
}
