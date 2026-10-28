@extends('admin.layouts.app')

@section('title', 'Inventory')
@section('page-title', 'Inventory')

@section('content')
@php
    $highlightVariantId = request('highlight_variant_id');
@endphp
<div class="inventory-page">
    <div class="inventory-header">
        <div>
            <div class="inventory-eyebrow">STOCK MANAGEMENT</div>
            <h1>Inventory</h1>
            <p>Manage stock levels for all product variants.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="inventory-alert inventory-alert-success">
            <span class="alert-icon">✓</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="inventory-alert inventory-alert-error">
            <span class="alert-icon">!</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="inventory-alert inventory-alert-error">
            <span class="alert-icon">!</span>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <div class="inventory-stats">
        <div class="inventory-stat">
            <span class="inventory-stat-label">Product Variants</span>
            <strong>{{ number_format($stats['total_variants'] ?? 0) }}</strong>
        </div>

        <div class="inventory-stat">
            <span class="inventory-stat-label">Inventory Records</span>
            <strong>{{ number_format($stats['inventory_records'] ?? 0) }}</strong>
        </div>

        <div class="inventory-stat">
            <span class="inventory-stat-label">Total Stock</span>
            <strong>{{ number_format($stats['total_stock'] ?? 0) }}</strong>
        </div>

        <div class="inventory-stat inventory-stat-alert">
            <span class="inventory-stat-label">Low / Out</span>
            <strong>
                {{ number_format($stats['low_stock'] ?? 0) }}
                <span>/</span>
                {{ number_format($stats['out_of_stock'] ?? 0) }}
            </strong>
        </div>
    </div>

    <section class="inventory-search-card">
        <form action="{{ route('admin.inventory.index') }}" method="GET" class="inventory-search-form">
            <div class="inventory-search-box">
                <span class="inventory-search-icon">⌕</span>
                <input
                    type="search"
                    name="search"
                    value="{{ $search ?? request('search') }}"
                    placeholder="Search anything: product, SKU, barcode, category, size, color, stock..."
                    autocomplete="off"
                >
            </div>

            <button type="submit" class="inventory-search-button">
                Search
            </button>

            @if(filled($search ?? request('search')))
                <a href="{{ route('admin.inventory.index') }}" class="inventory-clear-button">
                    Clear
                </a>
            @endif
        </form>
    </section>

    <section class="inventory-list-card">
        <div class="inventory-list-header">
            <div>
                <h2>Inventory List</h2>
                <p>
                    {{ number_format($variants->total()) }}
                    {{ $variants->total() === 1 ? 'variant' : 'variants' }} found
                    @if(filled($search ?? request('search')))
                        for “{{ $search ?? request('search') }}”
                    @endif
                </p>
            </div>
        </div>

        @if($variants->count())
            <div class="inventory-table-wrap">
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Variant</th>
                            <th>Current Stock</th>
                            <th>Low Stock At</th>
                            <th>Status</th>
                            <th>Set Stock</th>
                            <th>Adjust</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($variants as $variant)
                            @php
                                $product = $variant->product;
                                $inventory = $variant->inventory;
                                $stock = (int) ($inventory?->quantity ?? 0);
                                $threshold = (int) ($inventory?->low_stock_threshold ?? 0);
                                $isOut = $stock <= 0;
                                $isLow = !$isOut && $threshold > 0 && $stock <= $threshold;
                                $imageUrl = $product?->image
                                    ? \Illuminate\Support\Facades\Storage::url($product->image)
                                    : null;
                            @endphp

                            <tr
                                id="inventory-variant-{{ $variant->id }}"
                                data-variant-id="{{ $variant->id }}"
                                class="{{ (string) $highlightVariantId === (string) $variant->id ? 'inventory-row-highlight' : '' }}"
                            >
                                <td>
                                    <div class="inventory-product">
                                        <div class="inventory-product-image">
                                            @if($imageUrl)
                                                <img src="{{ $imageUrl }}" alt="{{ $product?->name }}">
                                            @else
                                                <span>
                                                    {{ strtoupper(substr($product?->name ?? 'P', 0, 1)) }}
                                                </span>
                                            @endif
                                        </div>

                                        <div class="inventory-product-info">
                                            <strong>{{ $product?->name ?? 'Unknown Product' }}</strong>
                                            <small>ID: {{ $product?->id ?? '—' }}</small>
                                            <small>SKU: {{ $variant->sku ?: '—' }}</small>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="inventory-variant">
                                        <div>
                                            <span>Size</span>
                                            <strong>{{ $variant->size ?: '—' }}</strong>
                                        </div>
                                        <div>
                                            <span>Color</span>
                                            <strong>{{ $variant->color ?: '—' }}</strong>
                                        </div>
                                        <div>
                                            <span>Barcode</span>
                                            <strong>{{ $variant->barcode ?: '—' }}</strong>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <span class="stock-number {{ $isOut ? 'stock-number-out' : ($isLow ? 'stock-number-low' : '') }}">
                                        {{ number_format($stock) }}
                                    </span>
                                </td>

                                <td>
                                    <span class="threshold-number">
                                        {{ number_format($threshold) }}
                                    </span>
                                </td>

                                <td>
                                    @if($isOut)
                                        <span class="stock-status stock-status-out">
                                            <i></i> Out of Stock
                                        </span>
                                    @elseif($isLow)
                                        <span class="stock-status stock-status-low">
                                            <i></i> Low Stock
                                        </span>
                                    @else
                                        <span class="stock-status stock-status-in">
                                            <i></i> In Stock
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <form
                                        action="{{ route('admin.inventory.update', $variant) }}"
                                        method="POST"
                                        class="set-stock-form"
                                    >
                                        @csrf
                                        @method('PUT')

                                        <input
                                            type="number"
                                            name="quantity"
                                            value="{{ $stock }}"
                                            min="0"
                                            step="1"
                                            aria-label="Current stock"
                                            required
                                        >

                                        <input
                                            type="number"
                                            name="low_stock_threshold"
                                            value="{{ $threshold }}"
                                            min="0"
                                            step="1"
                                            aria-label="Low stock threshold"
                                            required
                                        >

                                        <button type="submit">Save</button>
                                    </form>
                                </td>

                                <td>
                                    <div class="adjust-stock-wrap">
                                        <form
                                            action="{{ route('admin.inventory.adjust', $variant) }}"
                                            method="POST"
                                            class="adjust-form"
                                            onsubmit="return confirmStockAdjustment(this, 'in');"
                                        >
                                            @csrf
                                            <input
                                                type="number"
                                                name="quantity"
                                                value="1"
                                                min="1"
                                                step="1"
                                                aria-label="Quantity to add"
                                                required
                                            >
                                            <input type="hidden" name="type" value="in">
                                            <button type="submit" class="adjust-add">+ Add</button>
                                        </form>

                                        <form
                                            action="{{ route('admin.inventory.adjust', $variant) }}"
                                            method="POST"
                                            class="adjust-form"
                                            onsubmit="return confirmStockAdjustment(this, 'out');"
                                        >
                                            @csrf
                                            <input
                                                type="number"
                                                name="quantity"
                                                value="1"
                                                min="1"
                                                max="{{ $stock > 0 ? $stock : 1 }}"
                                                step="1"
                                                aria-label="Quantity to remove"
                                                required
                                            >
                                            <input type="hidden" name="type" value="out">
                                            <button type="submit" class="adjust-remove">− Remove</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="inventory-pagination">
                <div class="inventory-pagination-info">
                    Showing
                    <strong>{{ $variants->firstItem() }}</strong>
                    to
                    <strong>{{ $variants->lastItem() }}</strong>
                    of
                    <strong>{{ $variants->total() }}</strong>
                </div>

                <div class="inventory-pagination-links">
                    @if($variants->onFirstPage())
                        <span class="disabled">‹</span>
                    @else
                        <a href="{{ $variants->previousPageUrl() }}">‹</a>
                    @endif

                    @foreach($variants->getUrlRange(max(1, $variants->currentPage() - 2), min($variants->lastPage(), $variants->currentPage() + 2)) as $page => $url)
                        @if($page == $variants->currentPage())
                            <span class="current">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if($variants->hasMorePages())
                        <a href="{{ $variants->nextPageUrl() }}">›</a>
                    @else
                        <span class="disabled">›</span>
                    @endif
                </div>
            </div>
        @else
            <div class="inventory-empty">
                <div class="inventory-empty-icon">⌕</div>
                <h3>No inventory results</h3>
                @if(filled($search ?? request('search')))
                    <p>No inventory variant matches “{{ $search ?? request('search') }}”.</p>
                    <a href="{{ route('admin.inventory.index') }}">Clear search</a>
                @else
                    <p>There are no product variants in the inventory yet.</p>
                @endif
            </div>
        @endif
    </section>
</div>
@endsection

@push('styles')
<style>
    :root {
        --inv-brown: #6F4E37;
        --inv-brown-dark: #563A29;
        --inv-text: #111827;
        --inv-muted: #6B7280;
        --inv-border: #E6DED7;
        --inv-page: #FAF8F5;
        --inv-green: #16A34A;
        --inv-green-bg: #ECFDF3;
        --inv-red: #DC2626;
        --inv-red-bg: #FEF2F2;
        --inv-orange: #D97706;
        --inv-orange-bg: #FFF7ED;
    }

    .inventory-page {
        padding: 34px 36px 48px;
        background: var(--inv-page);
        color: var(--inv-text);
        min-height: calc(100vh - 80px);
        font-family: inherit;
    }

    .inventory-header {
        margin-bottom: 24px;
    }

    .inventory-eyebrow {
        margin-bottom: 7px;
        color: var(--inv-brown);
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 1.5px;
    }

    .inventory-header h1 {
        margin: 0;
        font-size: 34px;
        line-height: 1.1;
        font-weight: 800;
        letter-spacing: -0.7px;
        color: var(--inv-text);
    }

    .inventory-header p {
        margin: 8px 0 0;
        color: var(--inv-muted);
        font-size: 15px;
    }

    .inventory-alert {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 18px;
        padding: 13px 16px;
        border: 1px solid transparent;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
    }

    .inventory-alert-success {
        background: var(--inv-green-bg);
        border-color: #BBF7D0;
        color: var(--inv-green);
    }

    .inventory-alert-error {
        background: var(--inv-red-bg);
        border-color: #FECACA;
        color: var(--inv-red);
    }

    .alert-icon {
        width: 22px;
        height: 22px;
        display: inline-grid;
        place-items: center;
        border-radius: 50%;
        background: currentColor;
        color: #FFFFFF;
        font-size: 12px;
        font-weight: 900;
    }

    .inventory-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 18px;
    }

    .inventory-stat {
        padding: 21px 22px;
        border: 1px solid var(--inv-border);
        border-radius: 14px;
        background: #FFFFFF;
    }

    .inventory-stat-label {
        display: block;
        margin-bottom: 9px;
        color: #60708A;
        font-size: 14px;
        font-weight: 600;
    }

    .inventory-stat strong {
        display: block;
        color: var(--inv-text);
        font-size: 30px;
        line-height: 1;
        font-weight: 800;
        letter-spacing: -0.5px;
    }

    .inventory-stat-alert strong span {
        color: #9CA3AF;
        padding: 0 3px;
    }

    .inventory-search-card {
        margin-bottom: 18px;
        padding: 15px;
        border: 1px solid var(--inv-border);
        border-radius: 14px;
        background: #FFFFFF;
    }

    .inventory-search-form {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto auto;
        gap: 10px;
    }

    .inventory-search-box {
        position: relative;
    }

    .inventory-search-icon {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-52%);
        color: #8794A8;
        font-size: 23px;
        line-height: 1;
        pointer-events: none;
    }

    .inventory-search-box input {
        width: 100%;
        height: 50px;
        box-sizing: border-box;
        padding: 0 16px 0 43px;
        border: 1px solid #D7DDE5;
        border-radius: 10px;
        background: #FFFFFF;
        color: var(--inv-text);
        font: inherit;
        font-size: 15px;
        outline: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .inventory-search-box input:focus {
        border-color: var(--inv-brown);
        box-shadow: 0 0 0 3px rgba(111, 78, 55, .12);
    }

    .inventory-search-button,
    .inventory-clear-button {
        height: 50px;
        padding: 0 22px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid transparent;
        font: inherit;
        font-size: 14px;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
    }

    .inventory-search-button {
        background: var(--inv-brown);
        color: #FFFFFF;
    }

    .inventory-search-button:hover {
        background: var(--inv-brown-dark);
    }

    .inventory-clear-button {
        border-color: #D7DDE5;
        background: #FFFFFF;
        color: var(--inv-brown);
    }

    .inventory-list-card {
        overflow: hidden;
        border: 1px solid var(--inv-border);
        border-radius: 14px;
        background: #FFFFFF;
    }

    .inventory-list-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 22px 22px 18px;
        border-bottom: 1px solid #ECE7E2;
    }

    .inventory-list-header h2 {
        margin: 0;
        font-size: 20px;
        line-height: 1.2;
        font-weight: 800;
        color: var(--inv-text);
    }

    .inventory-list-header p {
        margin: 5px 0 0;
        color: var(--inv-muted);
        font-size: 13px;
    }

    .inventory-table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .inventory-table {
        width: 100%;
        min-width: 1280px;
        border-collapse: collapse;
    }

    .inventory-table th {
        padding: 14px 16px;
        background: #FAF8F5;
        border-bottom: 1px solid #ECE7E2;
        color: #53657D;
        font-size: 12px;
        font-weight: 800;
        text-align: left;
        white-space: nowrap;
    }

    .inventory-table td {
        padding: 16px;
        border-bottom: 1px solid #EEEAE6;
        vertical-align: middle;
        color: var(--inv-text);
        font-size: 14px;
    }

    .inventory-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .inventory-table tbody tr:hover {
        background: #FFFCF9;
    }

    .inventory-product {
        min-width: 230px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .inventory-product-image {
        width: 58px;
        height: 58px;
        flex: 0 0 58px;
        overflow: hidden;
        display: grid;
        place-items: center;
        border: 1px solid #E5DED7;
        border-radius: 10px;
        background: #F5F1ED;
        color: var(--inv-brown);
        font-size: 21px;
        font-weight: 800;
    }

    .inventory-product-image img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
    }

    .inventory-product-info {
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .inventory-product-info strong {
        max-width: 260px;
        overflow: hidden;
        color: var(--inv-text);
        font-size: 15px;
        font-weight: 800;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .inventory-product-info small {
        color: #738096;
        font-size: 12px;
    }

    .inventory-variant {
        min-width: 145px;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .inventory-variant div {
        display: flex;
        gap: 7px;
    }

    .inventory-variant span {
        min-width: 48px;
        color: #7A8798;
        font-size: 12px;
    }

    .inventory-variant strong {
        color: var(--inv-text);
        font-size: 13px;
        font-weight: 700;
    }

    .stock-number {
        display: inline-flex;
        min-width: 48px;
        min-height: 38px;
        align-items: center;
        justify-content: center;
        padding: 0 10px;
        border-radius: 9px;
        background: var(--inv-green-bg);
        color: var(--inv-green);
        font-size: 16px;
        font-weight: 800;
    }

    .stock-number-low {
        background: var(--inv-orange-bg);
        color: var(--inv-orange);
    }

    .stock-number-out {
        background: var(--inv-red-bg);
        color: var(--inv-red);
    }

    .threshold-number {
        color: var(--inv-text);
        font-size: 14px;
        font-weight: 700;
    }

    .stock-status {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-size: 13px;
        font-weight: 800;
        white-space: nowrap;
    }

    .stock-status i {
        width: 8px;
        height: 8px;
        display: block;
        border-radius: 50%;
    }

    .stock-status-in {
        color: var(--inv-green);
    }

    .stock-status-in i {
        background: var(--inv-green);
    }

    .stock-status-low {
        color: var(--inv-orange);
    }

    .stock-status-low i {
        background: var(--inv-orange);
    }

    .stock-status-out {
        color: var(--inv-red);
    }

    .stock-status-out i {
        background: var(--inv-red);
    }

    .set-stock-form {
        display: flex;
        align-items: center;
        gap: 7px;
        white-space: nowrap;
    }

    .set-stock-form input,
    .adjust-form input {
        width: 70px;
        height: 40px;
        box-sizing: border-box;
        padding: 0 10px;
        border: 1px solid #D5DCE5;
        border-radius: 9px;
        background: #FFFFFF;
        color: var(--inv-text);
        font: inherit;
        font-size: 14px;
        font-weight: 700;
        outline: none;
    }

    .set-stock-form input:focus,
    .adjust-form input:focus {
        border-color: var(--inv-brown);
        box-shadow: 0 0 0 3px rgba(111, 78, 55, .10);
    }

    .set-stock-form button {
        height: 40px;
        padding: 0 15px;
        border: 0;
        border-radius: 9px;
        background: var(--inv-brown);
        color: #FFFFFF;
        font: inherit;
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
    }

    .set-stock-form button:hover {
        background: var(--inv-brown-dark);
    }

    .adjust-stock-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
    }

    .adjust-form {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .adjust-form input {
        width: 52px;
    }

    .adjust-form button {
        height: 40px;
        padding: 0 12px;
        border-radius: 9px;
        font: inherit;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
    }

    .adjust-add {
        border: 1px solid #86EFAC;
        background: #F0FDF4;
        color: #15803D;
    }

    .adjust-add:hover {
        background: #DCFCE7;
    }

    .adjust-remove {
        border: 1px solid #FCA5A5;
        background: #FEF2F2;
        color: var(--inv-red);
    }

    .adjust-remove:hover {
        background: #FEE2E2;
    }

    .inventory-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 16px 20px;
        border-top: 1px solid #EEEAE6;
    }

    .inventory-pagination-info {
        color: #69778A;
        font-size: 13px;
    }

    .inventory-pagination-info strong {
        color: var(--inv-text);
    }

    .inventory-pagination-links {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .inventory-pagination-links a,
    .inventory-pagination-links span {
        width: 34px;
        height: 34px;
        box-sizing: border-box;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #D8DFE7;
        border-radius: 8px;
        background: #FFFFFF;
        color: var(--inv-text);
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
    }

    .inventory-pagination-links .current {
        border-color: var(--inv-brown);
        background: var(--inv-brown);
        color: #FFFFFF;
    }

    .inventory-pagination-links .disabled {
        color: #B7BEC8;
        cursor: not-allowed;
    }

    .inventory-empty {
        padding: 72px 20px;
        text-align: center;
    }

    .inventory-empty-icon {
        width: 58px;
        height: 58px;
        margin: 0 auto 14px;
        display: grid;
        place-items: center;
        border-radius: 50%;
        background: #F4EFEA;
        color: var(--inv-brown);
        font-size: 26px;
        font-weight: 800;
    }

    .inventory-empty h3 {
        margin: 0;
        color: var(--inv-text);
        font-size: 18px;
        font-weight: 800;
    }

    .inventory-empty p {
        margin: 7px 0 16px;
        color: var(--inv-muted);
        font-size: 14px;
    }

    .inventory-empty a {
        color: var(--inv-brown);
        font-size: 14px;
        font-weight: 800;
        text-decoration: none;
    }

    /* Highlight the stock row opened from a notification. */
    .inventory-table tbody tr.inventory-row-highlight {
        outline: 2px solid #D97706;
        outline-offset: -2px;
        animation: inventory-highlight-pulse 1.2s ease-in-out 2;
    }

    .inventory-table tbody tr.inventory-row-highlight > td {
        background: #FFF1C7 !important;
    }

    @keyframes inventory-highlight-pulse {
        0%, 100% { box-shadow: inset 0 0 0 9999px rgba(255, 241, 199, .35); }
        50% { box-shadow: inset 0 0 0 9999px rgba(217, 119, 6, .13); }
    }

    @media (max-width: 1100px) {
        .inventory-page {
            padding: 28px 24px 40px;
        }

        .inventory-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 700px) {
        .inventory-page {
            padding: 22px 14px 32px;
        }

        .inventory-header h1 {
            font-size: 30px;
        }

        .inventory-stats {
            grid-template-columns: 1fr;
        }

        .inventory-search-form {
            grid-template-columns: 1fr;
        }

        .inventory-search-button,
        .inventory-clear-button {
            width: 100%;
        }

        .inventory-pagination {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const params = new URLSearchParams(window.location.search);
        const variantId = params.get('highlight_variant_id');

        if (!variantId) return;

        const row = document.getElementById('inventory-variant-' + variantId);

        if (row) {
            row.scrollIntoView({ behavior: 'smooth', block: 'center' });
            row.classList.add('inventory-row-highlight');
        } else {
            // If the variant is not on this page, tell the admin to search by its ID.
            const searchInput = document.querySelector('input[name="search"]');
            if (searchInput && !searchInput.value) {
                searchInput.value = variantId;
            }
        }
    });

    function confirmStockAdjustment(form, type) {
        const input = form.querySelector('input[name="quantity"]');
        const amount = Number(input ? input.value : 0);

        if (!Number.isInteger(amount) || amount < 1) {
            alert('Please enter a valid quantity greater than 0.');
            return false;
        }

        const action = type === 'in' ? 'add' : 'remove';
        return confirm('Are you sure you want to ' + action + ' ' + amount + ' stock unit(s)?');
    }
</script>
@endpush
