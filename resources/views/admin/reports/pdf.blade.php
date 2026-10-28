<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Clothing Store Report</title>
<style>
body{font-family:Arial,sans-serif;color:#24201E;margin:30px;font-size:11px}
h1{font-size:22px;margin:0 0 5px}h2{font-size:15px;margin:22px 0 8px}
.meta{color:#8B8580;margin-bottom:20px}
table{width:100%;border-collapse:collapse;margin-bottom:16px}
th,td{border:1px solid #ddd;padding:7px;text-align:left}
th{background:#F5EDE3}td.num,th.num{text-align:right}
.print{position:fixed;right:20px;top:20px;padding:9px 14px;border:0;border-radius:8px;background:#6F4E37;color:#fff;font-weight:bold;cursor:pointer}
@media print{.print{display:none}body{margin:12px}}
</style>
</head>
<body>
<button class="print" onclick="window.print()">Print / Save as PDF</button>
<h1>CLOTHING STORE — REPORT</h1>
<div class="meta">From: {{ $dateFrom ?: 'All' }} &nbsp; | &nbsp; To: {{ $dateTo ?: 'All' }}</div>

<h2>Sales Summary</h2>
<table><tr><th>Total Sales</th><td class="num">${{ number_format($stats['total_sales'],2) }}</td></tr><tr><th>Total Orders</th><td class="num">{{ number_format($stats['total_orders']) }}</td></tr><tr><th>Items Sold</th><td class="num">{{ number_format($stats['items_sold']) }}</td></tr><tr><th>Average Order Value</th><td class="num">${{ number_format($stats['average_order_value'],2) }}</td></tr></table>

<h2>Profit Summary</h2>
<table><tr><th>Revenue</th><td class="num">${{ number_format($profit['revenue'],2) }}</td></tr><tr><th>COGS</th><td class="num">${{ number_format($profit['cogs'],2) }}</td></tr><tr><th>Gross Profit</th><td class="num">${{ number_format($profit['gross_profit'],2) }}</td></tr><tr><th>Gross Margin</th><td class="num">{{ number_format($profit['gross_margin'],1) }}%</td></tr></table>

<h2>Product Report</h2>
<table><tr><th>Product</th><th class="num">Qty</th><th class="num">Revenue</th><th class="num">Cost</th><th class="num">Profit</th></tr>
@forelse($products as $row)<tr><td>{{ $row->name }}</td><td class="num">{{ number_format($row->quantity) }}</td><td class="num">${{ number_format($row->revenue,2) }}</td><td class="num">${{ number_format($row->cost,2) }}</td><td class="num">${{ number_format($row->profit,2) }}</td></tr>@empty<tr><td colspan="5">No records.</td></tr>@endforelse
</table>

<h2>Category Report</h2>
<table><tr><th>Category</th><th class="num">Qty</th><th class="num">Revenue</th><th class="num">Cost</th><th class="num">Profit</th></tr>
@forelse($categories as $row)<tr><td>{{ $row->name }}</td><td class="num">{{ number_format($row->quantity) }}</td><td class="num">${{ number_format($row->revenue,2) }}</td><td class="num">${{ number_format($row->cost,2) }}</td><td class="num">${{ number_format($row->profit,2) }}</td></tr>@empty<tr><td colspan="5">No records.</td></tr>@endforelse
</table>

<h2>Gender Report</h2>
<table><tr><th>Gender</th><th class="num">Qty</th><th class="num">Revenue</th><th class="num">Cost</th><th class="num">Profit</th></tr>
@forelse($genders as $row)<tr><td>{{ $row->name }}</td><td class="num">{{ number_format($row->quantity) }}</td><td class="num">${{ number_format($row->revenue,2) }}</td><td class="num">${{ number_format($row->cost,2) }}</td><td class="num">${{ number_format($row->profit,2) }}</td></tr>@empty<tr><td colspan="5">No records.</td></tr>@endforelse
</table>

<h2>Inventory Summary</h2>
<table><tr><th>Current Stock</th><td class="num">{{ number_format($inventory['current_stock']) }}</td></tr><tr><th>Stock Value</th><td class="num">${{ number_format($inventory['stock_value'],2) }}</td></tr><tr><th>Low Stock</th><td class="num">{{ number_format($inventory['low_stock']) }}</td></tr><tr><th>Out of Stock</th><td class="num">{{ number_format($inventory['out_of_stock']) }}</td></tr></table>

<h2>Purchase Summary</h2>
<table><tr><th>Total Purchases</th><td class="num">{{ number_format($purchases['total_purchases']) }}</td></tr><tr><th>Purchase Cost</th><td class="num">${{ number_format($purchases['purchase_cost'],2) }}</td></tr><tr><th>Items Purchased</th><td class="num">{{ number_format($purchases['items_purchased']) }}</td></tr></table>

</body>
</html>
