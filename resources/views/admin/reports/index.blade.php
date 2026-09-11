@extends('admin.layouts.app')

@section('title', 'Reports')

@push('styles')
<style>
.report-page{padding:4px 2px 30px}
.report-header{display:flex;justify-content:space-between;gap:18px;align-items:flex-end;margin-bottom:18px}
.report-title{font-size:22px;font-weight:800;color:#24201E}
.report-subtitle{font-size:11px;color:#8B8580;margin-top:5px}
.report-actions{display:flex;gap:8px;flex-wrap:wrap}
.report-btn{height:38px;padding:0 13px;border-radius:9px;border:1px solid rgba(44,30,23,.10);background:#fff;color:#6F4E37;text-decoration:none;font-size:11px;font-weight:800;display:inline-flex;align-items:center;gap:7px}
.report-btn.primary{background:#6F4E37;color:#fff;border-color:#6F4E37}
.report-btn.success{background:#5F8D5A;color:#fff;border-color:#5F8D5A}
.report-filter{background:#fff;border:1px solid rgba(44,30,23,.08);border-radius:14px;padding:13px;margin-bottom:16px}
.report-filter form{display:flex;align-items:end;gap:10px;flex-wrap:wrap}
.report-field{display:flex;flex-direction:column;gap:5px;min-width:150px}
.report-field label{font-size:9px;font-weight:800;color:#8B8580;text-transform:uppercase;letter-spacing:.5px}
.report-field input{height:37px;border:1px solid #e7e0d9;border-radius:8px;padding:0 10px;font-size:11px;color:#24201E;background:#FAF8F5}
.report-grid-4{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:14px}
.report-grid-2{display:grid;grid-template-columns:repeat(2,1fr);gap:14px;margin-bottom:14px}
.report-card{background:#fff;border:1px solid rgba(44,30,23,.08);border-radius:15px;padding:16px;overflow:hidden}
.report-kpi{min-height:105px}
.report-kpi-label{font-size:9px;font-weight:800;color:#8B8580;text-transform:uppercase;letter-spacing:.5px}
.report-kpi-value{font-size:22px;font-weight:800;color:#24201E;margin-top:9px}
.report-kpi-note{font-size:9px;color:#8B8580;margin-top:5px}
.report-section-title{font-size:14px;font-weight:800;color:#24201E;margin:0}
.report-section-sub{font-size:9px;color:#8B8580;margin:4px 0 14px}
.report-table-wrap{overflow:auto}
.report-table{width:100%;border-collapse:collapse;min-width:520px}
.report-table th{background:#FAF8F5;color:#8B8580;font-size:9px;text-transform:uppercase;letter-spacing:.4px;text-align:left;padding:9px}
.report-table td{border-top:1px solid #f0ebe6;color:#24201E;font-size:10px;padding:9px}
.report-table td.num,.report-table th.num{text-align:right}
.report-empty{text-align:center;padding:28px;color:#8B8580;font-size:10px}
.report-chart{height:190px;display:flex;align-items:flex-end;gap:8px;padding:8px 2px 0}
.report-bar-wrap{height:100%;flex:1;display:flex;flex-direction:column;justify-content:flex-end;align-items:center;gap:5px;min-width:25px}
.report-bar{width:100%;max-width:34px;background:#6F4E37;border-radius:6px 6px 2px 2px;min-height:2px}
.report-bar-label{font-size:8px;color:#8B8580;white-space:nowrap}
.report-bar-value{font-size:8px;color:#24201E;font-weight:700}
.report-progress{height:7px;background:#F5EDE3;border-radius:99px;overflow:hidden;margin-top:6px}
.report-progress i{display:block;height:100%;background:#6F4E37;border-radius:99px}
.report-badge{display:inline-flex;padding:4px 7px;border-radius:6px;font-size:8px;font-weight:800}
.report-badge.good{background:#eaf3e8;color:#5F8D5A}
.report-badge.bad{background:#f9e9e5;color:#C85C4A}
.report-summary-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:8px}
.report-mini{background:#FAF8F5;border-radius:9px;padding:10px}
.report-mini span{display:block;font-size:8px;color:#8B8580}
.report-mini strong{display:block;margin-top:4px;font-size:13px;color:#24201E}
@media(max-width:1000px){.report-grid-4{grid-template-columns:repeat(2,1fr)}}
@media(max-width:700px){.report-header{align-items:stretch;flex-direction:column}.report-grid-4,.report-grid-2{grid-template-columns:1fr}.report-summary-grid{grid-template-columns:repeat(2,1fr)}}
</style>
@endpush

@section('content')
<div class="report-page">

    <div class="report-header">
        <div>
            <div class="report-title">Reports</div>
            <div class="report-subtitle">Track sales, profit, products, inventory and purchases from database records.</div>
        </div>

        <div class="report-actions">
            <a class="report-btn" href="{{ route('admin.reports.export.csv', request()->query()) }}">↓ CSV</a>
            <a class="report-btn" href="{{ route('admin.reports.export.excel', request()->query()) }}">↓ Excel</a>
            <a class="report-btn primary" href="{{ route('admin.reports.export.pdf', request()->query()) }}" target="_blank">↓ PDF</a>
        </div>
    </div>

    <div class="report-filter">
        <form method="GET" action="{{ route('admin.reports.index') }}">
            <div class="report-field">
                <label>Date From</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}">
            </div>
            <div class="report-field">
                <label>Date To</label>
                <input type="date" name="date_to" value="{{ $dateTo }}">
            </div>
            <button class="report-btn primary" type="submit">Apply Filter</button>
            <a class="report-btn" href="{{ route('admin.reports.index') }}">Clear</a>
        </form>
    </div>

    <div class="report-grid-4">
        <div class="report-card report-kpi">
            <div class="report-kpi-label">Total Sales</div>
            <div class="report-kpi-value">${{ number_format($stats['total_sales'], 2) }}</div>
            <div class="report-kpi-note">Completed sales only</div>
        </div>
        <div class="report-card report-kpi">
            <div class="report-kpi-label">Total Orders</div>
            <div class="report-kpi-value">{{ number_format($stats['total_orders']) }}</div>
            <div class="report-kpi-note">Completed orders</div>
        </div>
        <div class="report-card report-kpi">
            <div class="report-kpi-label">Items Sold</div>
            <div class="report-kpi-value">{{ number_format($stats['items_sold']) }}</div>
            <div class="report-kpi-note">Quantity from sale items</div>
        </div>
        <div class="report-card report-kpi">
            <div class="report-kpi-label">Average Order Value</div>
            <div class="report-kpi-value">${{ number_format($stats['average_order_value'], 2) }}</div>
            <div class="report-kpi-note">Sales ÷ orders</div>
        </div>
    </div>

    <div class="report-card" style="margin-bottom:14px">
        <h2 class="report-section-title">Profit Report</h2>
        <p class="report-section-sub">Revenue and gross profit calculated from completed sale items and product variant cost price.</p>
        <div class="report-summary-grid">
            <div class="report-mini"><span>Revenue</span><strong>${{ number_format($profit['revenue'],2) }}</strong></div>
            <div class="report-mini"><span>COGS</span><strong>${{ number_format($profit['cogs'],2) }}</strong></div>
            <div class="report-mini"><span>Gross Profit</span><strong>${{ number_format($profit['gross_profit'],2) }}</strong></div>
            <div class="report-mini"><span>Gross Margin</span><strong>{{ number_format($profit['gross_margin'],1) }}%</strong></div>
        </div>
    </div>

    <div class="report-grid-2">
        @foreach([
            ['title'=>'Daily Sales','items'=>$dailySales],
            ['title'=>'Weekly Sales','items'=>$weeklySales],
        ] as $chart)
            <div class="report-card">
                <h2 class="report-section-title">{{ $chart['title'] }}</h2>
                <p class="report-section-sub">Only dates/weeks containing database sales are shown.</p>
                @if(count($chart['items']))
                    @php $max = max(1, collect($chart['items'])->max('value')); @endphp
                    <div class="report-chart">
                        @foreach($chart['items'] as $item)
                            <div class="report-bar-wrap" title="{{ $item['date'] ?? $item['label'] }}">
                                <span class="report-bar-value">${{ number_format($item['value'],0) }}</span>
                                <div class="report-bar" style="height:{{ max(2, ($item['value'] / $max) * 125) }}px"></div>
                                <span class="report-bar-label">{{ $item['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="report-empty">No sales records in this date range.</div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="report-card" style="margin-bottom:14px">
        <h2 class="report-section-title">Monthly Sales</h2>
        <p class="report-section-sub">Monthly revenue from completed sales.</p>
        @if(count($monthlySales))
            @php $maxMonth = max(1, collect($monthlySales)->max('value')); @endphp
            <div class="report-chart">
                @foreach($monthlySales as $item)
                    <div class="report-bar-wrap">
                        <span class="report-bar-value">${{ number_format($item['value'],0) }}</span>
                        <div class="report-bar" style="height:{{ max(2, ($item['value'] / $maxMonth) * 125) }}px"></div>
                        <span class="report-bar-label">{{ $item['label'] }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="report-empty">No sales records in this date range.</div>
        @endif
    </div>

    <div class="report-grid-2">
        <div class="report-card">
            <h2 class="report-section-title">Best Selling Products</h2>
            <p class="report-section-sub">Ranked by quantity sold.</p>
            <div class="report-table-wrap">
                <table class="report-table">
                    <thead><tr><th>Product</th><th class="num">Qty</th><th class="num">Revenue</th><th class="num">Profit</th></tr></thead>
                    <tbody>
                    @forelse($bestProducts as $row)
                        <tr><td>{{ $row->name }}</td><td class="num">{{ number_format($row->quantity) }}</td><td class="num">${{ number_format($row->revenue,2) }}</td><td class="num">${{ number_format($row->profit,2) }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="report-empty">No product sales records.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="report-card">
            <h2 class="report-section-title">Lowest Selling Products</h2>
            <p class="report-section-sub">Products that have recorded sales, ranked lowest first.</p>
            <div class="report-table-wrap">
                <table class="report-table">
                    <thead><tr><th>Product</th><th class="num">Qty</th><th class="num">Revenue</th><th class="num">Profit</th></tr></thead>
                    <tbody>
                    @forelse($lowestProducts as $row)
                        <tr><td>{{ $row->name }}</td><td class="num">{{ number_format($row->quantity) }}</td><td class="num">${{ number_format($row->revenue,2) }}</td><td class="num">${{ number_format($row->profit,2) }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="report-empty">No product sales records.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="report-grid-2">
        <div class="report-card">
            <h2 class="report-section-title">Sales by Category</h2>
            <p class="report-section-sub">Quantity, revenue and profit by category.</p>
            <div class="report-table-wrap">
                <table class="report-table">
                    <thead><tr><th>Category</th><th class="num">Qty</th><th class="num">Revenue</th><th class="num">Profit</th></tr></thead>
                    <tbody>
                    @forelse($categories as $row)
                        <tr><td>{{ $row->name }}</td><td class="num">{{ number_format($row->quantity) }}</td><td class="num">${{ number_format($row->revenue,2) }}</td><td class="num">${{ number_format($row->profit,2) }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="report-empty">No category sales records.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="report-card">
            <h2 class="report-section-title">Sales by Gender</h2>
            <p class="report-section-sub">Quantity, revenue and profit by product gender.</p>
            <div class="report-table-wrap">
                <table class="report-table">
                    <thead><tr><th>Gender</th><th class="num">Qty</th><th class="num">Revenue</th><th class="num">Profit</th></tr></thead>
                    <tbody>
                    @forelse($genders as $row)
                        <tr><td>{{ $row->name }}</td><td class="num">{{ number_format($row->quantity) }}</td><td class="num">${{ number_format($row->revenue,2) }}</td><td class="num">${{ number_format($row->profit,2) }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="report-empty">No gender sales records.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="report-grid-2">
        <div class="report-card">
            <h2 class="report-section-title">Inventory Report</h2>
            <p class="report-section-sub">Current database inventory status.</p>
            <div class="report-summary-grid">
                <div class="report-mini"><span>Current Stock</span><strong>{{ number_format($inventory['current_stock']) }}</strong></div>
                <div class="report-mini"><span>Stock Value</span><strong>${{ number_format($inventory['stock_value'],2) }}</strong></div>
                <div class="report-mini"><span>Low Stock</span><strong>{{ number_format($inventory['low_stock']) }}</strong></div>
                <div class="report-mini"><span>Out of Stock</span><strong>{{ number_format($inventory['out_of_stock']) }}</strong></div>
            </div>
        </div>

        <div class="report-card">
            <h2 class="report-section-title">Purchase Report</h2>
            <p class="report-section-sub">Purchase records within the selected date range.</p>
            <div class="report-summary-grid">
                <div class="report-mini"><span>Total Purchases</span><strong>{{ number_format($purchases['total_purchases']) }}</strong></div>
                <div class="report-mini"><span>Purchase Cost</span><strong>${{ number_format($purchases['purchase_cost'],2) }}</strong></div>
                <div class="report-mini"><span>Items Purchased</span><strong>{{ number_format($purchases['items_purchased']) }}</strong></div>
            </div>
        </div>
    </div>

    <div class="report-grid-2">
        <div class="report-card">
            <h2 class="report-section-title">Payment Summary</h2>
            <p class="report-section-sub">Based on completed sales payment method stored in sales.</p>
            <div class="report-table-wrap">
                <table class="report-table">
                    <thead><tr><th>Method</th><th class="num">Orders</th><th class="num">Revenue</th></tr></thead>
                    <tbody>
                    @foreach($paymentMethods as $row)
                        <tr><td>{{ $row['label'] }}</td><td class="num">{{ number_format($row['orders']) }}</td><td class="num">${{ number_format($row['revenue'],2) }}</td></tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="report-card">
            <h2 class="report-section-title">Stock Movement</h2>
            <p class="report-section-sub">Fast and slow movement from inventory movement records.</p>
            <div class="report-table-wrap">
                <table class="report-table">
                    <thead><tr><th>Product</th><th class="num">Sold</th><th class="num">In</th></tr></thead>
                    <tbody>
                    @forelse($fastMoving as $row)
                        <tr><td>{{ $row->name }}</td><td class="num">{{ number_format($row->sold_quantity) }}</td><td class="num">{{ number_format($row->in_quantity) }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="report-empty">No movement records.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="report-grid-2">
        <div class="report-card">
            <h2 class="report-section-title">Low Stock</h2>
            <div class="report-table-wrap">
                <table class="report-table">
                    <thead><tr><th>Product</th><th>Variant</th><th class="num">Stock</th></tr></thead>
                    <tbody>
                    @forelse($lowStockRows as $row)
                        <tr><td>{{ $row->name }}</td><td>{{ $row->size ?: '—' }} / {{ $row->color ?: '—' }}</td><td class="num"><span class="report-badge bad">{{ $row->quantity }}</span></td></tr>
                    @empty
                        <tr><td colspan="3" class="report-empty">No low-stock records.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="report-card">
            <h2 class="report-section-title">Out of Stock</h2>
            <div class="report-table-wrap">
                <table class="report-table">
                    <thead><tr><th>Product</th><th>Variant</th><th class="num">Stock</th></tr></thead>
                    <tbody>
                    @forelse($outOfStockRows as $row)
                        <tr><td>{{ $row->name }}</td><td>{{ $row->size ?: '—' }} / {{ $row->color ?: '—' }}</td><td class="num"><span class="report-badge bad">0</span></td></tr>
                    @empty
                        <tr><td colspan="3" class="report-empty">No out-of-stock records.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="report-card">
        <h2 class="report-section-title">Slow Moving Products</h2>
        <p class="report-section-sub">Products with no recorded outgoing movement in inventory movements.</p>
        <div class="report-table-wrap">
            <table class="report-table">
                <thead><tr><th>Product</th><th class="num">Sold</th><th class="num">Stock In</th></tr></thead>
                <tbody>
                @forelse($slowMoving as $row)
                    <tr><td>{{ $row->name }}</td><td class="num">{{ number_format($row->sold_quantity) }}</td><td class="num">{{ number_format($row->in_quantity) }}</td></tr>
                @empty
                    <tr><td colspan="3" class="report-empty">No slow-moving records.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
