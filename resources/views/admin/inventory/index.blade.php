@extends('admin.layouts.app')

@section('title', 'Inventory')
@section('page-title', 'Inventory')

@section('content')

<style>

    .inventory-page {
        padding: 24px;
    }

    /* =========================================================
       HEADER
    ========================================================= */

    .inventory-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 24px;
    }

    .inventory-header h1 {
        margin: 0;
        color: #24201E;
        font-size: 28px;
        font-weight: 700;
    }

    .inventory-header p {
        margin: 7px 0 0;
        color: #8B8580;
        font-size: 14px;
    }


    /* =========================================================
       ALERT
    ========================================================= */

    .inventory-alert {
        margin-bottom: 18px;
        padding: 13px 16px;
        border-radius: 10px;
        font-size: 13px;
    }

    .inventory-success {
        background: #EEF5EC;
        color: #64DD17;
    }

    .inventory-error {
        background: #FCEFEB;
        color: #FF0000;
    }


    /* =========================================================
       STATS
    ========================================================= */

    .inventory-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 18px;
    }

    .inventory-stat {
        background: #FFFFFF;
        border: 1px solid rgba(44, 30, 23, .08);
        border-radius: 14px;
        padding: 18px 20px;
    }

    .inventory-stat-label {
        margin-bottom: 8px;
        color: #8B8580;
        font-size: 13px;
    }

    .inventory-stat-value {
        color: #24201E;
        font-size: 25px;
        font-weight: 700;
    }


    /* =========================================================
       FILTER
    ========================================================= */

    .inventory-filter {
        margin-bottom: 18px;
        padding: 16px;
        background: #FFFFFF;
        border: 1px solid rgba(44, 30, 23, .08);
        border-radius: 14px;
    }

    .inventory-filter-form {
        display: flex;
        gap: 10px;
    }

    .inventory-search {
        flex: 1;
        height: 42px;
        box-sizing: border-box;
        padding: 0 13px;
        border: 1px solid #E4DDD6;
        border-radius: 9px;
        background: #FAF8F5;
        color: #24201E;
        font-family: inherit;
        outline: none;
    }

    .inventory-search:focus {
        border-color: #A66A44;
        background: #FFFFFF;
    }

    .inventory-search-btn,
    .inventory-clear-btn {
        height: 42px;
        padding: 0 17px;
        border-radius: 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-family: inherit;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
    }

    .inventory-search-btn {
        border: none;
        background: #6F4E37;
        color: #FFFFFF;
    }

    .inventory-clear-btn {
        border: 1px solid #E4DDD6;
        background: #FFFFFF;
        color: #6F4E37;
    }


    /* =========================================================
       TABLE
    ========================================================= */

    .inventory-card {
        background: #FFFFFF;
        border: 1px solid rgba(44, 30, 23, .08);
        border-radius: 14px;
        overflow: hidden;
    }

    .inventory-table-wrapper {
        overflow-x: auto;
    }

    .inventory-table {
        width: 100%;
        min-width: 1050px;
        border-collapse: collapse;
    }

    .inventory-table th {
        padding: 15px 18px;
        background: #FAF8F5;
        border-bottom: 1px solid #EDE6DF;
        color: #8B8580;
        font-size: 12px;
        font-weight: 700;
        text-align: left;
        white-space: nowrap;
    }

    .inventory-table td {
        padding: 15px 18px;
        border-bottom: 1px solid #F0EBE6;
        vertical-align: middle;
        color: #24201E;
        font-size: 13px;
    }

    .inventory-table tr:last-child td {
        border-bottom: none;
    }

    .inventory-table tr:hover {
        background: #FCFAF8;
    }


    /* =========================================================
       PRODUCT
    ========================================================= */

    .inventory-product {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .inventory-product-image {
        width: 46px;
        height: 46px;
        flex: 0 0 46px;
        object-fit: cover;
        border-radius: 10px;
        background: #F5EDE3;
    }

    .inventory-product-placeholder {
        width: 46px;
        height: 46px;
        flex: 0 0 46px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: #F5EDE3;
        color: #6F4E37;
        font-weight: 700;
    }

    .inventory-product-name {
        color: #24201E;
        font-weight: 700;
    }

    .inventory-product-id {
        margin-top: 3px;
        color: #8B8580;
        font-size: 11px;
    }


    /* =========================================================
       VARIANT
    ========================================================= */

    .variant-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .variant-value {
        color: #6F4E37;
        font-weight: 600;
    }

    .variant-muted {
        color: #8B8580;
        font-size: 12px;
    }


    /* =========================================================
       STOCK
    ========================================================= */

    .stock-number {
        font-size: 18px;
        font-weight: 700;
    }

    .stock-normal {
        color: #64DD17;
    }

    .stock-low {
        color: #A66A44;
    }

    .stock-empty {
        color: #FF0000;
    }

    .not-set {
        color: #8B8580;
        font-size: 12px;
    }


    /* =========================================================
       STATUS
    ========================================================= */

    .stock-status {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 6px 10px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-good {
        background: #EEF5EC;
        color: #64DD17;
    }

    .status-low {
        background: #FBF3E9;
        color: #A66A44;
    }

    .status-out {
        background: #FCEFEB;
        color: #FF0000;
    }

    .status-not-set {
        background: #F5F1ED;
        color: #8B8580;
    }

    .status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor;
    }


    /* =========================================================
       ACTIONS
    ========================================================= */

    .inventory-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .inventory-action-form {
        margin: 0;
    }

    .inventory-action-input {
        width: 72px;
        height: 34px;
        box-sizing: border-box;
        padding: 0 8px;
        border: 1px solid #E4DDD6;
        border-radius: 8px;
        background: #FAF8F5;
        color: #24201E;
        font-family: inherit;
        font-size: 12px;
    }

    .inventory-action-btn {
        height: 34px;
        padding: 0 11px;
        border: none;
        border-radius: 8px;
        font-family: inherit;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
    }

    .stock-in-btn {
        background: #EEF5EC;
        color: #64DD17;
    }

    .stock-out-btn {
        background: #FCEFEB;
        color: #FF0000;
    }


    /* =========================================================
       SET STOCK
    ========================================================= */

    .set-stock-form {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    .set-stock-input {
        width: 65px;
        height: 34px;
        box-sizing: border-box;
        padding: 0 8px;
        border: 1px solid #E4DDD6;
        border-radius: 8px;
        background: #FAF8F5;
        font-family: inherit;
        font-size: 12px;
    }

    .set-stock-btn {
        height: 34px;
        padding: 0 11px;
        border: none;
        border-radius: 8px;
        background: #6F4E37;
        color: #FFFFFF;
        font-family: inherit;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
    }


    /* =========================================================
       EMPTY
    ========================================================= */

    .inventory-empty {
        padding: 70px 20px;
        text-align: center;
    }

    .inventory-empty-icon {
        margin-bottom: 12px;
        color: #6F4E37;
        font-size: 38px;
    }

    .inventory-empty h3 {
        margin: 0 0 6px;
        color: #24201E;
        font-size: 17px;
    }

    .inventory-empty p {
        margin: 0;
        color: #8B8580;
        font-size: 13px;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 1000px) {

        .inventory-stats {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 650px) {

        .inventory-page {
            padding: 16px;
        }

        .inventory-stats {
            grid-template-columns: 1fr;
        }

        .inventory-filter-form {
            flex-direction: column;
        }

        .inventory-search-btn,
        .inventory-clear-btn {
            width: 100%;
        }
    }

</style>


<div class="inventory-page">


    {{-- =====================================================
         HEADER
    ====================================================== --}}

    <div class="inventory-header">

        <div>

            <h1>
                Inventory
            </h1>

            <p>
                Manage stock levels for product variants.
            </p>

        </div>

    </div>


    {{-- =====================================================
         ALERTS
    ====================================================== --}}

    @if(session('success'))

        <div class="inventory-alert inventory-success">
            {{ session('success') }}
        </div>

    @endif


    @if(session('error'))

        <div class="inventory-alert inventory-error">
            {{ session('error') }}
        </div>

    @endif


    @if($errors->any())

        <div class="inventory-alert inventory-error">
            {{ $errors->first() }}
        </div>

    @endif


    {{-- =====================================================
         STATISTICS
    ====================================================== --}}

    <div class="inventory-stats">


        <div class="inventory-stat">

            <div class="inventory-stat-label">
                Product Variants
            </div>

            <div class="inventory-stat-value">
                {{ $stats['total_variants'] }}
            </div>

        </div>


        <div class="inventory-stat">

            <div class="inventory-stat-label">
                Inventory Records
            </div>

            <div class="inventory-stat-value">
                {{ $stats['inventory_records'] }}
            </div>

        </div>


        <div class="inventory-stat">

            <div class="inventory-stat-label">
                Total Stock
            </div>

            <div class="inventory-stat-value">
                {{ $stats['total_stock'] }}
            </div>

        </div>


        <div class="inventory-stat">

            <div class="inventory-stat-label">
                Low / Out
            </div>

            <div class="inventory-stat-value">
                {{ $stats['low_stock'] }} / {{ $stats['out_of_stock'] }}
            </div>

        </div>


    </div>


    {{-- =====================================================
         SEARCH
    ====================================================== --}}

    <div class="inventory-filter">

        <form
            action="{{ route('admin.inventory.index') }}"
            method="GET"
            class="inventory-filter-form"
        >

            <input
                type="text"
                name="search"
                class="inventory-search"
                value="{{ request('search') }}"
                placeholder="Search product, size or color..."
            >

            <button
                type="submit"
                class="inventory-search-btn"
            >
                Search
            </button>

            <a
                href="{{ route('admin.inventory.index') }}"
                class="inventory-clear-btn"
            >
                Clear
            </a>

        </form>

    </div>


    {{-- =====================================================
         INVENTORY TABLE
    ====================================================== --}}

    <div class="inventory-card">


        @if($variants->count())


            <div class="inventory-table-wrapper">

                <table class="inventory-table">

                    <thead>

                        <tr>

                            <th>
                                Product
                            </th>

                            <th>
                                Variant
                            </th>

                            <th>
                                Current Stock
                            </th>

                            <th>
                                Low Stock At
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Set Stock
                            </th>

                            <th>
                                Adjust
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        @foreach($variants as $variant)


                            @php

                                $inventory =
                                    $variant->inventory;

                                $stock =
                                    $inventory
                                    ? (int) $inventory->quantity
                                    : null;

                                $threshold =
                                    $inventory
                                    ? (int) $inventory->low_stock_threshold
                                    : null;

                            @endphp


                            <tr>


                                {{-- =================================================
                                     PRODUCT
                                ================================================== --}}

                                <td>

                                    <div class="inventory-product">


                                        @if($variant->product?->image)

                                            <img
                                                src="{{ Storage::url(
                                                    $variant->product->image
                                                ) }}"
                                                alt="{{ $variant->product->name }}"
                                                class="inventory-product-image"
                                            >

                                        @else

                                            <div class="inventory-product-placeholder">

                                                {{
                                                    strtoupper(
                                                        substr(
                                                            $variant->product?->name ?? 'P',
                                                            0,
                                                            1
                                                        )
                                                    )
                                                }}

                                            </div>

                                        @endif


                                        <div>

                                            <div class="inventory-product-name">

                                                {{
                                                    $variant->product?->name
                                                    ?? 'Product not found'
                                                }}

                                            </div>

                                            <div class="inventory-product-id">

                                                Product ID:
                                                {{ $variant->product?->id }}

                                            </div>

                                        </div>


                                    </div>

                                </td>


                                {{-- =================================================
                                     VARIANT
                                ================================================== --}}

                                <td>

                                    <div class="variant-info">


                                        <div>

                                            <span class="variant-muted">
                                                Size:
                                            </span>

                                            <span class="variant-value">

                                                {{
                                                    $variant->size
                                                    ?: '—'
                                                }}

                                            </span>

                                        </div>


                                        <div>

                                            <span class="variant-muted">
                                                Color:
                                            </span>

                                            <span class="variant-value">

                                                {{
                                                    $variant->color
                                                    ?: '—'
                                                }}

                                            </span>

                                        </div>


                                    </div>

                                </td>


                                {{-- =================================================
                                     CURRENT STOCK
                                ================================================== --}}

                                <td>

                                    @if($inventory)

                                        @if($stock <= 0)

                                            <span class="stock-number stock-empty">
                                                {{ $stock }}
                                            </span>

                                        @elseif(
                                            $threshold > 0
                                            &&
                                            $stock <= $threshold
                                        )

                                            <span class="stock-number stock-low">
                                                {{ $stock }}
                                            </span>

                                        @else

                                            <span class="stock-number stock-normal">
                                                {{ $stock }}
                                            </span>

                                        @endif

                                    @else

                                        <span class="not-set">
                                            Not Set
                                        </span>

                                    @endif

                                </td>


                                {{-- =================================================
                                     THRESHOLD
                                ================================================== --}}

                                <td>

                                    @if($inventory)

                                        {{ $threshold }}

                                    @else

                                        <span class="not-set">
                                            Not Set
                                        </span>

                                    @endif

                                </td>


                                {{-- =================================================
                                     STATUS
                                ================================================== --}}

                                <td>


                                    @if(!$inventory)

                                        <span class="stock-status status-not-set">

                                            <span class="status-dot"></span>

                                            Not Set

                                        </span>


                                    @elseif($stock <= 0)

                                        <span class="stock-status status-out">

                                            <span class="status-dot"></span>

                                            Out of Stock

                                        </span>


                                    @elseif(
                                        $threshold > 0
                                        &&
                                        $stock <= $threshold
                                    )

                                        <span class="stock-status status-low">

                                            <span class="status-dot"></span>

                                            Low Stock

                                        </span>


                                    @else

                                        <span class="stock-status status-good">

                                            <span class="status-dot"></span>

                                            In Stock

                                        </span>

                                    @endif


                                </td>


                                {{-- =================================================
                                     SET EXACT STOCK
                                ================================================== --}}

                                <td>

                                    <form
                                        action="{{ route(
                                            'admin.inventory.update',
                                            $variant
                                        ) }}"
                                        method="POST"
                                        class="set-stock-form"
                                    >

                                        @csrf

                                        @method('PUT')


                                        <input
                                            type="number"
                                            name="quantity"
                                            class="set-stock-input"
                                            min="0"
                                            value="{{ $stock ?? 0 }}"
                                            title="Stock quantity"
                                            required
                                        >


                                        <input
                                            type="number"
                                            name="low_stock_threshold"
                                            class="set-stock-input"
                                            min="0"
                                            value="{{ $threshold ?? 0 }}"
                                            title="Low stock threshold"
                                            required
                                        >


                                        <button
                                            type="submit"
                                            class="set-stock-btn"
                                            title="Save stock"
                                        >
                                            Save
                                        </button>

                                    </form>

                                </td>


                                {{-- =================================================
                                     ADJUST STOCK
                                ================================================== --}}

                                <td>

                                    <div class="inventory-actions">


                                        {{-- STOCK IN --}}

                                        <form
                                            action="{{ route(
                                                'admin.inventory.adjust',
                                                $variant
                                            ) }}"
                                            method="POST"
                                            class="inventory-action-form"
                                        >

                                            @csrf

                                            <input
                                                type="hidden"
                                                name="type"
                                                value="in"
                                            >

                                            <input
                                                type="number"
                                                name="quantity"
                                                class="inventory-action-input"
                                                min="1"
                                                value="1"
                                                required
                                            >

                                            <button
                                                type="submit"
                                                class="inventory-action-btn stock-in-btn"
                                                title="Add stock"
                                            >
                                                + Add
                                            </button>

                                        </form>


                                        {{-- STOCK OUT --}}

                                        <form
                                            action="{{ route(
                                                'admin.inventory.adjust',
                                                $variant
                                            ) }}"
                                            method="POST"
                                            class="inventory-action-form"
                                            onsubmit="return confirm('Remove this stock quantity?');"
                                        >

                                            @csrf

                                            <input
                                                type="hidden"
                                                name="type"
                                                value="out"
                                            >

                                            <input
                                                type="number"
                                                name="quantity"
                                                class="inventory-action-input"
                                                min="1"
                                                value="1"
                                                required
                                            >

                                            <button
                                                type="submit"
                                                class="inventory-action-btn stock-out-btn"
                                                title="Remove stock"
                                            >
                                                − Remove
                                            </button>

                                        </form>


                                    </div>

                                </td>


                            </tr>


                        @endforeach


                    </tbody>

                </table>

            </div>


        @else


            <div class="inventory-empty">

                <div class="inventory-empty-icon">
                    📦
                </div>

                <h3>
                    No Product Variants Found
                </h3>

                <p>
                    Inventory will appear here when product variants exist in the database.
                </p>

            </div>


        @endif


    </div>


</div>

@endsection