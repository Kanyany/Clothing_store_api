@extends('admin.layouts.app')

@section('title', 'Transactions')

@section('page-title', 'Transactions')

@section('content')

<div class="sales-page">

    {{-- HEADER --}}
    <div class="sales-page-header">
        <div>
            <span class="sales-eyebrow">TRANSACTIONS</span>
            <h1>Transactions</h1>
            <p>Manage sales transactions and transaction history.</p>
        </div>

        <button
            type="button"
            class="sales-primary-button"
            onclick="openSaleDialog('create')"
        >
            <span>＋</span>
            New Transaction
        </button>
    </div>


    {{-- STATS --}}
    <div class="sales-stats-grid">

        <div class="sales-stat sales-stat-sales">
            <span>Total Sales</span>
            <strong>${{ number_format($stats['total_sales'], 2) }}</strong>
            <small>Completed transactions</small>
        </div>

        <div class="sales-stat sales-stat-orders">
            <span>Total Orders</span>
            <strong>{{ number_format($stats['total_orders']) }}</strong>
            <small>Completed orders</small>
        </div>

        <div class="sales-stat sales-stat-completed">
            <span>Completed</span>
            <strong>{{ number_format($stats['completed']) }}</strong>
            <small>Successful transactions</small>
        </div>

        <div class="sales-stat sales-stat-cancelled">
            <span>Cancelled</span>
            <strong>{{ number_format($stats['cancelled']) }}</strong>
            <small>Cancelled transactions</small>
        </div>

    </div>


    {{-- FILTER --}}
    <div class="sales-filter-card">

        <form method="GET" action="{{ route('admin.transactions.index') }}">

            <div class="sales-filter-row">

                <div class="sales-filter-search">
                    <label for="sales-search">Search</label>
                    <input
                        id="sales-search"
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Invoice or product..."
                    >
                </div>

                <div>
                    <label>Status</label>
                    <select name="status">
                        <option value="">All Status</option>
                        <option value="completed" @selected(request('status') === 'completed')>
                            Completed
                        </option>
                        <option value="cancelled" @selected(request('status') === 'cancelled')>
                            Cancelled
                        </option>
                    </select>
                </div>

                <div>
                    <label>Payment</label>
                    <select name="payment_method">
                        <option value="">All Payments</option>

                        @foreach([
                            'cash' => 'Cash',
                            'card' => 'Card',
                            'qr' => 'QR Code',
                        ] as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected(request('payment_method') === $value)
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label>From</label>
                    <input
                        type="date"
                        name="date_from"
                        value="{{ request('date_from') }}"
                    >
                </div>

                <div>
                    <label>To</label>
                    <input
                        type="date"
                        name="date_to"
                        value="{{ request('date_to') }}"
                    >
                </div>

                <div>
                    <label>Sort</label>
                    <select name="sort">
                        <option value="latest" @selected(request('sort', 'latest') === 'latest')>
                            Latest
                        </option>
                        <option value="oldest" @selected(request('sort') === 'oldest')>
                            Oldest
                        </option>
                        <option value="highest" @selected(request('sort') === 'highest')>
                            Highest Total
                        </option>
                        <option value="lowest" @selected(request('sort') === 'lowest')>
                            Lowest Total
                        </option>
                    </select>
                </div>

                <button type="submit" class="sales-filter-button">
                    Search
                </button>

                <a
                    href="{{ route('admin.transactions.index') }}"
                    class="sales-clear-button"
                >
                    Clear
                </a>

            </div>

        </form>

    </div>


    {{-- SALES TABLE --}}
    <div class="sales-card">

        <div class="sales-card-heading">

            <div>
                <h2>Transaction History</h2>
                <p>{{ $sales->total() }} transaction(s) found</p>
            </div>

        </div>

        @if($sales->count() > 0)

            <div class="sales-table-wrap">

                <table class="sales-table">

                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Source</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($sales as $sale)

                            <tr>

                                <td>
                                    <strong class="sales-invoice">
                                        {{ $sale->invoice_number ?: '#'.$sale->id }}
                                    </strong>
                                </td>

                                <td>
                                    {{ $sale->sale_date?->format('d M Y') }}
                                </td>

                                <td>
                                    <span class="sales-customer">—</span>
                                </td>

                                <td>
                                    <strong class="sales-total">
                                        ${{ number_format($sale->total_amount, 2) }}
                                    </strong>
                                </td>

                                <td>
                                    <span class="sales-source">POS</span>
                                </td>

                                <td>
                                    @if($sale->status === 'completed')
                                        <span class="sales-status sales-status-completed">
                                            <i></i> Completed
                                        </span>
                                    @else
                                        <span class="sales-status sales-status-cancelled">
                                            <i></i> Cancelled
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <span class="sales-payment">
                                        {{ match($sale->payment_method) {
                                            'cash' => 'Cash',
                                            'card' => 'Card',
                                            'qr' => 'QR Code',
                                            default => '—',
                                        } }}
                                    </span>
                                </td>

                                <td>
                                    <div class="sales-action">

                                        <button
                                            type="button"
                                            class="sales-action-button"
                                            onclick="toggleSaleMenu({{ $sale->id }}, this)"
                                        >
                                            ⋯
                                        </button>

                                        <div
                                            class="sales-action-menu"
                                            id="sale-menu-{{ $sale->id }}"
                                        >

                                            <button
                                                type="button"
                                                onclick="openSaleDialog('view', {{ $sale->id }})"
                                            >
                                                <span>◉</span>
                                                View Details
                                            </button>

                                            <button
                                                type="button"
                                                onclick="printSaleReceipt({{ $sale->id }})"
                                            >
                                                <span>🖶</span>
                                                Print Receipt
                                            </button>

                                            <button
                                                type="button"
                                                onclick="openSaleDialog('edit', {{ $sale->id }})"
                                            >
                                                <span>✏</span>
                                                Edit
                                            </button>

                                            @if($sale->status === 'cancelled')
                                                <form
                                                    action="{{ route('admin.transactions.update', $sale) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('Reopen this transaction and mark it completed?');"
                                                >
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="sale_date" value="{{ $sale->sale_date?->format('Y-m-d') }}">
                                                    <input type="hidden" name="payment_method" value="{{ $sale->payment_method }}">
                                                    <input type="hidden" name="discount" value="{{ $sale->discount }}">
                                                    <input type="hidden" name="note" value="{{ $sale->note }}">
                                                    <input type="hidden" name="status" value="completed">

                                                    <button type="submit">
                                                        <span>↺</span>
                                                        Reopen Transaction
                                                    </button>
                                                </form>
                                            @endif

                                            <form
                                                action="{{ route('admin.transactions.destroy', $sale) }}"
                                                method="POST"
                                                onsubmit="return openSaleDeleteDialog(this, '{{ addslashes($sale->invoice_number ?: '#'.$sale->id) }}');"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="sales-delete-menu">
                                                    <span>▣</span>
                                                    Delete Record
                                                </button>
                                            </form>

                                        </div>

                                    </div>
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

            <div class="sales-pagination">
                {{ $sales->links() }}
            </div>

        @else

            <div class="sales-empty">
                <div class="sales-empty-icon">S</div>
                <strong>No sales found</strong>
                <p>There are no sales matching your current filters.</p>
            </div>

        @endif

    </div>

</div>


{{-- =========================================================
     CREATE SALE DIALOG
========================================================= --}}

<div class="sale-dialog-backdrop" id="sale-dialog-backdrop">

    <div
        class="sale-dialog sale-create-dialog"
        id="sale-create-dialog"
        role="dialog"
        aria-modal="true"
    >

        <div class="sale-dialog-header">
            <div>
                <span class="sale-dialog-eyebrow">TRANSACTION</span>
                <h2>New Transaction</h2>
                <p>Create a completed sale using products from inventory.</p>
            </div>

            <button
                type="button"
                class="sale-dialog-close"
                onclick="closeSaleDialog()"
            >
                ×
            </button>
        </div>

        <form
            method="POST"
            action="{{ route('admin.transactions.store') }}"
            class="sale-dialog-form"
        >
            @csrf

            <div class="sale-form-grid">

                <div>
                    <label>Sale Date <span>*</span></label>
                    <input
                        type="date"
                        name="sale_date"
                        value="{{ old('sale_date', now()->format('Y-m-d')) }}"
                        required
                    >
                </div>

                <div>
                    <label>Customer</label>
                    <select name="customer_name">
                        <option value="">Walk-in Customer</option>
                        @foreach($customers as $customer)
                            @php
                                $customerDisplayName = trim(($customer->first_name ?? '') . ' ' . ($customer->last_name ?? ''));
                                if ($customerDisplayName === '') {
                                    $customerDisplayName = $customer->name;
                                }
                            @endphp
                            <option value="{{ $customerDisplayName }}">{{ $customerDisplayName }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label>Payment Method <span>*</span></label>
                    <select name="payment_method" required>
                        <option value="">Select payment</option>
                        @foreach([
                            'cash' => 'Cash',
                            'card' => 'Card',
                            'qr' => 'QR Code',
                        ] as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected(old('payment_method') === $value)
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="sale-field-full">
                    <label>Note</label>
                    <textarea
                        name="note"
                        rows="2"
                        placeholder="Optional note..."
                    >{{ old('note') }}</textarea>
                </div>

            </div>

            <div class="sale-items-heading">
                <h3>Sale Items</h3>
                <button
                    type="button"
                    class="sale-add-item-button"
                    onclick="addSaleItem()"
                >
                    + Add Item
                </button>
            </div>

            <div id="sale-items-container"></div>

            <div class="sale-total-box">

                <div>
                    <span>Subtotal</span>
                    <strong id="sale-subtotal">$0.00</strong>
                </div>

                <div>
                    <label for="sale-discount">Discount</label>
                    <input
                        id="sale-discount"
                        type="number"
                        name="discount"
                        value="{{ old('discount', 0) }}"
                        min="0"
                        step="0.01"
                        oninput="calculateSaleTotal()"
                    >
                </div>

                <div class="sale-grand-total">
                    <span>Total</span>
                    <strong id="sale-total">$0.00</strong>
                </div>

            </div>

            @if($errors->any())
                <div class="sale-validation-errors">
                    <strong>Please check the form:</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="sale-dialog-footer">

                <button
                    type="button"
                    class="sale-secondary-button"
                    onclick="closeSaleDialog()"
                >
                    Cancel
                </button>

                <button type="submit" class="sale-primary-dialog-button">
                    Save Sale
                </button>

            </div>

        </form>

    </div>


    {{-- =========================================================
         VIEW + EDIT DIALOGS
    ========================================================= --}}

    @foreach($sales as $sale)

        {{-- VIEW --}}
        <div
            class="sale-dialog sale-view-dialog"
            id="sale-view-dialog-{{ $sale->id }}"
            role="dialog"
            aria-modal="true"
        >

            <div class="sale-dialog-header">
                <div>
                    <span class="sale-dialog-eyebrow">SALE DETAILS</span>
                    <h2>{{ $sale->invoice_number ?: '#'.$sale->id }}</h2>
                    <p>Transaction information and sold items.</p>
                </div>

                <button
                    type="button"
                    class="sale-dialog-close"
                    onclick="closeSaleDialog()"
                >
                    ×
                </button>
            </div>

            <div class="sale-view-content">

                <div class="sale-view-summary">

                    <div>
                        <span>Date</span>
                        <strong>{{ $sale->sale_date?->format('d M Y') }}</strong>
                    </div>

                    <div>
                        <span>Customer</span>
                        <strong>—</strong>
                    </div>

                    <div>
                        <span>Payment</span>
                        <strong>
                            {{ str_replace('_', ' ', ucfirst($sale->payment_method ?? '—')) }}
                        </strong>
                    </div>

                    <div>
                        <span>Status</span>
                        <strong class="{{ $sale->status === 'completed' ? 'view-success' : 'view-error' }}">
                            {{ ucfirst($sale->status) }}
                        </strong>
                    </div>

                    <div>
                        <span>Total</span>
                        <strong class="view-total">
                            ${{ number_format($sale->total_amount, 2) }}
                        </strong>
                    </div>

                </div>

                <div class="sale-view-items">

                    <h3>Products</h3>

                    @foreach($sale->items as $item)

                        <div class="sale-view-item">

                            <div>
                                <strong>
                                    {{ $item->productVariant?->product?->name ?? 'Product' }}
                                </strong>

                                <span>
                                    {{ $item->productVariant?->size ?: '—' }}
                                    /
                                    {{ $item->productVariant?->color ?: '—' }}
                                    · Qty {{ $item->quantity }}
                                </span>
                            </div>

                            <strong>
                                ${{ number_format($item->subtotal, 2) }}
                            </strong>

                        </div>

                    @endforeach

                </div>

                @if($sale->note)
                    <div class="sale-view-note">
                        <span>Note</span>
                        <p>{{ $sale->note }}</p>
                    </div>
                @endif

                <div class="sale-view-totals">

                    <div>
                        <span>Subtotal</span>
                        <strong>${{ number_format($sale->subtotal, 2) }}</strong>
                    </div>

                    <div>
                        <span>Discount</span>
                        <strong>${{ number_format($sale->discount, 2) }}</strong>
                    </div>

                    <div class="sale-view-grand">
                        <span>Total</span>
                        <strong>${{ number_format($sale->total_amount, 2) }}</strong>
                    </div>

                </div>

            </div>

        </div>


        {{-- EDIT --}}
        <div
            class="sale-dialog sale-edit-dialog"
            id="sale-edit-dialog-{{ $sale->id }}"
            role="dialog"
            aria-modal="true"
        >

            <div class="sale-dialog-header">
                <div>
                    <span class="sale-dialog-eyebrow">TRANSACTION</span>
                    <h2>Edit Sale</h2>
                    <p>{{ $sale->invoice_number ?: '#'.$sale->id }}</p>
                </div>

                <button
                    type="button"
                    class="sale-dialog-close"
                    onclick="closeSaleDialog()"
                >
                    ×
                </button>
            </div>

            <form
                method="POST"
                action="{{ route('admin.transactions.update', $sale) }}"
                class="sale-dialog-form"
            >
                @csrf
                @method('PUT')

                <div class="sale-form-grid">

                    <div>
                        <label>Sale Date <span>*</span></label>
                        <input
                            type="date"
                            name="sale_date"
                            value="{{ $sale->sale_date?->format('Y-m-d') }}"
                            required
                        >
                    </div>

                    <div>
                        <label>Payment Method <span>*</span></label>
                        <select name="payment_method" required>
                            @foreach([
                                'cash' => 'Cash',
                            'card' => 'Card',
                            'qr' => 'QR Code',
                            ] as $value => $label)
                                <option
                                    value="{{ $value }}"
                                    @selected($sale->payment_method === $value)
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label>Status <span>*</span></label>
                        <select name="status" required>
                            <option
                                value="completed"
                                @selected($sale->status === 'completed')
                            >
                                Completed
                            </option>

                            <option
                                value="cancelled"
                                @selected($sale->status === 'cancelled')
                            >
                                Cancelled
                            </option>
                        </select>
                    </div>

                    <div>
                        <label>Discount</label>
                        <input
                            type="number"
                            name="discount"
                            value="{{ $sale->discount }}"
                            min="0"
                            step="0.01"
                        >
                    </div>

                    <div class="sale-field-full">
                        <label>Note</label>
                        <textarea
                            name="note"
                            rows="3"
                        >{{ $sale->note }}</textarea>
                    </div>

                </div>

                <div class="sale-edit-info">
                    <span>Subtotal</span>
                    <strong>${{ number_format($sale->subtotal, 2) }}</strong>
                    <span>Current Total</span>
                    <strong>${{ number_format($sale->total_amount, 2) }}</strong>
                </div>

                <div class="sale-dialog-footer">

                    <button
                        type="button"
                        class="sale-secondary-button"
                        onclick="closeSaleDialog()"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="sale-primary-dialog-button"
                    >
                        Save Changes
                    </button>

                </div>

            </form>

        </div>

    @endforeach


    {{-- DELETE --}}
    <div
        class="sale-delete-dialog"
        id="sale-delete-dialog"
        role="dialog"
        aria-modal="true"
    >

        <div class="sale-delete-icon">!</div>

        <h2>Delete Transaction?</h2>

        <p>
            Are you sure you want to delete
            <strong id="sale-delete-name"></strong>?
            The sale will be removed and completed-sale stock will be returned.
        </p>

        <div class="sale-delete-actions">

            <button
                type="button"
                class="sale-secondary-button"
                onclick="closeSaleDialog()"
            >
                Cancel
            </button>

            <button
                type="button"
                class="sale-delete-confirm"
                onclick="confirmSaleDelete()"
            >
                Delete Transaction
            </button>

        </div>

    </div>

</div>

{{-- =========================================================
     VARIANTS DATA + SALES JAVASCRIPT
========================================================= --}}

@php
    $saleVariants = $variants->map(function ($variant) {
        return [
            'id' => $variant->id,
            'name' => $variant->product?->name ?? 'Product',
            'size' => $variant->size,
            'color' => $variant->color,
            'selling_price' => (float) $variant->selling_price,
            'stock' => (int) ($variant->inventory?->quantity ?? 0),
        ];
    })->values()->all();
@endphp

<script>
    const saleVariants = @json($saleVariants);

    let saleItemIndex = 0;
    let saleDeleteForm = null;

    function openSaleDialog(type, saleId = null) {
        const backdrop = document.getElementById('sale-dialog-backdrop');

        if (!backdrop) {
            return;
        }

        closeAllSaleMenus();

        document.querySelectorAll(
            '.sale-dialog, .sale-delete-dialog'
        ).forEach(function (dialog) {
            dialog.classList.remove('show');
        });

        if (type === 'create') {
            const dialog = document.getElementById('sale-create-dialog');

            if (dialog) {
                dialog.classList.add('show');

                const container =
                    document.getElementById('sale-items-container');

                if (container && container.children.length === 0) {
                    addSaleItem();
                }

                calculateSaleTotal();
            }
        }

        if (type === 'view' && saleId) {
            const dialog = document.getElementById(
                'sale-view-dialog-' + saleId
            );

            if (dialog) {
                dialog.classList.add('show');
            }
        }

        if (type === 'edit' && saleId) {
            const dialog = document.getElementById(
                'sale-edit-dialog-' + saleId
            );

            if (dialog) {
                dialog.classList.add('show');
            }
        }

        backdrop.classList.add('show');
        document.body.classList.add('sale-dialog-open');
    }

    function closeSaleDialog() {
        const backdrop =
            document.getElementById('sale-dialog-backdrop');

        if (!backdrop) {
            return;
        }

        document.querySelectorAll(
            '.sale-dialog, .sale-delete-dialog'
        ).forEach(function (dialog) {
            dialog.classList.remove('show');
        });

        backdrop.classList.remove('show');
        document.body.classList.remove('sale-dialog-open');

        saleDeleteForm = null;
    }

    function addSaleItem() {
        const container =
            document.getElementById('sale-items-container');

        if (!container) {
            return;
        }

        const row = document.createElement('div');

        row.className = 'sale-item-row';
        row.dataset.index = saleItemIndex;

        let options =
            '<option value="">Select product / variant</option>';

        saleVariants.forEach(function (variant) {
            const label =
                variant.name +
                ' — ' +
                (variant.size || '—') +
                ' / ' +
                (variant.color || '—') +
                ' · Stock ' +
                variant.stock;

            options +=
                '<option value="' + variant.id + '">' +
                escapeHtml(label) +
                '</option>';
        });

        row.innerHTML = `
            <div class="sale-item-product">
                <label>Product / Variant</label>

                <select
                    name="items[${saleItemIndex}][product_variant_id]"
                    onchange="setSaleVariant(this)"
                    required
                >
                    ${options}
                </select>
            </div>

            <div>
                <label>Quantity</label>

                <input
                    type="number"
                    name="items[${saleItemIndex}][quantity]"
                    value="1"
                    min="1"
                    step="1"
                    oninput="calculateSaleTotal()"
                    required
                >
            </div>

            <div>
                <label>Selling Price</label>

                <input
                    type="number"
                    name="items[${saleItemIndex}][selling_price]"
                    value="0"
                    min="0"
                    step="0.01"
                    oninput="calculateSaleTotal()"
                    required
                >
            </div>

            <div class="sale-item-subtotal">
                <label>Subtotal</label>
                <strong>$0.00</strong>
            </div>

            <button
                type="button"
                class="sale-remove-item"
                onclick="removeSaleItem(this)"
                title="Remove item"
            >
                ×
            </button>
        `;

        container.appendChild(row);

        saleItemIndex++;
    }

    function setSaleVariant(select) {
        const row = select.closest('.sale-item-row');

        if (!row) {
            return;
        }

        const variantId = Number(select.value);

        const variant = saleVariants.find(function (item) {
            return Number(item.id) === variantId;
        });

        const priceInput =
            row.querySelector(
                'input[name*="[selling_price]"]'
            );

        const quantityInput =
            row.querySelector(
                'input[name*="[quantity]"]'
            );

        if (!variant) {
            if (priceInput) {
                priceInput.value = '0';
            }

            if (quantityInput) {
                quantityInput.removeAttribute('max');
            }

            calculateSaleTotal();
            return;
        }

        if (priceInput) {
            priceInput.value =
                Number(variant.selling_price).toFixed(2);
        }

        if (quantityInput) {
            quantityInput.max = variant.stock;
        }

        calculateSaleTotal();
    }

    function removeSaleItem(button) {
        const row = button.closest('.sale-item-row');

        if (!row) {
            return;
        }

        row.remove();

        calculateSaleTotal();
    }

    function calculateSaleTotal() {
        const rows =
            document.querySelectorAll('.sale-item-row');

        let subtotal = 0;

        rows.forEach(function (row) {
            const quantity =
                Number(
                    row.querySelector(
                        'input[name*="[quantity]"]'
                    )?.value || 0
                );

            const price =
                Number(
                    row.querySelector(
                        'input[name*="[selling_price]"]'
                    )?.value || 0
                );

            const rowSubtotal =
                quantity * price;

            subtotal += rowSubtotal;

            const subtotalElement =
                row.querySelector(
                    '.sale-item-subtotal strong'
                );

            if (subtotalElement) {
                subtotalElement.textContent =
                    '$' + rowSubtotal.toFixed(2);
            }
        });

        const discount =
            Number(
                document.getElementById(
                    'sale-discount'
                )?.value || 0
            );

        const total =
            Math.max(0, subtotal - discount);

        const subtotalElement =
            document.getElementById('sale-subtotal');

        const totalElement =
            document.getElementById('sale-total');

        if (subtotalElement) {
            subtotalElement.textContent =
                '$' + subtotal.toFixed(2);
        }

        if (totalElement) {
            totalElement.textContent =
                '$' + total.toFixed(2);
        }
    }

    function openSaleDeleteDialog(form, invoice) {
        saleDeleteForm = form;

        const backdrop =
            document.getElementById(
                'sale-dialog-backdrop'
            );

        const dialog =
            document.getElementById(
                'sale-delete-dialog'
            );

        const name =
            document.getElementById(
                'sale-delete-name'
            );

        if (!backdrop || !dialog || !name) {
            return true;
        }

        closeAllSaleMenus();

        name.textContent = invoice;

        document.querySelectorAll(
            '.sale-dialog, .sale-delete-dialog'
        ).forEach(function (item) {
            item.classList.remove('show');
        });

        dialog.classList.add('show');
        backdrop.classList.add('show');

        document.body.classList.add(
            'sale-dialog-open'
        );

        return false;
    }

    function confirmSaleDelete() {
        if (saleDeleteForm) {
            saleDeleteForm.submit();
        }
    }

    function toggleSaleMenu(id, button) {
        const menu =
            document.getElementById(
                'sale-menu-' + id
            );

        if (!menu) {
            return;
        }

        const wasOpen = menu.classList.contains('show');

        closeAllSaleMenus();

        if (wasOpen) {
            return;
        }

        // Move the menu to <body> so it escapes any ancestor with
        // overflow:hidden/auto (e.g. the scrollable table wrapper),
        // which is why the menu used to get clipped/invisible.
        if (menu.parentElement !== document.body) {
            if (!menu.dataset.homeId) {
                const home = menu.parentElement;
                home.id = home.id || ('sale-menu-home-' + id);
                menu.dataset.homeId = home.id;
            }
            document.body.appendChild(menu);
        }

        positionSaleMenu(menu, button);

        menu.classList.add('show');
    }

    function positionSaleMenu(menu, button) {
        if (!button) {
            return;
        }

        const rect = button.getBoundingClientRect();
        const menuWidth = menu.offsetWidth || 175;

        let left = rect.right - menuWidth;
        left = Math.max(8, Math.min(left, window.innerWidth - menuWidth - 8));

        let top = rect.bottom + 6;
        const menuHeight = menu.offsetHeight || 160;

        if (top + menuHeight > window.innerHeight - 8) {
            top = rect.top - menuHeight - 6;
        }

        menu.style.position = 'fixed';
        menu.style.top = top + 'px';
        menu.style.left = left + 'px';
        menu.style.right = 'auto';
    }

    function closeAllSaleMenus() {
        document.querySelectorAll(
            '.sales-action-menu'
        ).forEach(function (menu) {
            menu.classList.remove('show');
        });
    }

    function printSaleReceipt(id) {
        openSaleDialog('view', id);

        // Give the dialog a tick to render before printing.
        setTimeout(function () {
            window.print();
        }, 150);
    }

    function escapeHtml(value) {
        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    document.addEventListener(
        'click',
        function (event) {
            if (
                !event.target.closest('.sales-action') &&
                !event.target.closest('.sales-action-menu')
            ) {
                closeAllSaleMenus();
            }
        }
    );

    window.addEventListener('scroll', closeAllSaleMenus, true);
    window.addEventListener('resize', closeAllSaleMenus);

    document.addEventListener(
        'DOMContentLoaded',
        function () {
            const backdrop =
                document.getElementById('sale-dialog-backdrop');

            if (backdrop) {
                backdrop.addEventListener(
                    'click',
                    function (event) {
                        if (event.target === this) {
                            closeSaleDialog();
                        }
                    }
                );
            }

            @if($errors->any())
                openSaleDialog('create');
            @endif
        }
    );

    document.addEventListener(
        'keydown',
        function (event) {
            if (event.key === 'Escape') {
                closeSaleDialog();
            }
        }
    );
</script>

@endsection

@push('styles')

<style>

:root {
    --sales-dark: #2C1E17;
    --sales-brown: #6F4E37;
    --sales-warm: #A66A44;
    --sales-light: #C99A6B;
    --sales-cream: #F5EDE3;
    --sales-bg: #FAF8F5;
    --sales-white: #FFFFFF;
    --sales-text: #24201E;
    --sales-muted: #8B8580;
    --sales-success: #64DD17;
    --sales-error: #ff0000;
    --sales-border: rgba(44, 30, 23, .09);
}

.sales-page {
    max-width: 1500px;
    margin: 0 auto;
    padding: 24px 28px 40px;
    color: var(--sales-text);
}

.sales-page * {
    box-sizing: border-box;
}

.sales-page-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 18px;
}

.sales-eyebrow,
.sale-dialog-eyebrow {
    display: block;
    margin-bottom: 6px;
    color: var(--sales-warm);
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.2px;
}

.sales-page-header h1 {
    margin: 0;
    font-size: 25px;
    line-height: 1.2;
    font-weight: 800;
}

.sales-page-header p {
    margin: 5px 0 0;
    color: var(--sales-muted);
    font-size: 12px;
}

.sales-primary-button,
.sales-primary-dialog-button {
    min-height: 43px;
    padding: 0 17px;
    border: 0;
    border-radius: 10px;
    background: var(--sales-brown);
    color: #FFFFFF;
    font-family: inherit;
    font-size: 13px;
    font-weight: 800;
    cursor: pointer;
}

.sales-primary-button:hover,
.sales-primary-dialog-button:hover {
    background: var(--sales-dark);
}

.sales-primary-button span {
    margin-right: 5px;
    font-size: 16px;
}

.sales-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 13px;
    margin-bottom: 15px;
}

.sales-stat {
    min-height: 130px;
    padding: 18px;
    border: 1px solid var(--sales-border);
    border-radius: 14px;
    background: var(--sales-white);
    box-shadow: 0 5px 20px rgba(44, 30, 23, .035);
}

.sales-stat > span {
    display: block;
    color: var(--sales-muted);
    font-size: 11px;
    font-weight: 700;
}

.sales-stat strong {
    display: block;
    margin-top: 13px;
    color: var(--sales-dark);
    font-size: 24px;
    font-weight: 800;
}

.sales-stat small {
    display: block;
    margin-top: 5px;
    color: var(--sales-muted);
    font-size: 10px;
}

.sales-stat-sales {
    border-top: 3px solid var(--sales-brown);
}

.sales-stat-sales strong {
    color: var(--sales-brown);
}

.sales-stat-orders {
    border-top: 3px solid var(--sales-light);
}

.sales-stat-completed {
    border-top: 3px solid var(--sales-success);
}

.sales-stat-completed strong {
    color: var(--sales-success);
}

.sales-stat-cancelled {
    border-top: 3px solid var(--sales-error);
}

.sales-stat-cancelled strong {
    color: var(--sales-error);
}

.sales-filter-card,
.sales-card {
    margin-bottom: 15px;
    border: 1px solid var(--sales-border);
    border-radius: 15px;
    background: var(--sales-white);
    box-shadow: 0 5px 20px rgba(44, 30, 23, .035);
}

.sales-filter-card {
    padding: 16px;
}

.sales-filter-row {
    display: grid;
    grid-template-columns: minmax(180px, 1.5fr) repeat(5, minmax(105px, 1fr)) auto auto;
    align-items: end;
    gap: 9px;
}

.sales-filter-row label {
    display: block;
    margin-bottom: 5px;
    color: var(--sales-muted);
    font-size: 10px;
    font-weight: 700;
}

.sales-filter-row input,
.sales-filter-row select {
    width: 100%;
    height: 40px;
    padding: 0 10px;
    border: 1px solid var(--sales-border);
    border-radius: 9px;
    background: var(--sales-bg);
    color: var(--sales-text);
    font-family: inherit;
    font-size: 12px;
    outline: none;
}

.sales-filter-row input:focus,
.sales-filter-row select:focus {
    border-color: var(--sales-warm);
    background: #FFFFFF;
}

.sales-filter-button,
.sales-clear-button {
    height: 40px;
    padding: 0 13px;
    border-radius: 9px;
    font-family: inherit;
    font-size: 11px;
    font-weight: 800;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.sales-filter-button {
    border: 0;
    background: var(--sales-brown);
    color: #FFFFFF;
}

.sales-clear-button {
    border: 1px solid var(--sales-border);
    background: #FFFFFF;
    color: var(--sales-brown);
}

.sales-card-heading {
    padding: 18px 20px;
    border-bottom: 1px solid var(--sales-border);
}

.sales-card-heading h2 {
    margin: 0;
    font-size: 17px;
    font-weight: 800;
}

.sales-card-heading p {
    margin: 4px 0 0;
    color: var(--sales-muted);
    font-size: 11px;
}

.sales-table-wrap {
    width: 100%;
    overflow-x: auto;
}

.sales-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 980px;
}

.sales-table th {
    padding: 12px 16px;
    background: var(--sales-bg);
    color: var(--sales-muted);
    font-size: 10px;
    font-weight: 800;
    text-align: left;
    text-transform: uppercase;
    letter-spacing: .45px;
}

.sales-table td {
    padding: 14px 16px;
    border-bottom: 1px solid var(--sales-border);
    color: var(--sales-muted);
    font-size: 12px;
}

.sales-table tbody tr:hover {
    background: #FCFAF8;
}

.sales-invoice {
    color: var(--sales-text);
    font-size: 12px;
    font-weight: 800;
}

.sales-total {
    color: var(--sales-brown);
    font-size: 13px;
}

.sales-customer,
.sales-source {
    color: var(--sales-text);
    font-size: 11px;
    font-weight: 600;
}

.sales-source {
    color: var(--sales-muted);
}

.sales-payment {
    display: inline-flex;
    padding: 5px 8px;
    border-radius: 7px;
    background: var(--sales-cream);
    color: var(--sales-brown);
    font-size: 10px;
    font-weight: 700;
    text-transform: capitalize;
}

.sales-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 8px;
    border-radius: 7px;
    font-size: 10px;
    font-weight: 800;
}

.sales-status i {
    width: 6px;
    height: 6px;
    border-radius: 50%;
}

.sales-status-completed {
    background: #EEF5EC;
    color: var(--sales-success);
}

.sales-status-completed i {
    background: var(--sales-success);
}

.sales-status-cancelled {
    background: #FCEFEB;
    color: var(--sales-error);
}

.sales-status-cancelled i {
    background: var(--sales-error);
}

.sales-action {
    position: relative;
}

.sales-action-button {
    width: 36px;
    height: 36px;
    border: 1px solid var(--sales-border);
    border-radius: 9px;
    background: #FFFFFF;
    color: var(--sales-brown);
    font-size: 19px;
    line-height: 1;
    cursor: pointer;
}

.sales-action-button:hover {
    background: var(--sales-cream);
}

.sales-action-menu {
    position: fixed;
    z-index: 10050;
    display: none;
    width: 175px;
    padding: 5px;
    border: 1px solid var(--sales-border);
    border-radius: 10px;
    background: #FFFFFF;
    box-shadow: 0 12px 30px rgba(44, 30, 23, .14);
}

.sales-action-menu.show {
    display: block;
}

.sales-action-menu button {
    width: 100%;
    padding: 9px 10px;
    border: 0;
    border-radius: 7px;
    background: transparent;
    color: var(--sales-text);
    font-family: inherit;
    font-size: 11px;
    font-weight: 700;
    text-align: left;
    cursor: pointer;
}

.sales-action-menu button:hover {
    background: var(--sales-bg);
}

.sales-action-menu button span {
    display: inline-block;
    width: 20px;
    color: var(--sales-brown);
}

.sales-action-menu .sales-delete-menu {
    color: var(--sales-error);
}

.sales-action-menu .sales-delete-menu span {
    color: var(--sales-error);
}

.sales-pagination {
    padding: 13px 18px;
}

.sales-empty {
    min-height: 270px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
}

.sales-empty-icon {
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
    border-radius: 12px;
    background: var(--sales-cream);
    color: var(--sales-brown);
    font-size: 15px;
    font-weight: 900;
}

.sales-empty strong {
    font-size: 14px;
}

.sales-empty p {
    margin: 5px 0 0;
    color: var(--sales-muted);
    font-size: 11px;
}


/* =========================================================
   DIALOG
========================================================= */

.sale-dialog-backdrop {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 18px;
    background: rgba(36, 32, 30, .58);
    backdrop-filter: blur(4px);
}

.sale-dialog-backdrop.show {
    display: flex;
}

.sale-dialog {
    display: none;
    width: min(760px, 100%);
    max-height: calc(100vh - 36px);
    overflow-y: auto;
    border-radius: 17px;
    border: 1px solid var(--sales-border);
    background: #FFFFFF;
    box-shadow: 0 25px 70px rgba(44, 30, 23, .25);
}

.sale-dialog.show {
    display: block;
}

.sale-dialog-header {
    position: sticky;
    top: 0;
    z-index: 2;
    display: flex;
    justify-content: space-between;
    gap: 15px;
    padding: 21px 23px 17px;
    border-bottom: 1px solid var(--sales-border);
    background: #FFFFFF;
}

.sale-dialog-header h2 {
    margin: 0;
    color: var(--sales-text);
    font-size: 21px;
    font-weight: 800;
}

.sale-dialog-header p {
    margin: 4px 0 0;
    color: var(--sales-muted);
    font-size: 11px;
}

.sale-dialog-close {
    width: 37px;
    height: 37px;
    flex: 0 0 37px;
    border: 1px solid var(--sales-border);
    border-radius: 9px;
    background: var(--sales-bg);
    color: var(--sales-brown);
    font-size: 22px;
    cursor: pointer;
}

.sale-dialog-form {
    padding: 20px 23px 23px;
}

.sale-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 13px;
}

.sale-form-grid label,
.sale-item-row label,
.sale-total-box label {
    display: block;
    margin-bottom: 5px;
    color: var(--sales-text);
    font-size: 11px;
    font-weight: 750;
}

.sale-form-grid label span {
    color: var(--sales-error);
}

.sale-form-grid input,
.sale-form-grid select,
.sale-form-grid textarea,
.sale-item-row input,
.sale-item-row select,
.sale-total-box input {
    width: 100%;
    box-sizing: border-box;
    border: 1px solid var(--sales-border);
    border-radius: 9px;
    background: var(--sales-bg);
    color: var(--sales-text);
    font-family: inherit;
    font-size: 12px;
    outline: none;
}

.sale-form-grid input,
.sale-form-grid select,
.sale-item-row input,
.sale-item-row select,
.sale-total-box input {
    height: 41px;
    padding: 0 10px;
}

.sale-form-grid textarea {
    min-height: 76px;
    padding: 10px;
    resize: vertical;
}

.sale-form-grid input:focus,
.sale-form-grid select:focus,
.sale-form-grid textarea:focus,
.sale-item-row input:focus,
.sale-item-row select:focus,
.sale-total-box input:focus {
    border-color: var(--sales-warm);
    background: #FFFFFF;
    box-shadow: 0 0 0 3px rgba(166, 106, 68, .08);
}

.sale-field-full {
    grid-column: 1 / -1;
}

.sale-items-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin: 20px 0 10px;
}

.sale-items-heading h3 {
    margin: 0;
    font-size: 15px;
    font-weight: 800;
}

.sale-add-item-button {
    height: 34px;
    padding: 0 11px;
    border: 1px solid var(--sales-border);
    border-radius: 8px;
    background: var(--sales-cream);
    color: var(--sales-brown);
    font-family: inherit;
    font-size: 10px;
    font-weight: 800;
    cursor: pointer;
}

.sale-item-row {
    position: relative;
    display: grid;
    grid-template-columns: minmax(260px, 2.2fr) 90px 110px 100px 32px;
    align-items: end;
    gap: 9px;
    margin-bottom: 9px;
    padding: 12px;
    border: 1px solid var(--sales-border);
    border-radius: 10px;
    background: var(--sales-bg);
}

.sale-item-product {
    min-width: 0;
}

.sale-item-subtotal strong {
    display: block;
    height: 41px;
    padding-top: 12px;
    color: var(--sales-brown);
    font-size: 12px;
}

.sale-remove-item {
    width: 32px;
    height: 32px;
    border: 0;
    border-radius: 8px;
    background: #FCEFEB;
    color: var(--sales-error);
    font-size: 17px;
    cursor: pointer;
}

.sale-total-box {
    display: grid;
    grid-template-columns: 1fr 150px 1fr;
    align-items: end;
    gap: 15px;
    margin-top: 15px;
    padding: 15px;
    border-radius: 11px;
    background: var(--sales-cream);
}

.sale-total-box > div {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.sale-total-box span {
    color: var(--sales-muted);
    font-size: 10px;
}

.sale-total-box > div > strong {
    color: var(--sales-brown);
    font-size: 17px;
    font-weight: 800;
}

.sale-grand-total {
    text-align: right;
}

.sale-grand-total strong {
    color: var(--sales-dark) !important;
    font-size: 22px !important;
}

.sale-validation-errors {
    margin-top: 13px;
    padding: 11px 13px;
    border-radius: 9px;
    background: #FCEFEB;
    color: var(--sales-error);
    font-size: 11px;
}

.sale-validation-errors ul {
    margin: 6px 0 0 17px;
}

.sale-dialog-footer {
    display: flex;
    justify-content: flex-end;
    gap: 9px;
    margin-top: 18px;
}

.sale-secondary-button,
.sale-delete-confirm {
    min-height: 41px;
    padding: 0 15px;
    border-radius: 9px;
    font-family: inherit;
    font-size: 11px;
    font-weight: 800;
    cursor: pointer;
}

.sale-secondary-button {
    border: 1px solid var(--sales-border);
    background: #FFFFFF;
    color: var(--sales-brown);
}

.sale-secondary-button:hover {
    background: var(--sales-bg);
}

.sale-edit-info {
    display: flex;
    align-items: center;
    gap: 9px;
    margin-top: 14px;
    padding: 12px;
    border-radius: 9px;
    background: var(--sales-bg);
    color: var(--sales-muted);
    font-size: 10px;
}

.sale-edit-info strong {
    color: var(--sales-brown);
    margin-right: 10px;
}

.sale-view-content {
    padding: 20px 23px 23px;
}

.sale-view-summary {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 9px;
    margin-bottom: 18px;
}

.sale-view-summary > div {
    padding: 12px;
    border-radius: 9px;
    background: var(--sales-bg);
}

.sale-view-summary span {
    display: block;
    margin-bottom: 5px;
    color: var(--sales-muted);
    font-size: 9px;
    font-weight: 700;
}

.sale-view-summary strong {
    color: var(--sales-text);
    font-size: 12px;
    font-weight: 800;
}

.view-success {
    color: var(--sales-success) !important;
}

.view-error {
    color: var(--sales-error) !important;
}

.view-total {
    color: var(--sales-brown) !important;
}

.sale-view-items h3 {
    margin: 0 0 8px;
    font-size: 14px;
    font-weight: 800;
}

.sale-view-item {
    display: flex;
    justify-content: space-between;
    gap: 15px;
    padding: 11px 0;
    border-bottom: 1px solid var(--sales-border);
}

.sale-view-item div {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.sale-view-item strong {
    color: var(--sales-text);
    font-size: 12px;
}

.sale-view-item span {
    color: var(--sales-muted);
    font-size: 10px;
}

.sale-view-item > strong {
    color: var(--sales-brown);
}

.sale-view-note {
    margin-top: 14px;
    padding: 12px;
    border-radius: 9px;
    background: var(--sales-bg);
}

.sale-view-note span {
    color: var(--sales-muted);
    font-size: 10px;
    font-weight: 800;
}

.sale-view-note p {
    margin: 5px 0 0;
    color: var(--sales-text);
    font-size: 11px;
}

.sale-view-totals {
    width: min(300px, 100%);
    margin: 17px 0 0 auto;
}

.sale-view-totals > div {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    padding: 7px 0;
    color: var(--sales-muted);
    font-size: 11px;
}

.sale-view-totals strong {
    color: var(--sales-text);
}

.sale-view-grand {
    margin-top: 4px;
    padding-top: 11px !important;
    border-top: 1px solid var(--sales-border);
    color: var(--sales-text) !important;
    font-size: 13px !important;
    font-weight: 800;
}

.sale-view-grand strong {
    color: var(--sales-brown) !important;
    font-size: 17px;
}


/* =========================================================
   DELETE DIALOG
========================================================= */

.sale-delete-dialog {
    display: none;
    width: min(420px, 100%);
    padding: 27px;
    border-radius: 17px;
    background: #FFFFFF;
    box-shadow: 0 25px 70px rgba(44, 30, 23, .25);
    text-align: center;
}

.sale-delete-dialog.show {
    display: block;
}

.sale-delete-icon {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 13px;
    border-radius: 50%;
    background: #FCEFEB;
    color: var(--sales-error);
    font-size: 22px;
    font-weight: 900;
}

.sale-delete-dialog h2 {
    margin: 0;
    color: var(--sales-text);
    font-size: 20px;
    font-weight: 800;
}

.sale-delete-dialog p {
    margin: 8px 0 20px;
    color: var(--sales-muted);
    font-size: 11px;
    line-height: 1.6;
}

.sale-delete-dialog p strong {
    color: var(--sales-text);
}

.sale-delete-actions {
    display: flex;
    justify-content: center;
    gap: 9px;
}

.sale-delete-confirm {
    border: 0;
    background: var(--sales-error);
    color: #FFFFFF;
}

.sale-delete-confirm:hover {
    background: #A94738;
}

body.sale-dialog-open {
    overflow: hidden;
}

@media print {
    body * {
        visibility: hidden;
    }

    .sale-view-dialog.show,
    .sale-view-dialog.show * {
        visibility: visible;
    }

    .sale-view-dialog.show {
        position: absolute;
        inset: 0;
        width: 100%;
        max-height: none;
        box-shadow: none;
        border: 0;
    }

    .sale-dialog-close {
        display: none;
    }
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1200px) {

    .sales-filter-row {
        grid-template-columns: repeat(4, 1fr);
    }

    .sales-filter-search {
        grid-column: span 2;
    }

}

@media (max-width: 900px) {

    .sales-stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .sale-item-row {
        grid-template-columns: 1fr 90px 110px 100px 32px;
    }

}

@media (max-width: 650px) {

    .sales-page {
        padding: 18px 14px 30px;
    }

    .sales-page-header {
        align-items: stretch;
        flex-direction: column;
    }

    .sales-page-header h1 {
        font-size: 22px;
    }

    .sales-primary-button {
        width: 100%;
    }

    .sales-stats-grid {
        grid-template-columns: 1fr;
    }

    .sales-filter-row {
        grid-template-columns: 1fr;
    }

    .sales-filter-search {
        grid-column: auto;
    }

    .sale-form-grid {
        grid-template-columns: 1fr;
    }

    .sale-field-full {
        grid-column: auto;
    }

    .sale-item-row {
        grid-template-columns: 1fr 1fr;
    }

    .sale-item-product {
        grid-column: 1 / -1;
    }

    .sale-item-subtotal {
        grid-column: 1;
    }

    .sale-remove-item {
        grid-column: 2;
        justify-self: end;
    }

    .sale-total-box {
        grid-template-columns: 1fr;
    }

    .sale-grand-total {
        text-align: left;
    }

    .sale-view-summary {
        grid-template-columns: 1fr 1fr;
    }

    .sale-dialog-footer,
    .sale-delete-actions {
        flex-direction: column-reverse;
    }

    .sale-dialog-footer button,
    .sale-delete-actions button {
        width: 100%;
    }

}

</style>

@endpush