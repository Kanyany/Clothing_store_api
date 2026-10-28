@extends('admin.layouts.app')

@section('title', 'Reports')
@section('page-title', 'Reports')

@section('content')
<div class="report-page">

    {{-- Header --}}
    <header class="report-header">
        <div>
            <span class="report-eyebrow">CLOTHING STORE • BUSINESS INTELLIGENCE</span>
            <h1>Reports & Analytics</h1>
            <p>Understand sales, products, inventory and purchasing using real data from your store.</p>
        </div>

        <div class="report-export">
            <a href="{{ route('admin.reports.export.csv', request()->query()) }}" class="report-export-btn">
                <span>↓</span> CSV
            </a>
            <a href="{{ route('admin.reports.export.excel', request()->query()) }}" class="report-export-btn">
                <span>↓</span> Excel
            </a>
            <a href="{{ route('admin.reports.export.pdf', request()->query()) }}" target="_blank" class="report-export-btn report-export-primary">
                <span>↓</span> PDF
            </a>
        </div>
    </header>

    {{-- Date filter --}}
    <section class="report-filter-card">
        <form method="GET" class="report-filter">
            <div class="report-field report-search-period">
                <label for="date_from">From date</label>
                <input id="date_from" type="date" name="date_from" value="{{ $dateFrom }}">
            </div>

            <div class="report-field">
                <label for="date_to">To date</label>
                <input id="date_to" type="date" name="date_to" value="{{ $dateTo }}">
            </div>

            <button type="submit" class="report-apply">Apply Filter</button>
            <a href="{{ route('admin.reports.index') }}" class="report-clear">Clear</a>
        </form>

        <div class="report-period-note">
            <span class="report-period-dot"></span>
            <strong>{{ $dateFrom || $dateTo ? 'Filtered period' : 'All recorded dates' }}</strong>
            <span>
                {{ $dateFrom ?: 'All dates' }}
                <b>→</b>
                {{ $dateTo ?: 'All dates' }}
            </span>
            <em>Completed sales only for sales and profit metrics</em>
        </div>
    </section>

    {{-- Main KPI cards --}}
    <section class="report-kpis">
        <article class="report-kpi revenue">
            <div class="report-kpi-top">
                <span>Revenue</span>
                <i>↗</i>
            </div>
            <strong>${{ number_format($stats['total_sales'], 2) }}</strong>
            <small>{{ number_format($stats['total_orders']) }} completed orders</small>
        </article>

        <article class="report-kpi profit">
            <div class="report-kpi-top">
                <span>Gross Profit</span>
                <i>✓</i>
            </div>
            <strong>${{ number_format($profit['gross_profit'], 2) }}</strong>
            <small>{{ number_format($profit['gross_margin'], 1) }}% gross margin</small>
        </article>

        <article class="report-kpi items">
            <div class="report-kpi-top">
                <span>Items Sold</span>
                <i>▣</i>
            </div>
            <strong>{{ number_format($stats['items_sold']) }}</strong>
            <small>units sold</small>
        </article>

        <article class="report-kpi average">
            <div class="report-kpi-top">
                <span>Average Order</span>
                <i>≈</i>
            </div>
            <strong>${{ number_format($stats['average_order_value'], 2) }}</strong>
            <small>per completed order</small>
        </article>
    </section>

    {{-- Sales trend + payment --}}
    <section class="report-grid report-grid-main">

        <article class="report-card report-trend-card">
            <div class="report-card-header">
                <div>
                    <span class="report-section-label">REVENUE TREND</span>
                    <h2>Sales by Day</h2>
                    <p>Daily revenue from completed transactions.</p>
                </div>
                <div class="report-card-total">
                    <small>Total revenue</small>
                    <strong>${{ number_format($stats['total_sales'], 2) }}</strong>
                </div>
            </div>

            @if($dailySales->count())
                @php
                    $maxDaily = max((float) $dailySales->max('value'), 1);
                @endphp

                <div class="report-chart">
                    @foreach($dailySales as $day)
                        @php
                            $barHeight = $day['value'] > 0
                                ? max(8, ($day['value'] / $maxDaily) * 100)
                                : 3;
                        @endphp
                        <div class="report-chart-col" title="{{ $day['date'] }} • ${{ number_format($day['value'], 2) }}">
                            <div class="report-chart-value">
                                {{ $day['value'] > 0 ? '$'.number_format($day['value'], 0) : '' }}
                            </div>
                            <div class="report-chart-track">
                                <div class="report-chart-bar" style="height: {{ $barHeight }}%"></div>
                            </div>
                            <small>{{ $day['label'] }}</small>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="report-empty report-empty-chart">
                    <div class="report-empty-icon">▥</div>
                    <strong>No completed sales</strong>
                    <p>There are no completed sales in the selected period.</p>
                </div>
            @endif
        </article>

        <article class="report-card">
            <div class="report-card-header">
                <div>
                    <span class="report-section-label">PAYMENT MIX</span>
                    <h2>Payment Methods</h2>
                    <p>Revenue by payment method.</p>
                </div>
            </div>

            <div class="report-payment-list">
                @forelse($payments as $payment)
                    @php
                        $share = $stats['total_sales'] > 0
                            ? ($payment['revenue'] / $stats['total_sales']) * 100
                            : 0;
                    @endphp
                    <div class="report-payment-row">
                        <div class="report-payment-head">
                            <div>
                                <strong>{{ $payment['label'] }}</strong>
                                <span>{{ number_format($payment['orders']) }} orders</span>
                            </div>
                            <b>${{ number_format($payment['revenue'], 2) }}</b>
                        </div>

                        <div class="report-progress">
                            <i style="width: {{ min(100, $share) }}%"></i>
                        </div>

                        <small>{{ number_format($share, 1) }}% of revenue</small>
                    </div>
                @empty
                    <div class="report-empty">
                        <div class="report-empty-icon">◌</div>
                        <strong>No payment records</strong>
                        <p>No completed payment data is available.</p>
                    </div>
                @endforelse
            </div>
        </article>
    </section>

    {{-- Product and category performance --}}
    <section class="report-grid report-grid-two">
        <article class="report-card">
            <div class="report-card-header">
                <div>
                    <span class="report-section-label">PRODUCT PERFORMANCE</span>
                    <h2>Top Products</h2>
                    <p>Products ranked by units sold.</p>
                </div>
            </div>

            <div class="report-ranking-list">
                @forelse($bestProducts as $index => $row)
                    <div class="report-ranking-row">
                        <span class="report-rank">{{ $index + 1 }}</span>
                        <div class="report-rank-main">
                            <strong>{{ $row->name }}</strong>
                            <small>{{ number_format($row->quantity) }} units • ${{ number_format($row->revenue, 2) }} revenue</small>
                        </div>
                        <b class="{{ $row->profit >= 0 ? 'is-positive' : 'is-negative' }}">
                            ${{ number_format($row->profit, 2) }}
                        </b>
                    </div>
                @empty
                    <div class="report-empty">
                        <div class="report-empty-icon">□</div>
                        <strong>No product sales</strong>
                        <p>Products will appear here after completed sales.</p>
                    </div>
                @endforelse
            </div>
        </article>

        <article class="report-card">
            <div class="report-card-header">
                <div>
                    <span class="report-section-label">CATEGORY PERFORMANCE</span>
                    <h2>Sales by Category</h2>
                    <p>See which clothing categories generate revenue.</p>
                </div>
            </div>

            <div class="report-ranking-list">
                @forelse($categories->take(8) as $index => $row)
                    <div class="report-category-row">
                        <div class="report-category-name">
                            <span>{{ $index + 1 }}</span>
                            <strong>{{ $row->name }}</strong>
                        </div>
                        <div class="report-category-metrics">
                            <small>{{ number_format($row->quantity) }} units</small>
                            <b>${{ number_format($row->revenue, 2) }}</b>
                        </div>
                    </div>
                @empty
                    <div class="report-empty">
                        <div class="report-empty-icon">◇</div>
                        <strong>No category sales</strong>
                        <p>No completed category sales are available.</p>
                    </div>
                @endforelse
            </div>
        </article>
    </section>

    {{-- Inventory health --}}
    <section class="report-card report-inventory-card">
        <div class="report-card-header">
            <div>
                <span class="report-section-label">INVENTORY HEALTH</span>
                <h2>Current Stock</h2>
                <p>Live inventory position from your product variants.</p>
            </div>
        </div>

        <div class="report-inventory-kpis">
            <div class="stock-normal">
                <small>Units in stock</small>
                <strong>{{ number_format($inventory['current_stock']) }}</strong>
                <span>current quantity</span>
            </div>
            <div class="stock-value">
                <small>Stock value</small>
                <strong>${{ number_format($inventory['stock_value'], 2) }}</strong>
                <span>at current cost price</span>
            </div>
            <div class="stock-low">
                <small>Low stock</small>
                <strong>{{ number_format($inventory['low_stock']) }}</strong>
                <span>variants need attention</span>
            </div>
            <div class="stock-out">
                <small>Out of stock</small>
                <strong>{{ number_format($inventory['out_of_stock']) }}</strong>
                <span>variants unavailable</span>
            </div>
        </div>

        @if($lowStockRows->count() || $outOfStockRows->count())
            <div class="report-table-wrap">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Variant</th>
                            <th>Stock</th>
                            <th>Threshold</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lowStockRows as $row)
                            <tr>
                                <td><strong>{{ $row->name }}</strong></td>
                                <td>{{ $row->color ?: '—' }} / {{ $row->size ?: '—' }}</td>
                                <td><strong>{{ number_format($row->quantity) }}</strong></td>
                                <td>{{ number_format($row->low_stock_threshold) }}</td>
                                <td><span class="report-badge warning"><i></i> Low stock</span></td>
                            </tr>
                        @endforeach

                        @foreach($outOfStockRows as $row)
                            <tr>
                                <td><strong>{{ $row->name }}</strong></td>
                                <td>{{ $row->color ?: '—' }} / {{ $row->size ?: '—' }}</td>
                                <td><strong>0</strong></td>
                                <td>—</td>
                                <td><span class="report-badge danger"><i></i> Out of stock</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="report-stock-ok">
                <span>✓</span>
                <div>
                    <strong>Inventory looks healthy</strong>
                    <p>No low-stock or out-of-stock variants were found.</p>
                </div>
            </div>
        @endif
    </section>

    {{-- Detailed product report + purchases --}}
    <section class="report-grid report-grid-two">

        <article class="report-card">
            <div class="report-card-header">
                <div>
                    <span class="report-section-label">PRODUCT REPORT</span>
                    <h2>All Products Sold</h2>
                    <p>Detailed sales and estimated profit by product.</p>
                </div>
            </div>

            <div class="report-table-wrap">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Revenue</th>
                            <th>Cost</th>
                            <th>Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $row)
                            <tr>
                                <td><strong>{{ $row->name }}</strong></td>
                                <td>{{ number_format($row->quantity) }}</td>
                                <td>${{ number_format($row->revenue, 2) }}</td>
                                <td>${{ number_format($row->cost, 2) }}</td>
                                <td class="{{ $row->profit >= 0 ? 'is-positive' : 'is-negative' }}">
                                    <strong>${{ number_format($row->profit, 2) }}</strong>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="report-empty report-table-empty">
                                        <strong>No product records</strong>
                                        <p>No completed product sales in this period.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="report-card report-purchase-card">
            <div class="report-card-header">
                <div>
                    <span class="report-section-label">PURCHASE ACTIVITY</span>
                    <h2>Purchases</h2>
                    <p>Procurement activity for the selected period.</p>
                </div>
            </div>

            <div class="report-purchase-grid">
                <div>
                    <small>Purchase orders</small>
                    <strong>{{ number_format($purchases['total_purchases']) }}</strong>
                </div>
                <div>
                    <small>Items purchased</small>
                    <strong>{{ number_format($purchases['items_purchased']) }}</strong>
                </div>
                <div class="report-purchase-total">
                    <small>Purchase cost</small>
                    <strong>${{ number_format($purchases['purchase_cost'], 2) }}</strong>
                </div>
            </div>

            <div class="report-info-note">
                <span>i</span>
                <p>
                    Gross profit is an estimate using the current
                    <strong>product variant cost price</strong> against completed sale revenue.
                    Historical cost is not stored in the current sale item structure.
                </p>
            </div>
        </article>
    </section>

</div>
@endsection

@push('styles')
<style>
:root {
    --report-brown: #6F4E37;
    --report-brown-dark: #4A3123;
    --report-brown-soft: #F5ECE4;

    --report-text: #111827;
    --report-muted: #667085;
    --report-border: #E4E7EC;
    --report-bg: #F8F7F5;
    --report-white: #FFFFFF;

    --report-green: #16A34A;
    --report-green-bg: #ECFDF3;
    --report-red: #DC2626;
    --report-red-bg: #FEF2F2;
    --report-orange: #D97706;
    --report-orange-bg: #FFF7ED;
    --report-blue: #2563EB;
    --report-blue-bg: #EFF6FF;
}

.report-page,
.report-page * {
    box-sizing: border-box;
}

.report-page {
    width: 100%;
    max-width: 1540px;
    margin: 0 auto;
    padding: 28px 30px 50px;
    color: var(--report-text);
    font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont,
        "Segoe UI", Roboto, Arial, sans-serif;
    -webkit-font-smoothing: antialiased;
    text-rendering: optimizeLegibility;
}

/* Header */
.report-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 25px;
    margin-bottom: 22px;
}

.report-eyebrow {
    display: block;
    margin-bottom: 8px;
    color: var(--report-brown);
    font-size: 11px;
    line-height: 1.2;
    font-weight: 850;
    letter-spacing: 1.15px;
}

.report-header h1 {
    margin: 0;
    color: var(--report-text);
    font-size: 30px;
    line-height: 1.2;
    font-weight: 850;
    letter-spacing: -.45px;
}

.report-header p {
    margin: 8px 0 0;
    max-width: 720px;
    color: var(--report-muted);
    font-size: 14px;
    line-height: 1.55;
}

.report-export {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.report-export-btn {
    min-height: 42px;
    padding: 0 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    border: 1px solid #D0D5DD;
    border-radius: 9px;
    background: #fff;
    color: var(--report-text);
    text-decoration: none;
    font-size: 13px;
    line-height: 1;
    font-weight: 750;
}

.report-export-btn:hover {
    border-color: var(--report-brown);
    color: var(--report-brown);
    background: var(--report-brown-soft);
}

.report-export-primary {
    border-color: var(--report-brown);
    background: var(--report-brown);
    color: #fff;
}

.report-export-primary:hover {
    background: var(--report-brown-dark);
    color: #fff;
}

/* Filter */
.report-filter-card {
    margin-bottom: 18px;
    padding: 18px;
    border: 1px solid var(--report-border);
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 3px 14px rgba(16, 24, 40, .035);
}

.report-filter {
    display: grid;
    grid-template-columns: 200px 200px auto auto;
    align-items: end;
    gap: 12px;
}

.report-field label {
    display: block;
    margin-bottom: 7px;
    color: var(--report-text);
    font-size: 12px;
    line-height: 1.25;
    font-weight: 750;
}

.report-field input {
    width: 100%;
    height: 44px;
    padding: 0 12px;
    border: 1px solid #D0D5DD;
    border-radius: 9px;
    background: #fff;
    color: var(--report-text);
    font: inherit;
    font-size: 14px;
    outline: none;
}

.report-field input:focus {
    border-color: var(--report-brown);
    box-shadow: 0 0 0 3px rgba(111, 78, 55, .10);
}

.report-apply,
.report-clear {
    min-height: 44px;
    padding: 0 18px;
    border-radius: 9px;
    font: inherit;
    font-size: 14px;
    line-height: 1;
    font-weight: 750;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.report-apply {
    border: 1px solid var(--report-brown);
    background: var(--report-brown);
    color: #fff;
}

.report-apply:hover {
    background: var(--report-brown-dark);
}

.report-clear {
    border: 1px solid #D0D5DD;
    background: #fff;
    color: var(--report-text);
}

.report-clear:hover {
    border-color: var(--report-brown);
    background: var(--report-brown-soft);
    color: var(--report-brown);
}

.report-period-note {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 13px;
    padding: 10px 12px;
    border-radius: 9px;
    background: var(--report-bg);
    color: var(--report-muted);
    font-size: 12px;
    line-height: 1.45;
}

.report-period-note strong {
    color: var(--report-text);
}

.report-period-note b {
    color: var(--report-brown);
    margin: 0 3px;
}

.report-period-note em {
    margin-left: auto;
    color: var(--report-muted);
    font-style: normal;
}

.report-period-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--report-green);
}

/* KPI */
.report-kpis {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 15px;
    margin-bottom: 18px;
}

.report-kpi {
    min-height: 150px;
    padding: 20px;
    border: 1px solid var(--report-border);
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 3px 14px rgba(16, 24, 40, .035);
    border-top: 4px solid transparent;
}

.report-kpi.revenue {
    border-top-color: var(--report-brown);
}
.report-kpi.profit {
    border-top-color: var(--report-green);
}
.report-kpi.items {
    border-top-color: var(--report-blue);
}
.report-kpi.average {
    border-top-color: var(--report-orange);
}

.report-kpi-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.report-kpi-top span {
    color: var(--report-muted);
    font-size: 13px;
    line-height: 1.3;
    font-weight: 700;
}

.report-kpi-top i {
    width: 30px;
    height: 30px;
    display: grid;
    place-items: center;
    border-radius: 8px;
    background: var(--report-bg);
    color: var(--report-brown);
    font-size: 14px;
    font-style: normal;
    font-weight: 850;
}

.report-kpi strong {
    display: block;
    margin-top: 13px;
    color: var(--report-text);
    font-size: 28px;
    line-height: 1.1;
    font-weight: 850;
    letter-spacing: -.35px;
}

.report-kpi.revenue strong {
    color: var(--report-brown);
}
.report-kpi.profit strong {
    color: var(--report-green);
}
.report-kpi.items strong {
    color: var(--report-blue);
}
.report-kpi.average strong {
    color: var(--report-orange);
}

.report-kpi small {
    display: block;
    margin-top: 8px;
    color: var(--report-muted);
    font-size: 12px;
    line-height: 1.4;
}

/* Cards and grids */
.report-grid {
    display: grid;
    gap: 18px;
    margin-bottom: 18px;
}

.report-grid-main {
    grid-template-columns: minmax(0, 1.45fr) minmax(330px, .85fr);
}

.report-grid-two {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.report-card {
    min-width: 0;
    border: 1px solid var(--report-border);
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 3px 14px rgba(16, 24, 40, .035);
    overflow: hidden;
}

.report-card-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    padding: 21px 22px 16px;
}

.report-section-label {
    display: block;
    margin-bottom: 6px;
    color: var(--report-brown);
    font-size: 10px;
    line-height: 1.2;
    font-weight: 850;
    letter-spacing: 1.15px;
}

.report-card-header h2 {
    margin: 0;
    color: var(--report-text);
    font-size: 19px;
    line-height: 1.3;
    font-weight: 820;
}

.report-card-header p {
    margin: 5px 0 0;
    color: var(--report-muted);
    font-size: 13px;
    line-height: 1.45;
}

.report-card-total {
    min-width: 130px;
    text-align: right;
}

.report-card-total small {
    display: block;
    color: var(--report-muted);
    font-size: 11px;
}

.report-card-total strong {
    display: block;
    margin-top: 4px;
    color: var(--report-brown);
    font-size: 19px;
    line-height: 1.25;
    font-weight: 850;
}

/* Chart */
.report-chart {
    height: 255px;
    display: flex;
    align-items: flex-end;
    gap: 7px;
    margin: 4px 20px 18px;
    padding: 0 5px;
    border-bottom: 1px solid #EDEFF2;
}

.report-chart-col {
    height: 100%;
    min-width: 12px;
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    align-items: center;
}

.report-chart-value {
    min-height: 20px;
    color: var(--report-brown);
    font-size: 10px;
    line-height: 1.2;
    font-weight: 750;
    white-space: nowrap;
}

.report-chart-track {
    width: 100%;
    height: 190px;
    display: flex;
    align-items: flex-end;
    justify-content: center;
}

.report-chart-bar {
    width: min(34px, 75%);
    min-height: 3px;
    border-radius: 7px 7px 2px 2px;
    background: var(--report-brown);
    transition: opacity .15s ease;
}

.report-chart-col:hover .report-chart-bar {
    opacity: .78;
}

.report-chart-col small {
    min-height: 27px;
    padding-top: 8px;
    color: var(--report-muted);
    font-size: 10px;
    line-height: 1.2;
    white-space: nowrap;
}

/* Payments */
.report-payment-list {
    padding: 0 22px 18px;
}

.report-payment-row {
    padding: 14px 0;
    border-bottom: 1px solid #EEF0F2;
}

.report-payment-row:last-child {
    border-bottom: 0;
}

.report-payment-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
}

.report-payment-head div {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.report-payment-head strong {
    color: var(--report-text);
    font-size: 14px;
    line-height: 1.3;
    font-weight: 780;
}

.report-payment-head span {
    color: var(--report-muted);
    font-size: 12px;
}

.report-payment-head b {
    color: var(--report-brown);
    font-size: 14px;
    font-weight: 820;
}

.report-progress {
    height: 7px;
    margin: 10px 0 5px;
    overflow: hidden;
    border-radius: 99px;
    background: #EEE8E2;
}

.report-progress i {
    display: block;
    height: 100%;
    min-width: 0;
    border-radius: inherit;
    background: var(--report-brown);
}

.report-payment-row > small {
    color: var(--report-muted);
    font-size: 11px;
}

/* Ranking */
.report-ranking-list {
    padding: 0 22px 14px;
}

.report-ranking-row {
    display: grid;
    grid-template-columns: 32px minmax(0, 1fr) auto;
    align-items: center;
    gap: 12px;
    min-height: 67px;
    padding: 10px 0;
    border-bottom: 1px solid #EEF0F2;
}

.report-ranking-row:last-child {
    border-bottom: 0;
}

.report-rank {
    width: 30px;
    height: 30px;
    display: grid;
    place-items: center;
    border-radius: 8px;
    background: var(--report-brown-soft);
    color: var(--report-brown);
    font-size: 12px;
    font-weight: 850;
}

.report-rank-main {
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.report-rank-main strong {
    overflow: hidden;
    color: var(--report-text);
    font-size: 14px;
    line-height: 1.3;
    font-weight: 750;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.report-rank-main small {
    color: var(--report-muted);
    font-size: 12px;
    line-height: 1.35;
}

.report-ranking-row > b {
    font-size: 13px;
    white-space: nowrap;
}

.is-positive {
    color: var(--report-green) !important;
}

.is-negative {
    color: var(--report-red) !important;
}

.report-category-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    min-height: 61px;
    padding: 10px 0;
    border-bottom: 1px solid #EEF0F2;
}

.report-category-row:last-child {
    border-bottom: 0;
}

.report-category-name {
    min-width: 0;
    display: flex;
    align-items: center;
    gap: 11px;
}

.report-category-name > span {
    width: 29px;
    height: 29px;
    display: grid;
    place-items: center;
    border-radius: 8px;
    background: var(--report-bg);
    color: var(--report-brown);
    font-size: 11px;
    font-weight: 850;
}

.report-category-name strong {
    overflow: hidden;
    color: var(--report-text);
    font-size: 14px;
    font-weight: 750;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.report-category-metrics {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 3px;
}

.report-category-metrics small {
    color: var(--report-muted);
    font-size: 11px;
}

.report-category-metrics b {
    color: var(--report-brown);
    font-size: 13px;
}

/* Inventory */
.report-inventory-card {
    margin-bottom: 18px;
}

.report-inventory-kpis {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 10px;
    padding: 0 22px 18px;
}

.report-inventory-kpis > div {
    min-height: 105px;
    padding: 15px;
    border: 1px solid #EEF0F2;
    border-radius: 10px;
    background: var(--report-bg);
}

.report-inventory-kpis small {
    display: block;
    color: var(--report-muted);
    font-size: 11px;
    line-height: 1.3;
    font-weight: 700;
}

.report-inventory-kpis strong {
    display: block;
    margin-top: 8px;
    color: var(--report-text);
    font-size: 21px;
    line-height: 1.2;
    font-weight: 850;
}

.report-inventory-kpis span {
    display: block;
    margin-top: 4px;
    color: var(--report-muted);
    font-size: 10px;
}

.stock-value strong {
    color: var(--report-brown) !important;
}
.stock-low strong {
    color: var(--report-orange) !important;
}
.stock-out strong {
    color: var(--report-red) !important;
}

.report-table-wrap {
    width: 100%;
    overflow-x: auto;
}

.report-table {
    width: 100%;
    min-width: 590px;
    border-collapse: collapse;
}

.report-table th {
    padding: 12px 18px;
    border-top: 1px solid #EEF0F2;
    border-bottom: 1px solid #E4E7EC;
    background: #FAFAF9;
    color: #475467;
    font-size: 11px;
    line-height: 1.3;
    font-weight: 820;
    text-align: left;
    white-space: nowrap;
}

.report-table td {
    padding: 14px 18px;
    border-bottom: 1px solid #EEF0F2;
    color: #475467;
    font-size: 13px;
    line-height: 1.4;
    vertical-align: middle;
}

.report-table tbody tr:last-child td {
    border-bottom: 0;
}

.report-table tbody tr:hover {
    background: #FCFBF9;
}

.report-table td strong {
    color: var(--report-text);
    font-weight: 750;
}

.report-badge {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    min-height: 29px;
    padding: 5px 9px;
    border-radius: 8px;
    font-size: 11px;
    line-height: 1.2;
    font-weight: 800;
    white-space: nowrap;
}

.report-badge i {
    width: 7px;
    height: 7px;
    border-radius: 50%;
}

.report-badge.warning {
    color: var(--report-orange);
    background: var(--report-orange-bg);
}
.report-badge.warning i {
    background: var(--report-orange);
}

.report-badge.danger {
    color: var(--report-red);
    background: var(--report-red-bg);
}
.report-badge.danger i {
    background: var(--report-red);
}

.report-stock-ok {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 0 22px 20px;
    padding: 14px 15px;
    border: 1px solid #BBF7D0;
    border-radius: 10px;
    background: var(--report-green-bg);
}

.report-stock-ok > span {
    width: 31px;
    height: 31px;
    display: grid;
    place-items: center;
    border-radius: 8px;
    background: #D1FAE5;
    color: var(--report-green);
    font-weight: 850;
}

.report-stock-ok strong {
    display: block;
    color: #166534;
    font-size: 13px;
}

.report-stock-ok p {
    margin: 3px 0 0;
    color: #4B5563;
    font-size: 11px;
}

/* Purchases */
.report-purchase-card {
    min-height: 100%;
}

.report-purchase-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    padding: 0 22px;
}

.report-purchase-grid > div {
    padding: 16px;
    border: 1px solid #EEF0F2;
    border-radius: 10px;
    background: var(--report-bg);
}

.report-purchase-grid > div:last-child {
    grid-column: 1 / -1;
    background: var(--report-brown-soft);
    border-color: #E8DDD3;
}

.report-purchase-grid small {
    display: block;
    color: var(--report-muted);
    font-size: 11px;
    font-weight: 700;
}

.report-purchase-grid strong {
    display: block;
    margin-top: 7px;
    color: var(--report-text);
    font-size: 21px;
    line-height: 1.2;
    font-weight: 850;
}

.report-purchase-total strong {
    color: var(--report-brown);
}

.report-info-note {
    display: flex;
    gap: 10px;
    margin: 16px 22px 22px;
    padding: 13px;
    border-radius: 9px;
    background: var(--report-bg);
}

.report-info-note > span {
    width: 23px;
    height: 23px;
    flex: 0 0 23px;
    display: grid;
    place-items: center;
    border-radius: 50%;
    background: var(--report-brown-soft);
    color: var(--report-brown);
    font-size: 12px;
    font-weight: 850;
}

.report-info-note p {
    margin: 0;
    color: var(--report-muted);
    font-size: 11px;
    line-height: 1.6;
}

.report-info-note strong {
    color: var(--report-text);
}

/* Empty states */
.report-empty {
    min-height: 180px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 25px 15px;
    text-align: center;
}

.report-empty-chart {
    height: 255px;
}

.report-empty-icon {
    width: 46px;
    height: 46px;
    display: grid;
    place-items: center;
    margin-bottom: 10px;
    border-radius: 12px;
    background: var(--report-brown-soft);
    color: var(--report-brown);
    font-size: 17px;
    font-weight: 850;
}

.report-empty strong {
    color: var(--report-text);
    font-size: 14px;
    line-height: 1.35;
    font-weight: 800;
}

.report-empty p {
    margin: 5px 0 0;
    color: var(--report-muted);
    font-size: 12px;
    line-height: 1.5;
}

.report-table-empty {
    min-height: 145px;
}

/* Responsive */
@media (max-width: 1200px) {
    .report-grid-main {
        grid-template-columns: 1fr;
    }

    .report-filter {
        grid-template-columns: 1fr 1fr auto auto;
    }
}

@media (max-width: 950px) {
    .report-page {
        padding: 22px 20px 42px;
    }

    .report-kpis {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .report-grid-two {
        grid-template-columns: 1fr;
    }

    .report-inventory-kpis {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .report-header {
        align-items: flex-start;
        flex-direction: column;
    }
}

@media (max-width: 650px) {
    .report-page {
        padding: 18px 14px 32px;
    }

    .report-header h1 {
        font-size: 25px;
    }

    .report-header p {
        font-size: 13px;
    }

    .report-export {
        width: 100%;
    }

    .report-export-btn {
        flex: 1;
        min-width: 0;
    }

    .report-filter {
        grid-template-columns: 1fr;
    }

    .report-apply,
    .report-clear {
        width: 100%;
    }

    .report-period-note em {
        width: 100%;
        margin-left: 15px;
    }

    .report-kpis {
        grid-template-columns: 1fr;
    }

    .report-kpi {
        min-height: 135px;
    }

    .report-kpi strong {
        font-size: 25px;
    }

    .report-card-header {
        padding: 18px 16px 14px;
    }

    .report-card-total {
        display: none;
    }

    .report-chart {
        margin-left: 14px;
        margin-right: 14px;
        gap: 4px;
    }

    .report-chart-value {
        font-size: 8px;
    }

    .report-chart-col small {
        font-size: 9px;
    }

    .report-payment-list,
    .report-ranking-list {
        padding-left: 16px;
        padding-right: 16px;
    }

    .report-inventory-kpis {
        grid-template-columns: 1fr 1fr;
        padding-left: 16px;
        padding-right: 16px;
    }

    .report-stock-ok {
        margin-left: 16px;
        margin-right: 16px;
    }

    .report-purchase-grid {
        padding-left: 16px;
        padding-right: 16px;
    }

    .report-info-note {
        margin-left: 16px;
        margin-right: 16px;
    }

    .report-table th,
    .report-table td {
        padding-left: 13px;
        padding-right: 13px;
    }
}
</style>
@endpush
