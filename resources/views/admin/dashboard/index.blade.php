@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('page-title', 'Overview')

@section('content')

<div class="overview-page">

    {{-- =========================================================
         HEADER + DATE RANGE
    ========================================================== --}}

    <div class="overview-header">

        <div>
            <span class="overview-eyebrow">BUSINESS OVERVIEW</span>

            <h1>
                Welcome back, {{ auth()->user()->name ?? 'Administrator' }}
            </h1>

            <p>
                Sales, profit, orders and product performance from your database.
            </p>
        </div>

        <form
            method="GET"
            action="{{ route('admin.dashboard') }}"
            class="overview-period-form"
        >
            <div class="overview-date-row">

                <div class="overview-date-field quick-field">
                    <label for="dashboard-period">Quick View</label>

                    <select
                        id="dashboard-period"
                        name="period"
                        onchange="submitQuickDashboardPeriod(this)"
                    >
                        <option value="3" @selected($selectedPeriod === 3)>
                            3 Days
                        </option>

                        <option value="7" @selected($selectedPeriod === 7)>
                            7 Days
                        </option>

                        <option value="14" @selected($selectedPeriod === 14)>
                            2 Weeks
                        </option>

                        <option value="30" @selected($selectedPeriod === 30)>
                            1 Month
                        </option>

                        <option value="custom" @selected($hasCustomRange)>
                            Custom Range
                        </option>
                    </select>
                </div>

                <div class="overview-date-field">
                    <label for="dashboard-start-date">From</label>

                    <input
                        id="dashboard-start-date"
                        type="date"
                        name="start_date"
                        value="{{ $customStartDate }}"
                        max="{{ now()->format('Y-m-d') }}"
                    >
                </div>

                <div class="overview-date-field">
                    <label for="dashboard-end-date">To</label>

                    <input
                        id="dashboard-end-date"
                        type="date"
                        name="end_date"
                        value="{{ $customEndDate }}"
                        max="{{ now()->format('Y-m-d') }}"
                    >
                </div>

                <button
                    type="submit"
                    class="overview-apply-button"
                >
                    Apply
                </button>

            </div>
        </form>

    </div>


    {{-- =========================================================
         SELECTED RANGE
    ========================================================== --}}

    <div class="overview-range">
        <span class="range-dot"></span>

        <strong>{{ $periodLabel }}</strong>

        <span>
            {{ $startDate->format('d M Y') }}
            –
            {{ $endDate->format('d M Y') }}
        </span>
    </div>


    {{-- =========================================================
         MAIN KPI CARDS
    ========================================================== --}}

    <div class="overview-kpi-grid">

        {{-- SALES --}}
        <div class="overview-kpi sales-kpi">

            <div class="kpi-top">
                <div class="kpi-icon">₿</div>

                <span class="kpi-badge">
                    SALES
                </span>
            </div>

            <div class="kpi-number">
                ${{ number_format($stats['total_sales'], 2) }}
            </div>

            <div class="kpi-label">
                Total Sales
            </div>

            <div class="kpi-description">
                Completed sales in {{ strtolower($periodLabel) }}.
            </div>

        </div>


        {{-- PROFIT --}}
        <div class="overview-kpi profit-kpi">

            <div class="kpi-top">
                <div class="kpi-icon">↗</div>

                <span class="kpi-badge">
                    PROFIT
                </span>
            </div>

            <div class="kpi-number">
                ${{ number_format($stats['total_profit'], 2) }}
            </div>

            <div class="kpi-label">
                Estimated Profit
            </div>

            <div class="kpi-description">
                Sales minus actual variant cost.
            </div>

        </div>


        {{-- ORDERS --}}
        <div class="overview-kpi orders-kpi">

            <div class="kpi-top">
                <div class="kpi-icon">#</div>

                <span class="kpi-badge">
                    ORDERS
                </span>
            </div>

            <div class="kpi-number">
                {{ number_format($stats['total_orders']) }}
            </div>

            <div class="kpi-label">
                Completed Orders
            </div>

            <div class="kpi-description">
                Successful transactions in this period.
            </div>

        </div>


        {{-- AOV --}}
        <div class="overview-kpi aov-kpi">

            <div class="kpi-top">
                <div class="kpi-icon">◆</div>

                <span class="kpi-badge">
                    AVERAGE
                </span>
            </div>

            <div class="kpi-number">
                ${{ number_format($stats['average_order_value'], 2) }}
            </div>

            <div class="kpi-label">
                Average Order Value
            </div>

            <div class="kpi-description">
                Average completed order value.
            </div>

        </div>

    </div>


    {{-- =========================================================
         SALES CHART + INVENTORY
    ========================================================== --}}

    <div class="overview-two-column">

        <section class="overview-card sales-chart-card">

            <div class="section-heading">

                <div>
                    <span class="section-eyebrow">REVENUE TREND</span>

                    <h2>Sales Overview</h2>

                    <p>
                        Completed sales for {{ strtolower($periodLabel) }}.
                    </p>
                </div>

                <div class="section-total">
                    ${{ number_format(collect($salesOverview)->sum('sales'), 2) }}
                </div>

            </div>

            @php
                $maxSales = collect($salesOverview)->max('sales') ?? 0;
                $chartStep = $rangeDays <= 7
                    ? 1
                    : max(1, (int) ceil($rangeDays / 12));
            @endphp

            @if($maxSales > 0)

                <div class="sales-chart">

                    @foreach($salesOverview as $index => $day)

                        @php
                            $height = $maxSales > 0
                                ? ($day['sales'] / $maxSales) * 100
                                : 0;

                            $showLabel = $rangeDays <= 7
                                || $index % $chartStep === 0
                                || $index === count($salesOverview) - 1;
                        @endphp

                        <div class="chart-column">

                            <span class="chart-value">
                                @if($day['sales'] > 0)
                                    ${{ number_format($day['sales'], 0) }}
                                @endif
                            </span>

                            <div class="chart-track">
                                <div
                                    class="chart-bar"
                                    style="height: {{ max($height, 3) }}%;"
                                    title="{{ $day['date'] }} — ${{ number_format($day['sales'], 2) }}"
                                ></div>
                            </div>

                            @if($showLabel)
                                <span class="chart-label">
                                    {{ $day['label'] }}
                                </span>
                            @else
                                <span class="chart-label">&nbsp;</span>
                            @endif

                        </div>

                    @endforeach

                </div>

            @else

                <div class="overview-empty">
                    <div class="empty-icon">$</div>

                    <strong>No completed sales</strong>

                    <p>
                        There are no sales records in {{ strtolower($periodLabel) }}.
                    </p>
                </div>

            @endif

            <div class="chart-footer">

                <div>
                    <span>Sales</span>
                    <strong>
                        ${{ number_format($stats['total_sales'], 2) }}
                    </strong>
                </div>

                <div>
                    <span>Orders</span>
                    <strong>
                        {{ number_format($stats['total_orders']) }}
                    </strong>
                </div>

                <div>
                    <span>Profit</span>
                    <strong class="profit-text">
                        ${{ number_format($stats['total_profit'], 2) }}
                    </strong>
                </div>

            </div>

        </section>


        <section class="overview-card inventory-card">

            <div class="section-heading">

                <div>
                    <span class="section-eyebrow">STOCK STATUS</span>

                    <h2>Inventory</h2>

                    <p>
                        Current stock from the database.
                    </p>
                </div>

            </div>

            <div class="inventory-stat-grid">

                <div class="inventory-stat stock-stat">
                    <span class="inventory-stat-icon">▦</span>
                    <strong>{{ number_format($stats['total_stock']) }}</strong>
                    <span>Total Stock</span>
                </div>

                <div class="inventory-stat low-stat">
                    <span class="inventory-stat-icon">!</span>
                    <strong>{{ number_format($stats['low_stock']) }}</strong>
                    <span>Low Stock</span>
                </div>

                <div class="inventory-stat out-stat">
                    <span class="inventory-stat-icon">×</span>
                    <strong>{{ number_format($stats['out_of_stock']) }}</strong>
                    <span>Out of Stock</span>
                </div>

                <div class="inventory-stat product-stat">
                    <span class="inventory-stat-icon">P</span>
                    <strong>{{ number_format($stats['total_products']) }}</strong>
                    <span>Products</span>
                </div>

            </div>

        </section>

    </div>


    {{-- =========================================================
         TOP PRODUCTS + CATEGORY SALES
    ========================================================== --}}

    <div class="overview-two-column">

        <section class="overview-card">

            <div class="section-heading">

                <div>
                    <span class="section-eyebrow">PRODUCT PERFORMANCE</span>

                    <h2>Top Selling Products</h2>

                    <p>
                        Best-selling products for {{ strtolower($periodLabel) }}.
                    </p>
                </div>

                <span class="section-pill">
                    Top 5
                </span>

            </div>

            @if($topSellingProducts->count() > 0)

                <div class="top-products-list">

                    @foreach($topSellingProducts as $index => $product)

                        <div class="top-product-row">

                            <div class="product-rank">
                                {{ $index + 1 }}
                            </div>

                            <div class="product-avatar">
                                @if($product->image)
                                    <img
                                        src="{{ \Illuminate\Support\Facades\Storage::url($product->image) }}"
                                        alt="{{ $product->name }}"
                                    >
                                @else
                                    P
                                @endif
                            </div>

                            <div class="product-main">

                                <strong>
                                    {{ $product->name }}
                                </strong>

                                <span>
                                    {{ number_format($product->total_quantity) }}
                                    units sold
                                    ·
                                    {{ number_format($product->total_orders) }}
                                    orders
                                </span>

                            </div>

                            <div class="product-result">

                                <strong>
                                    ${{ number_format($product->total_sales, 2) }}
                                </strong>

                                <span class="{{ $product->profit >= 0 ? 'profit-text' : 'loss-text' }}">
                                    Profit
                                    ${{ number_format($product->profit, 2) }}
                                </span>

                            </div>

                        </div>

                    @endforeach

                </div>

            @else

                <div class="overview-empty small-empty">

                    <div class="empty-icon">P</div>

                    <strong>No product sales yet</strong>

                    <p>
                        Products will appear after completed sales are recorded.
                    </p>

                </div>

            @endif

        </section>


        <section class="overview-card">

            <div class="section-heading">

                <div>
                    <span class="section-eyebrow">CATEGORY PERFORMANCE</span>

                    <h2>Sales by Category</h2>

                    <p>
                        Percentage of sales by product category.
                    </p>
                </div>

                <span class="section-pill">
                    {{ number_format($categorySalesTotal, 2) }} total
                </span>

            </div>

            @if($salesByCategory->count() > 0)

                @php
                    $categoryColors = [
                        '#6F4E37',
                        '#A66A44',
                        '#C99A6B',
                        '#5F8D5A',
                        '#C85C4A',
                        '#8B8580',
                    ];
                @endphp

                <div class="category-list">

                    @foreach($salesByCategory as $index => $category)

                        @php
                            $categoryColor = $categoryColors[
                                $index % count($categoryColors)
                            ];
                        @endphp

                        <div class="category-row">

                            <div class="category-row-top">

                                <div class="category-name">

                                    <span
                                        class="category-dot"
                                        style="background: {{ $categoryColor }};"
                                    ></span>

                                    <strong>
                                        {{ $category->name }}
                                    </strong>

                                </div>

                                <strong class="category-percent">
                                    {{ number_format($category->percentage, 1) }}%
                                </strong>

                            </div>

                            <div class="category-progress">
                                <div
                                    class="category-progress-bar"
                                    style="
                                        width: {{ min($category->percentage, 100) }}%;
                                        background: {{ $categoryColor }};
                                    "
                                ></div>
                            </div>

                            <div class="category-row-bottom">

                                <span>
                                    {{ number_format($category->total_quantity) }}
                                    units
                                </span>

                                <span>
                                    ${{ number_format($category->total_sales, 2) }}
                                </span>

                            </div>

                        </div>

                    @endforeach

                </div>

            @else

                <div class="overview-empty small-empty">

                    <div class="empty-icon">%</div>

                    <strong>No category sales yet</strong>

                    <p>
                        Category percentages will appear after completed sales.
                    </p>

                </div>

            @endif

        </section>

    </div>


    {{-- =========================================================
         RECENT SALES
    ========================================================== --}}

    <section class="overview-card recent-sales-card">

        <div class="section-heading">

            <div>
                <span class="section-eyebrow">TRANSACTIONS</span>

                <h2>Recent Sales</h2>

                <p>
                    Latest completed transactions in the selected period.
                </p>
            </div>


        </div>

        @if($recentSales->count() > 0)

            <div class="recent-sales-table">

                <div class="recent-sales-head">
                    <span>Invoice</span>
                    <span>Date</span>
                    <span>Payment</span>
                    <span>Total</span>
                </div>

                @foreach($recentSales as $sale)

                    <div class="recent-sales-row">

                        <strong>
                            {{ $sale->invoice_number ?: '#'.$sale->id }}
                        </strong>

                        <span>
                            {{ $sale->sale_date?->format('d M Y') }}
                        </span>

                        <span class="payment-badge">
                            {{ str_replace('_', ' ', ucfirst($sale->payment_method ?? '—')) }}
                        </span>

                        <strong class="recent-total">
                            ${{ number_format($sale->total_amount, 2) }}
                        </strong>

                    </div>

                @endforeach

            </div>

        @else

            <div class="recent-empty">
                No completed sales in {{ strtolower($periodLabel) }}.
            </div>

        @endif

    </section>

</div>

@endsection


@push('styles')

<style>

:root {
    --overview-dark: #2C1E17;
    --overview-brown: #6F4E37;
    --overview-warm: #A66A44;
    --overview-light: #C99A6B;
    --overview-cream: #F5EDE3;
    --overview-bg: #FAF8F5;
    --overview-white: #FFFFFF;
    --overview-text: #24201E;
    --overview-muted: #8B8580;
    --overview-success: #5F8D5A;
    --overview-error: #C85C4A;
    --overview-border: rgba(44, 30, 23, .09);
}

.overview-page {
    width: 100%;
    max-width: 1500px;
    margin: 0 auto;
    padding: 24px 28px 40px;
    color: var(--overview-text);
}

.overview-page * {
    box-sizing: border-box;
}

.overview-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 14px;
}

.overview-eyebrow,
.section-eyebrow {
    display: block;
    margin-bottom: 7px;
    color: var(--overview-warm);
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1.4px;
}

.overview-header h1 {
    margin: 0;
    font-size: 24px;
    line-height: 1.2;
    font-weight: 850;
}

.overview-header p {
    margin: 7px 0 0;
    color: var(--overview-muted);
    font-size: 12px;
}

.overview-period-form {
    min-width: 190px;
}

.overview-period-form label {
    display: block;
    margin-bottom: 6px;
    color: var(--overview-muted);
    font-size: 12px;
    font-weight: 700;
}

.overview-period-form select {
    width: 100%;
    height: 46px;
    padding: 0 13px;
    border: 1px solid var(--overview-border);
    border-radius: 11px;
    background: var(--overview-white);
    color: var(--overview-text);
    font-family: inherit;
    font-size: 13px;
    font-weight: 600;
    outline: none;
    cursor: pointer;
}

.overview-period-form select:focus {
    border-color: var(--overview-warm);
    box-shadow: 0 0 0 3px rgba(166, 106, 68, .10);
}

.overview-range {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 18px;
    padding: 8px 12px;
    border: 1px solid var(--overview-border);
    border-radius: 9px;
    background: var(--overview-white);
    color: var(--overview-muted);
    font-size: 11px;
}

.overview-range strong {
    color: var(--overview-brown);
}

.range-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--overview-success);
}

.overview-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
    margin-bottom: 16px;
}

.overview-kpi {
    position: relative;
    min-width: 0;
    min-height: 155px;
    padding: 17px;
    overflow: hidden;
    border: 1px solid var(--overview-border);
    border-radius: 17px;
    background: var(--overview-white);
    box-shadow: 0 7px 25px rgba(44, 30, 23, .045);
}

.overview-kpi::after {
    position: absolute;
    right: -35px;
    bottom: -45px;
    width: 130px;
    height: 130px;
    border-radius: 50%;
    content: "";
    opacity: .09;
}

.sales-kpi {
    border-top: 4px solid var(--overview-brown);
}

.sales-kpi::after {
    background: var(--overview-brown);
}

.profit-kpi {
    border-top: 4px solid var(--overview-success);
}

.profit-kpi::after {
    background: var(--overview-success);
}

.orders-kpi {
    border-top: 4px solid #C99A6B;
}

.orders-kpi::after {
    background: #C99A6B;
}

.aov-kpi {
    border-top: 4px solid #8B8580;
}

.aov-kpi::after {
    background: #8B8580;
}

.kpi-top {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.kpi-icon {
    width: 39px;
    height: 39px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 11px;
    background: var(--overview-cream);
    color: var(--overview-brown);
    font-size: 17px;
    font-weight: 900;
}

.profit-kpi .kpi-icon {
    background: rgba(95, 141, 90, .12);
    color: var(--overview-success);
}

.orders-kpi .kpi-icon {
    background: rgba(201, 154, 107, .16);
    color: var(--overview-warm);
}

.aov-kpi .kpi-icon {
    background: rgba(139, 133, 128, .13);
    color: var(--overview-muted);
}

.kpi-badge {
    padding: 6px 8px;
    border-radius: 7px;
    background: var(--overview-bg);
    color: var(--overview-muted);
    font-size: 10px;
    font-weight: 850;
    letter-spacing: .8px;
}

.kpi-number {
    position: relative;
    z-index: 1;
    margin-top: 18px;
    color: var(--overview-dark);
    font-size: clamp(22px, 1.8vw, 29px);
    line-height: 1;
    font-weight: 850;
}

.profit-kpi .kpi-number {
    color: var(--overview-success);
}

.kpi-label {
    position: relative;
    z-index: 1;
    margin-top: 8px;
    color: var(--overview-text);
    font-size: 13px;
    font-weight: 750;
}

.kpi-description {
    position: relative;
    z-index: 1;
    margin-top: 5px;
    color: var(--overview-muted);
    font-size: 10px;
    line-height: 1.45;
}

.overview-two-column {
    display: grid;
    grid-template-columns: minmax(0, 1.35fr) minmax(0, 1fr);
    gap: 16px;
    margin-bottom: 16px;
}

.overview-card {
    min-width: 0;
    padding: 21px;
    border: 1px solid var(--overview-border);
    border-radius: 17px;
    background: var(--overview-white);
    box-shadow: 0 7px 25px rgba(44, 30, 23, .045);
}

.section-heading {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 18px;
    margin-bottom: 18px;
}

.section-heading h2 {
    margin: 0;
    color: var(--overview-text);
    font-size: 17px;
    font-weight: 800;
}

.section-heading p {
    margin: 5px 0 0;
    color: var(--overview-muted);
    font-size: 11px;
}

.section-total {
    color: var(--overview-brown);
    font-size: 17px;
    font-weight: 800;
    white-space: nowrap;
}

.section-pill {
    padding: 7px 10px;
    border-radius: 8px;
    background: var(--overview-cream);
    color: var(--overview-brown);
    font-size: 11px;
    font-weight: 800;
    white-space: nowrap;
}

.sales-chart-card {
    min-height: 360px;
}

.sales-chart {
    height: 215px;
    display: flex;
    align-items: stretch;
    gap: 7px;
    padding: 10px 4px 0;
    border-bottom: 1px solid var(--overview-border);
}

.chart-column {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-end;
}

.chart-value {
    height: 17px;
    overflow: hidden;
    color: var(--overview-brown);
    font-size: 9px;
    font-weight: 800;
    white-space: nowrap;
}

.chart-track {
    width: 100%;
    height: 174px;
    display: flex;
    align-items: flex-end;
    justify-content: center;
}

.chart-bar {
    width: min(30px, 70%);
    min-height: 4px;
    border-radius: 7px 7px 2px 2px;
    background: var(--overview-brown);
    box-shadow: 0 4px 10px rgba(111, 78, 55, .16);
}

.chart-column:nth-child(3n + 2) .chart-bar {
    background: var(--overview-warm);
}

.chart-column:nth-child(3n + 3) .chart-bar {
    background: var(--overview-light);
}

.chart-label {
    width: 100%;
    height: 21px;
    margin-top: 6px;
    overflow: hidden;
    color: var(--overview-muted);
    font-size: 9px;
    font-weight: 700;
    text-align: center;
    white-space: nowrap;
}

.chart-footer {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-top: 15px;
}

.chart-footer div {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.chart-footer span {
    color: var(--overview-muted);
    font-size: 11px;
}

.chart-footer strong {
    color: var(--overview-text);
    font-size: 14px;
    font-weight: 800;
}

.profit-text {
    color: var(--overview-success) !important;
}

.loss-text {
    color: var(--overview-error) !important;
}

.inventory-card {
    min-height: 360px;
}

.inventory-stat-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}

.inventory-stat {
    min-height: 128px;
    padding: 17px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    border-radius: 13px;
}

.inventory-stat-icon {
    width: 31px;
    height: 31px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
    border-radius: 9px;
    font-size: 13px;
    font-weight: 900;
}

.inventory-stat strong {
    font-size: 22px;
    line-height: 1;
    font-weight: 800;
}

.inventory-stat > span:last-child {
    margin-top: 5px;
    font-size: 11px;
    font-weight: 700;
}

.stock-stat {
    background: #F5EDE3;
    color: var(--overview-brown);
}

.stock-stat .inventory-stat-icon {
    background: rgba(111, 78, 55, .12);
}

.low-stat {
    background: #FFF5E7;
    color: #A66A44;
}

.low-stat .inventory-stat-icon {
    background: rgba(166, 106, 68, .13);
}

.out-stat {
    background: #FCEFEB;
    color: var(--overview-error);
}

.out-stat .inventory-stat-icon {
    background: rgba(200, 92, 74, .12);
}

.product-stat {
    background: #EEF5EC;
    color: var(--overview-success);
}

.product-stat .inventory-stat-icon {
    background: rgba(95, 141, 90, .12);
}

.top-products-list {
    display: flex;
    flex-direction: column;
}

.top-product-row {
    display: grid;
    grid-template-columns: 32px 43px minmax(0, 1fr) auto;
    align-items: center;
    gap: 11px;
    min-width: 0;
    padding: 11px 0;
    border-bottom: 1px solid var(--overview-border);
}

.top-product-row:last-child {
    border-bottom: 0;
}

.product-rank {
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 9px;
    background: var(--overview-cream);
    color: var(--overview-brown);
    font-size: 12px;
    font-weight: 900;
}

.product-avatar {
    width: 43px;
    height: 43px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    background: var(--overview-cream);
    color: var(--overview-brown);
    font-size: 14px;
    font-weight: 900;
}

.product-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-main {
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.product-main strong {
    overflow: hidden;
    color: var(--overview-text);
    font-size: 13px;
    font-weight: 750;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.product-main span {
    color: var(--overview-muted);
    font-size: 11px;
}

.product-result {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
}

.product-result strong {
    color: var(--overview-brown);
    font-size: 14px;
    font-weight: 850;
}

.product-result span {
    font-size: 10px;
    font-weight: 700;
}

.category-list {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.category-row {
    padding-bottom: 13px;
    border-bottom: 1px solid var(--overview-border);
}

.category-row:last-child {
    border-bottom: 0;
    padding-bottom: 0;
}

.category-row-top,
.category-row-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.category-name {
    min-width: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.category-name strong {
    overflow: hidden;
    color: var(--overview-text);
    font-size: 13px;
    font-weight: 800;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.category-dot {
    width: 10px;
    height: 10px;
    flex: 0 0 10px;
    border-radius: 50%;
}

.category-percent {
    color: var(--overview-brown);
    font-size: 13px;
    font-weight: 850;
}

.category-progress {
    height: 8px;
    margin: 8px 0 6px;
    overflow: hidden;
    border-radius: 20px;
    background: #F0EBE6;
}

.category-progress-bar {
    height: 100%;
    min-width: 2px;
    border-radius: 20px;
}

.category-row-bottom {
    color: var(--overview-muted);
    font-size: 10px;
}

.recent-sales-card {
    margin-bottom: 20px;
}

.section-link {
    padding: 8px 11px;
    border-radius: 8px;
    background: var(--overview-cream);
    color: var(--overview-brown);
    font-size: 11px;
    font-weight: 800;
    text-decoration: none;
    white-space: nowrap;
}

.section-link:hover {
    background: var(--overview-brown);
    color: var(--overview-white);
}

.recent-sales-table {
    width: 100%;
}

.recent-sales-head,
.recent-sales-row {
    display: grid;
    grid-template-columns: 1.2fr 1fr 1fr .7fr;
    align-items: center;
    gap: 15px;
}

.recent-sales-head {
    padding: 10px 12px;
    border-radius: 8px;
    background: var(--overview-bg);
    color: var(--overview-muted);
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .5px;
}

.recent-sales-row {
    padding: 13px 12px;
    border-bottom: 1px solid var(--overview-border);
    color: var(--overview-muted);
    font-size: 12px;
}

.recent-sales-row strong {
    color: var(--overview-text);
    font-size: 12px;
}

.recent-total {
    color: var(--overview-brown) !important;
    text-align: right;
}

.payment-badge {
    width: fit-content;
    padding: 5px 8px;
    border-radius: 7px;
    background: var(--overview-cream);
    color: var(--overview-brown);
    font-size: 10px;
    font-weight: 750;
    text-transform: capitalize;
}

.overview-empty {
    min-height: 220px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
}

.small-empty {
    min-height: 190px;
}

.empty-icon {
    width: 46px;
    height: 46px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
    border-radius: 13px;
    background: var(--overview-cream);
    color: var(--overview-brown);
    font-size: 17px;
    font-weight: 900;
}

.overview-empty strong {
    color: var(--overview-text);
    font-size: 14px;
    font-weight: 850;
}

.overview-empty p {
    max-width: 310px;
    margin: 6px 0 0;
    color: var(--overview-muted);
    font-size: 11px;
    line-height: 1.5;
}

.recent-empty {
    padding: 30px;
    border-radius: 10px;
    background: var(--overview-bg);
    color: var(--overview-muted);
    font-size: 13px;
    text-align: center;
}


/* =========================================================
   TABLET
========================================================= */

@media (max-width: 1050px) {

    .overview-kpi-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .overview-two-column {
        grid-template-columns: 1fr;
    }
}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 650px) {

    .overview-page {
        padding: 18px 14px 30px;
    }

    .overview-header {
        align-items: stretch;
        flex-direction: column;
    }

    .overview-header h1 {
        font-size: 21px;
    }

    .overview-kpi-grid {
        grid-template-columns: 1fr;
    }

    .overview-kpi {
        min-height: 155px;
    }

    .overview-two-column {
        grid-template-columns: 1fr;
    }

    .inventory-stat-grid {
        grid-template-columns: 1fr 1fr;
    }

    .top-product-row {
        grid-template-columns: 30px 40px minmax(0, 1fr);
    }

    .product-result {
        grid-column: 3;
        align-items: flex-start;
    }

    .recent-sales-head {
        display: none;
    }

    .recent-sales-row {
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        padding: 13px 5px;
    }

    .recent-total {
        text-align: left;
    }

    .section-heading {
        flex-direction: column;
    }

    .section-total,
    .section-pill,
    .section-link {
        align-self: flex-start;
    }

    .sales-chart {
        gap: 3px;
    }

    .chart-value {
        font-size: 7px;
    }
}


/* =========================================================
   CLEAN DATE FILTER
========================================================= */

.overview-date-row {
    display: flex;
    align-items: flex-end;
    justify-content: flex-end;
    gap: 9px;
}

.overview-date-field {
    min-width: 145px;
}

.overview-date-field.quick-field {
    min-width: 125px;
}

.overview-date-field label {
    display: block;
    margin: 0 0 6px 2px;
    color: var(--overview-muted);
    font-size: 10px;
    font-weight: 700;
}

.overview-date-field select,
.overview-date-field input {
    width: 100%;
    height: 40px;
    padding: 0 10px;
    border: 1px solid var(--overview-border);
    border-radius: 9px;
    background: var(--overview-white);
    color: var(--overview-text);
    font-family: inherit;
    font-size: 12px;
    font-weight: 600;
    outline: none;
}

.overview-date-field input {
    color-scheme: light;
}

.overview-date-field select:focus,
.overview-date-field input:focus {
    border-color: var(--overview-warm);
    box-shadow: 0 0 0 3px rgba(166, 106, 68, .08);
}

.overview-apply-button {
    height: 40px;
    padding: 0 15px;
    border: 0;
    border-radius: 9px;
    background: var(--overview-brown);
    color: #FFFFFF;
    font-family: inherit;
    font-size: 12px;
    font-weight: 750;
    cursor: pointer;
}

.overview-apply-button:hover {
    background: var(--overview-dark);
}

.sales-chart-card {
    overflow: hidden;
}

.sales-chart {
    overflow-x: auto;
    padding-bottom: 5px;
}

.chart-column {
    flex: 0 0 44px;
}

@media (max-width: 1050px) {
    .overview-header {
        align-items: flex-start;
    }

    .overview-date-row {
        flex-wrap: wrap;
        justify-content: flex-start;
    }
}

@media (max-width: 650px) {
    .overview-date-row {
        width: 100%;
        display: grid;
        grid-template-columns: 1fr 1fr;
    }

    .overview-date-field,
    .overview-date-field.quick-field {
        min-width: 0;
    }

    .overview-apply-button {
        width: 100%;
    }
}
</style>

<script>
function submitQuickDashboardPeriod(select) {
    if (select.value === 'custom') {
        return;
    }

    const form = select.closest('form');

    if (!form) {
        return;
    }

    const startInput = form.querySelector(
        'input[name="start_date"]'
    );

    const endInput = form.querySelector(
        'input[name="end_date"]'
    );

    if (startInput) {
        startInput.value = '';
    }

    if (endInput) {
        endInput.value = '';
    }

    form.submit();
}

document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('.overview-period-form');
    const quick = document.getElementById('dashboard-period');
    const start = document.getElementById('dashboard-start-date');
    const end = document.getElementById('dashboard-end-date');

    if (!form || !quick || !start || !end) {
        return;
    }

    function markCustomRange() {
        if (start.value && end.value) {
            quick.value = 'custom';
        }
    }

    start.addEventListener('change', markCustomRange);
    end.addEventListener('change', markCustomRange);
});
</script>

@endpush
