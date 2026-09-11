<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::query()
            ->with([
                'items.productVariant.product',
                'items.productVariant.inventory',
            ])
            ->withCount('items');

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('payment_method', 'like', "%{$search}%")
                    ->orWhereHas('items.productVariant.product', function ($productQuery) use ($search) {
                        $productQuery->where('products.name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('sale_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('sale_date', '<=', $request->date_to);
        }

        $sort = $request->get('sort', 'latest');

        match ($sort) {
            'oldest' => $query->orderBy('sale_date')->orderBy('id'),
            'highest' => $query->orderByDesc('total_amount'),
            'lowest' => $query->orderBy('total_amount'),
            default => $query->orderByDesc('sale_date')->orderByDesc('id'),
        };

        $sales = $query->paginate(10)->withQueryString();

        $statsQuery = Sale::query();

        if ($request->filled('date_from')) {
            $statsQuery->whereDate('sale_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $statsQuery->whereDate('sale_date', '<=', $request->date_to);
        }

        $stats = [
            'total_sales' => (float) (clone $statsQuery)
                ->where('status', 'completed')
                ->sum('total_amount'),
            'total_orders' => (int) (clone $statsQuery)
                ->where('status', 'completed')
                ->count(),
            'completed' => (int) (clone $statsQuery)
                ->where('status', 'completed')
                ->count(),
            'cancelled' => (int) (clone $statsQuery)
                ->where('status', 'cancelled')
                ->count(),
        ];

        $customers = User::query()
            ->whereHas('role', function ($query) {
                $query->where('name', 'Customer');
            })
            ->orderByRaw("COALESCE(first_name, name) ASC")
            ->orderBy('last_name')
            ->get();

        $variants = ProductVariant::query()
            ->with(['product', 'inventory'])
            ->where('status', true)
            ->whereHas('product', function ($query) {
                $query->where('status', true);
            })
            ->orderBy('product_id')
            ->orderBy('id')
            ->get();

        return view('admin.transactions.index', compact('sales', 'stats', 'variants', 'customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sale_date' => ['required', 'date'],
            'payment_method' => [
                'required',
                Rule::in([
                    'cash', 'card', 'qr',
                ]),
            ],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.selling_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated) {
            $subtotal = 0;

            foreach ($validated['items'] as $item) {
                $variant = ProductVariant::query()->findOrFail($item['product_variant_id']);
                $subtotal += (int) $item['quantity'] * (float) $variant->selling_price;
            }

            $discount = (float) ($validated['discount'] ?? 0);

            if ($discount > $subtotal) {
                abort(422, 'Discount cannot be greater than the subtotal.');
            }

            $sale = Sale::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'sale_date' => $validated['sale_date'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total_amount' => $subtotal - $discount,
                'payment_method' => $validated['payment_method'],
                'status' => 'completed',
                'note' => $validated['note'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $variant = ProductVariant::query()
                    ->with('product')
                    ->findOrFail($item['product_variant_id']);

                $inventory = Inventory::query()
                    ->where('product_variant_id', $variant->id)
                    ->lockForUpdate()
                    ->first();

                if (! $inventory) {
                    abort(422, 'Inventory is not set for product variant: ' . $variant->product->name);
                }

                if ($inventory->quantity < (int) $item['quantity']) {
                    abort(422, 'Insufficient stock for product variant: ' . $variant->product->name);
                }

                $itemSellingPrice = (float) $variant->selling_price;
                $itemSubtotal = (int) $item['quantity'] * $itemSellingPrice;

                $sale->items()->create([
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity' => $item['quantity'],
                    'selling_price' => $itemSellingPrice,
                    'subtotal' => $itemSubtotal,
                ]);

                $inventory->quantity -= (int) $item['quantity'];
                $inventory->save();

                InventoryMovement::create([
                    'product_variant_id' => $item['product_variant_id'],
                    'type' => 'out',
                    'quantity' => $item['quantity'],
                    'reference_type' => 'sale',
                    'reference_id' => $sale->id,
                    'note' => 'Stock sold',
                ]);
            }
        });

        return redirect()->route('admin.transactions.index')
            ->with('success', 'Transaction created successfully.');
    }

    public function update(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'sale_date' => ['required', 'date'],
            'payment_method' => [
                'required',
                Rule::in([
                    'cash', 'card', 'qr',
                ]),
            ],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:completed,cancelled'],
            'note' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated, $sale) {
            $sale->load('items');
            $oldStatus = $sale->status;
            $newStatus = $validated['status'];

            if ($oldStatus === 'completed' && $newStatus === 'cancelled') {
                foreach ($sale->items as $item) {
                    $inventory = Inventory::query()
                        ->where('product_variant_id', $item->product_variant_id)
                        ->lockForUpdate()->first();

                    if (! $inventory) {
                        abort(422, 'Inventory is not set for product variant ID: ' . $item->product_variant_id);
                    }

                    $inventory->quantity += $item->quantity;
                    $inventory->save();

                    InventoryMovement::create([
                        'product_variant_id' => $item->product_variant_id,
                        'type' => 'in',
                        'quantity' => $item->quantity,
                        'reference_type' => 'sale',
                        'reference_id' => $sale->id,
                        'note' => 'Stock returned from cancelled transaction',
                    ]);
                }
            }

            if ($oldStatus === 'cancelled' && $newStatus === 'completed') {
                foreach ($sale->items as $item) {
                    $inventory = Inventory::query()
                        ->where('product_variant_id', $item->product_variant_id)
                        ->lockForUpdate()->first();

                    if (! $inventory) {
                        abort(422, 'Inventory is not set for product variant ID: ' . $item->product_variant_id);
                    }

                    if ($inventory->quantity < $item->quantity) {
                        abort(422, 'Insufficient stock to complete this transaction.');
                    }

                    $inventory->quantity -= $item->quantity;
                    $inventory->save();

                    InventoryMovement::create([
                        'product_variant_id' => $item->product_variant_id,
                        'type' => 'out',
                        'quantity' => $item->quantity,
                        'reference_type' => 'sale',
                        'reference_id' => $sale->id,
                        'note' => 'Stock sold after transaction reactivation',
                    ]);
                }
            }

            $discount = (float) ($validated['discount'] ?? 0);
            $subtotal = (float) $sale->subtotal;

            if ($discount > $subtotal) {
                abort(422, 'Discount cannot be greater than the subtotal.');
            }

            $sale->update([
                'sale_date' => $validated['sale_date'],
                'payment_method' => $validated['payment_method'],
                'discount' => $discount,
                'total_amount' => $subtotal - $discount,
                'status' => $newStatus,
                'note' => $validated['note'] ?? null,
            ]);
        });

        return redirect()->route('admin.transactions.index')
            ->with('success', 'Transaction updated successfully.');
    }

    public function destroy(Sale $sale)
    {
        if ($sale->payments()->exists()) {
            return redirect()->route('admin.transactions.index')
                ->with('error', 'This transaction cannot be deleted because payment records exist.');
        }

        DB::transaction(function () use ($sale) {
            $sale->load('items');

            if ($sale->status === 'completed') {
                foreach ($sale->items as $item) {
                    $inventory = Inventory::query()
                        ->where('product_variant_id', $item->product_variant_id)
                        ->lockForUpdate()->first();

                    if (! $inventory) {
                        abort(422, 'Inventory is not set for product variant ID: ' . $item->product_variant_id);
                    }

                    $inventory->quantity += $item->quantity;
                    $inventory->save();

                    InventoryMovement::create([
                        'product_variant_id' => $item->product_variant_id,
                        'type' => 'in',
                        'quantity' => $item->quantity,
                        'reference_type' => 'sale',
                        'reference_id' => $sale->id,
                        'note' => 'Stock returned from deleted transaction',
                    ]);
                }
            }

            $sale->items()->delete();
            $sale->delete();
        });

        return redirect()->route('admin.transactions.index')
            ->with('success', 'Transaction deleted successfully.');
    }

    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV-' . now()->format('Ymd') . '-';

        $lastInvoice = Sale::query()
            ->where('invoice_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('invoice_number');

        $nextNumber = 1;

        if ($lastInvoice) {
            $nextNumber = (int) str_replace($prefix, '', $lastInvoice) + 1;
        }

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
