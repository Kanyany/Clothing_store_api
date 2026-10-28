@extends('admin.layouts.app')

@section('title', 'Orders')

@section('content')
@php
    $statuses = [
        'pending'    => 'Pending',
        'confirmed'  => 'Confirmed',
        'processing' => 'Processing',
        'shipped'    => 'Shipped',
        'delivered'  => 'Delivered',
        'cancelled'  => 'Cancelled',
    ];

    $activeStatus = request('status');

    // Optional: controller can pass $statusCounts = ['pending' => 4, ...]
    $statusCounts = $statusCounts ?? null;

    $orderTotal = function ($order) {
        $direct = $order->total_amount ?? $order->total ?? $order->grand_total ?? null;
        if ($direct !== null) {
            return (float) $direct;
        }
        return (float) $order->items->sum(function ($i) {
            $price = $i->price ?? $i->unit_price ?? optional($i->productVariant)->selling_price ?? 0;
            return $price * ($i->quantity ?? 1);
        });
    };

    $paidWords   = ['paid', 'completed', 'complete', 'success', 'succeeded', 'settled', 'captured', 'approved'];
    $failedWords = ['failed', 'cancelled', 'canceled', 'refunded', 'declined', 'expired'];

    // Whole order -> 'paid' | 'failed' | 'unpaid'
    $orderPay = function ($order) use ($paidWords, $failedWords) {
        $values = $order->payments
            ->map(fn ($p) => strtolower(trim((string) ($p->status ?? $p->payment_status ?? ''))))
            ->filter()->all();
        if (!empty($order->payment_status)) $values[] = strtolower((string) $order->payment_status);

        if (!empty($order->paid_at) || ($order->is_paid ?? false) || count(array_intersect($values, $paidWords))) return 'paid';
        if (count(array_intersect($values, $failedWords))) return 'failed';
        return 'unpaid';
    };

    $imgUrl = function ($image) {
        if (!$image) return null;
        return str_starts_with($image, 'http') ? $image : url('/api/product-image/' . $image);
    };
@endphp

<style>
    .ord {
        --ord-ink: #2a211b;
        --ord-muted: #7d7068;
        --ord-line: #e8e1db;
        --ord-surface: #ffffff;
        --ord-soft: #f7f4f1;
        --ord-accent: #6b4a35;
        --ord-accent-ink: #ffffff;
        color: var(--ord-ink);
        font-size: 14px;
    }
    .ord *, .ord *::before, .ord *::after { box-sizing: border-box; }

    .ord-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 20px; }
    .ord-title { font-size: 26px; font-weight: 700; margin: 0; letter-spacing: -0.01em; }
    .ord-sub { margin: 4px 0 0; color: var(--ord-muted); }

    .ord-tabs { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 14px; }
    .ord-tab {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 7px 14px; border-radius: 999px;
        border: 1px solid var(--ord-line); background: var(--ord-surface);
        color: var(--ord-ink); text-decoration: none; font-weight: 500;
    }
    .ord-tab:hover { background: var(--ord-soft); }
    .ord-tab.is-active { background: var(--ord-accent); border-color: var(--ord-accent); color: var(--ord-accent-ink); }
    .ord-tab-count { font-size: 12px; opacity: .75; font-variant-numeric: tabular-nums; }

    .ord-panel { background: var(--ord-surface); border: 1px solid var(--ord-line); border-radius: 14px; overflow: hidden; }

    .ord-filter { display: flex; gap: 10px; flex-wrap: wrap; padding: 14px; border-bottom: 1px solid var(--ord-line); }
    .ord-input, .ord-select {
        height: 40px; padding: 0 12px; border: 1px solid var(--ord-line); border-radius: 10px;
        background: var(--ord-surface); color: var(--ord-ink); font: inherit;
    }
    .ord-input { flex: 1 1 260px; min-width: 200px; }
    .ord-input:focus, .ord-select:focus, .ord-btn:focus-visible, .ord-tab:focus-visible, .ord-link:focus-visible {
        outline: 2px solid var(--ord-accent); outline-offset: 2px;
    }
    .ord-btn {
        height: 40px; padding: 0 18px; border-radius: 10px; border: 1px solid var(--ord-accent);
        background: var(--ord-accent); color: var(--ord-accent-ink); font: inherit; font-weight: 600; cursor: pointer;
        display: inline-flex; align-items: center; justify-content: center; text-decoration: none;
    }
    .ord-btn.is-ghost { background: transparent; color: var(--ord-ink); border-color: var(--ord-line); }
    .ord-btn.is-ghost:hover { background: var(--ord-soft); }

    .ord-scroll { overflow-x: auto; }
    .ord-table { width: 100%; border-collapse: collapse; min-width: 860px; }
    .ord-table th {
        text-align: left; font-weight: 600; color: var(--ord-muted); font-size: 13px;
        padding: 12px 16px; background: var(--ord-soft); border-bottom: 1px solid var(--ord-line); white-space: nowrap;
    }
    .ord-table td { padding: 14px 16px; border-bottom: 1px solid var(--ord-line); vertical-align: middle; }
    .ord-table tr:last-child td { border-bottom: 0; }
    .ord-table tbody tr:hover td { background: #fcfaf8; }
    .ord-num { text-align: right; font-variant-numeric: tabular-nums; }

    .ord-id { font-weight: 700; color: var(--ord-ink); text-decoration: none; }
    .ord-id:hover { text-decoration: underline; }

    .ord-person { display: flex; align-items: center; gap: 12px; min-width: 0; }
    .ord-avatar {
        width: 36px; height: 36px; border-radius: 50%; flex: none;
        background: var(--ord-soft); border: 1px solid var(--ord-line); color: var(--ord-accent);
        display: grid; place-items: center; font-weight: 700;
    }
    .ord-person-name { font-weight: 600; }
    .ord-person-mail { color: var(--ord-muted); font-size: 13px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 220px; }

    .ord-items { display: flex; align-items: center; gap: 10px; }
    .ord-thumb { width: 40px; height: 48px; border-radius: 8px; object-fit: cover; background: var(--ord-soft); border: 1px solid var(--ord-line); flex: none; }
    .ord-thumb.is-empty { display: grid; place-items: center; color: var(--ord-muted); font-size: 11px; }
    .ord-items-name { font-weight: 500; max-width: 190px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .ord-items-more { color: var(--ord-muted); font-size: 13px; }

    .ord-badge {
        display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 999px;
        font-size: 12.5px; font-weight: 600; white-space: nowrap;
    }
    .ord-badge::before { content: ""; width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
    .ord-badge--pending    { background: #fff4dc; color: #8a5a00; }
    .ord-badge--confirmed  { background: #e3eefc; color: #1c4f94; }
    .ord-badge--processing { background: #efe7fb; color: #5a3a96; }
    .ord-badge--shipped    { background: #dff3f4; color: #17686e; }
    .ord-badge--delivered  { background: #e0f4e6; color: #1f6b38; }
    .ord-badge--cancelled  { background: #fbe4e2; color: #a2342b; }
    .ord-badge--paid       { background: #e0f4e6; color: #1f6b38; }
    .ord-badge--unpaid     { background: #f0ece8; color: #6b5f57; }
    .ord-badge--failed     { background: #fbe4e2; color: #a2342b; }

    .ord-date { white-space: nowrap; }
    .ord-date small { display: block; color: var(--ord-muted); }

    .ord-link { color: var(--ord-accent); font-weight: 600; text-decoration: none; }
    .ord-link:hover { text-decoration: underline; }
    .ord-view-btn { border: 0; padding: 6px 0; background: transparent; font: inherit; cursor: pointer; }
    .ord-row-actions { display:flex; align-items:center; justify-content:flex-end; gap:12px; white-space:nowrap; }
    .ord-receipt-link { display:inline-flex; align-items:center; justify-content:center; padding:7px 10px; border:1px solid var(--ord-line); border-radius:9px; color:var(--ord-accent); text-decoration:none; font-weight:700; }
    .ord-receipt-link:hover { background:var(--ord-soft); }

    /* Order details alert modal */
    .ord-detail-modal { --ord-ink: #2a211b; --ord-muted: #7d7068; --ord-line: #e8e1db; --ord-surface: #ffffff; --ord-soft: #f7f4f1; --ord-accent: #6b4a35; --ord-accent-ink: #ffffff; position: fixed; inset: 0; z-index: 99999; display: grid; place-items: center; padding: 20px; background: rgba(35, 27, 22, .62); }
    .ord-detail-modal[hidden] { display: none; }
    .ord-detail-card { width: min(820px, 100%); max-height: min(88vh, 900px); display: flex; flex-direction: column; overflow: hidden; border: 1px solid var(--ord-line); border-radius: 18px; background: #fff; box-shadow: 0 28px 80px rgba(0,0,0,.28); }
    .ord-detail-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; padding: 22px 26px; border-bottom: 1px solid var(--ord-line); }
    .ord-detail-eyebrow { color: var(--ord-accent); font-size: 11px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
    .ord-detail-title { margin: 4px 0 3px; font-size: 25px; font-weight: 800; }
    .ord-detail-sub { margin: 0; color: var(--ord-muted); }
    .ord-detail-close { width: 40px; height: 40px; flex: none; border: 1px solid var(--ord-line); border-radius: 10px; background: #fff; color: var(--ord-ink); font-size: 24px; cursor: pointer; }
    .ord-detail-body { overflow: auto; padding: 22px 26px; }
    .ord-detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 18px; }
    .ord-detail-box { min-width: 0; border: 1px solid var(--ord-line); border-radius: 12px; padding: 15px; }
    .ord-detail-label { display: block; margin-bottom: 6px; color: var(--ord-muted); font-size: 12px; }
    .ord-detail-value { font-weight: 700; overflow-wrap: anywhere; }
    .ord-detail-section-title { margin: 20px 0 10px; font-size: 15px; font-weight: 800; }
    .ord-detail-items { display: grid; gap: 10px; }
    .ord-detail-item { display: grid; grid-template-columns: 54px minmax(0,1fr) auto; gap: 12px; align-items: center; padding: 10px; border: 1px solid var(--ord-line); border-radius: 12px; }
    .ord-detail-img { width: 54px; height: 64px; object-fit: cover; border: 1px solid var(--ord-line); border-radius: 8px; background: var(--ord-soft); }
    .ord-detail-item-name { font-weight: 700; }
    .ord-detail-item-meta { margin-top: 3px; color: var(--ord-muted); font-size: 12px; }
    .ord-detail-item-price { text-align: right; font-weight: 700; white-space: nowrap; }
    .ord-detail-summary { margin-top: 16px; padding: 14px 16px; border-radius: 12px; background: var(--ord-soft); }
    .ord-detail-summary-row { display: flex; justify-content: space-between; gap: 12px; padding: 5px 0; }
    .ord-detail-summary-row:last-child { margin-top: 7px; padding-top: 12px; border-top: 1px solid var(--ord-line); font-size: 17px; font-weight: 800; }
    .ord-detail-summary-row span:first-child { color: var(--ord-muted); }
    .ord-muted { color: var(--ord-muted); }
    .ord-detail-foot { display: flex; justify-content: flex-end; flex-wrap: wrap; gap: 10px; padding: 16px 26px; border-top: 1px solid var(--ord-line); }
    .ord-detail-foot .ord-btn { min-width: 130px; }
    body.ord-modal-open { overflow: hidden; }
    @media (max-width: 600px) { .ord-detail-grid { grid-template-columns: 1fr; } .ord-detail-head, .ord-detail-body { padding: 18px; } .ord-detail-foot { padding: 14px 18px; flex-direction: column-reverse; } .ord-detail-foot .ord-btn { width: 100%; } .ord-detail-item { grid-template-columns: 46px minmax(0,1fr); } .ord-detail-img { width: 46px; height: 56px; } .ord-detail-item-price { grid-column: 2; text-align: left; } }

    .ord-empty { text-align: center; padding: 56px 20px; }
    .ord-empty h3 { margin: 0 0 6px; font-size: 18px; }
    .ord-empty p { margin: 0 0 16px; color: var(--ord-muted); }

    .ord-foot { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; padding: 14px 16px; border-top: 1px solid var(--ord-line); color: var(--ord-muted); }

    @media (max-width: 640px) {
        .ord-title { font-size: 22px; }
        .ord-input { flex-basis: 100%; }
    }
</style>

<div class="ord">

    {{-- Header --}}
    <div class="ord-head">
        <div>
            <h1 class="ord-title">Orders</h1>
            <p class="ord-sub">
                {{ number_format($orders->total()) }}
                {{ \Illuminate\Support\Str::plural('order', $orders->total()) }}
                @if (request()->filled('search') || $activeStatus) matching your filters @else from customers @endif
            </p>
        </div>
    </div>

    {{-- Status tabs --}}
    <nav class="ord-tabs" aria-label="Filter orders by status">
        <a href="{{ route('admin.orders.index', request()->except(['status', 'page'])) }}"
           class="ord-tab {{ !$activeStatus ? 'is-active' : '' }}">
            All
            @if ($statusCounts)
                <span class="ord-tab-count">{{ array_sum($statusCounts) }}</span>
            @endif
        </a>

        @foreach ($statuses as $key => $label)
            <a href="{{ route('admin.orders.index', array_merge(request()->except('page'), ['status' => $key])) }}"
               class="ord-tab {{ $activeStatus === $key ? 'is-active' : '' }}">
                {{ $label }}
                @if ($statusCounts)
                    <span class="ord-tab-count">{{ $statusCounts[$key] ?? 0 }}</span>
                @endif
            </a>
        @endforeach
    </nav>

    <div class="ord-panel">

        {{-- Search --}}
        <form method="GET" action="{{ route('admin.orders.index') }}" class="ord-filter">
            <input type="search" name="search" class="ord-input"
                   value="{{ request('search') }}"
                   placeholder="Search by order number, customer name or email"
                   aria-label="Search orders">

            <select name="status" class="ord-select" aria-label="Order status">
                <option value="">All statuses</option>
                @foreach ($statuses as $key => $label)
                    <option value="{{ $key }}" @selected($activeStatus === $key)>{{ $label }}</option>
                @endforeach
            </select>

            <button type="submit" class="ord-btn">Search</button>

            @if (request()->filled('search') || $activeStatus)
                <a href="{{ route('admin.orders.index') }}" class="ord-btn is-ghost">Clear filters</a>
            @endif
        </form>

        @if ($orders->count())
            <div class="ord-scroll">
                <table class="ord-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th class="ord-num">Total</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Placed</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            @php
                                $first   = $order->items->first();
                                $product = optional(optional($first)->productVariant)->product;
                                $thumb   = $imgUrl(optional($product)->image);
                                $count   = $order->items->count();
                                $payClass = $orderPay($order);
                                $customerName = optional($order->user)->name ?? 'Deleted customer';
                                $detailPayload = [
                                    'id' => $order->id,
                                    'status' => (string) $order->status,
                                    'status_label' => $statuses[$order->status] ?? ucfirst((string) $order->status),
                                    'payment_status' => ucfirst($payClass),
                                    'customer' => $customerName,
                                    'email' => optional($order->user)->email,
                                    'phone' => optional($order->user)->phone,
                                    'created_at' => optional($order->created_at)->format('M d, Y \a\t h:i A'),
                                    'total' => $orderTotal($order),
                                    'details_url' => route('admin.orders.show', $order),
                                    'items' => $order->items->map(function ($item) use ($imgUrl) {
                                        $variant = $item->productVariant;
                                        $productItem = optional($variant)->product;
                                        $price = (float) ($item->price ?? $item->unit_price ?? optional($variant)->selling_price ?? 0);
                                        $qty = (int) ($item->quantity ?? 1);
                                        return [
                                            'name' => optional($productItem)->name ?? 'Product removed',
                                            'image' => $imgUrl(optional($productItem)->image),
                                            'color' => optional($variant)->color,
                                            'size' => optional($variant)->size,
                                            'quantity' => $qty,
                                            'unit_price' => $price,
                                            'line_total' => $price * $qty,
                                        ];
                                    })->values()->all(),
                                    'payments' => $order->payments->map(function ($payment) {
                                        return [
                                            'method' => $payment->payment_method ?? $payment->method ?? 'Payment',
                                            'status' => $payment->status ?? $payment->payment_status ?? 'unpaid',
                                            'amount' => isset($payment->amount) ? (float) $payment->amount : null,
                                        ];
                                    })->values()->all(),
                                ];
                            @endphp
                            <tr>
                                <td>
                                    <a class="ord-id" href="{{ route('admin.orders.show', $order) }}">#{{ $order->id }}</a>
                                </td>

                                <td>
                                    <div class="ord-person">
                                        <span class="ord-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($customerName, 0, 1)) }}</span>
                                        <div style="min-width:0">
                                            <div class="ord-person-name">{{ $customerName }}</div>
                                            <div class="ord-person-mail">{{ optional($order->user)->email }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="ord-items">
                                        @if ($thumb)
                                            <img class="ord-thumb" src="{{ $thumb }}" alt="" loading="lazy">
                                        @else
                                            <span class="ord-thumb is-empty">No image</span>
                                        @endif
                                        <div>
                                            <div class="ord-items-name">{{ optional($product)->name ?? 'Product removed' }}</div>
                                            @if ($count > 1)
                                                <div class="ord-items-more">and {{ $count - 1 }} more</div>
                                            @else
                                                <div class="ord-items-more">{{ $count }} item</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="ord-num"><strong>${{ number_format($orderTotal($order), 2) }}</strong></td>

                                <td>
                                    <span class="ord-badge ord-badge--{{ $payClass }}">
                                        {{ ucfirst($payClass) }}
                                    </span>
                                </td>

                                <td>
                                    <span class="ord-badge ord-badge--{{ $order->status }}">
                                        {{ $statuses[$order->status] ?? ucfirst($order->status) }}
                                    </span>
                                </td>

                                <td class="ord-date">
                                    {{ $order->created_at->format('M d, Y') }}
                                    <small>{{ $order->created_at->format('h:i A') }}</small>
                                </td>

                                <td class="ord-num">
                                    <div class="ord-row-actions">
                                        <button type="button" class="ord-link ord-view-btn js-view-order"
                                                data-order="{{ json_encode($detailPayload, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) }}"
                                                aria-haspopup="dialog">View order</button>
                                        <a class="ord-receipt-link" href="{{ route('admin.orders.show', $order) }}" aria-label="Open receipt for order #{{ $order->id }}">Receipt</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="ord-foot">
                <span>Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }}</span>
                <div>{{ $orders->links() }}</div>
            </div>
        @else
            <div class="ord-empty">
                @if (request()->filled('search') || $activeStatus)
                    <h3>No orders match your filters</h3>
                    <p>Try a different order number, name or status.</p>
                    <a href="{{ route('admin.orders.index') }}" class="ord-btn">Clear filters</a>
                @else
                    <h3>No orders yet</h3>
                    <p>Orders placed in the shop app will appear here.</p>
                @endif
            </div>
        @endif
    </div>
</div>
{{-- Order details alert screen: values come from the real order loaded by Laravel. --}}
<div class="ord-detail-modal" id="ord-detail-modal" role="dialog" aria-modal="true" aria-labelledby="ord-detail-title" hidden>
    <section class="ord-detail-card">
        <header class="ord-detail-head">
            <div>
                <div class="ord-detail-eyebrow">Order details</div>
                <h2 class="ord-detail-title" id="ord-detail-title">Order</h2>
                <p class="ord-detail-sub" id="ord-detail-date"></p>
            </div>
            <button type="button" class="ord-detail-close" id="ord-detail-close" aria-label="Close order details">&times;</button>
        </header>
        <div class="ord-detail-body" id="ord-detail-body"></div>
        <footer class="ord-detail-foot">
            <button type="button" class="ord-btn is-ghost" id="ord-detail-dismiss">Close</button>
            <a class="ord-btn" id="ord-detail-open-full" href="#">Open full details</a>
        </footer>
    </section>
</div>

<script>
(function () {
    const modal = document.getElementById('ord-detail-modal');
    const body = document.getElementById('ord-detail-body');
    const title = document.getElementById('ord-detail-title');
    const date = document.getElementById('ord-detail-date');
    const closeBtn = document.getElementById('ord-detail-close');
    const dismissBtn = document.getElementById('ord-detail-dismiss');
    const fullLink = document.getElementById('ord-detail-open-full');
    if (!modal || !body) return;

    // Place modal directly under body so parent overflow/z-index cannot hide it.
    document.body.appendChild(modal);
    let lastFocus = null;
    const money = value => '$' + Number(value || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    const safeText = value => value === null || value === undefined || value === '' ? '—' : String(value);
    const el = (tag, className, text) => {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined && text !== null) node.textContent = String(text);
        return node;
    };
    const badge = (status, label) => {
        const key = String(status || 'unpaid').toLowerCase().replace(/[^a-z0-9_-]/g, '');
        return el('span', 'ord-badge ord-badge--' + key, label || status || 'Unpaid');
    };
    const infoBox = (label, value, extra) => {
        const box = el('div', 'ord-detail-box');
        box.appendChild(el('span', 'ord-detail-label', label));
        const val = el('div', 'ord-detail-value');
        if (extra) val.appendChild(extra); else val.textContent = safeText(value);
        box.appendChild(val);
        return box;
    };
    function openOrder(order) {
        lastFocus = document.activeElement;
        title.textContent = 'Order #' + order.id;
        date.textContent = 'Placed on ' + safeText(order.created_at);
        fullLink.href = order.details_url || '#';
        body.replaceChildren();

        const grid = el('div', 'ord-detail-grid');
        grid.appendChild(infoBox('Customer', order.customer));
        grid.appendChild(infoBox('Email', order.email));
        grid.appendChild(infoBox('Order status', null, badge(order.status, order.status_label)));
        grid.appendChild(infoBox('Payment status', null, badge(String(order.payment_status || 'unpaid').toLowerCase(), order.payment_status || 'Unpaid')));
        body.appendChild(grid);

        body.appendChild(el('h3', 'ord-detail-section-title', 'Order items'));
        const itemsWrap = el('div', 'ord-detail-items');
        const items = Array.isArray(order.items) ? order.items : [];
        if (!items.length) {
            itemsWrap.appendChild(el('p', 'ord-muted', 'No items recorded for this order.'));
        } else {
            items.forEach(item => {
                const row = el('div', 'ord-detail-item');
                if (item.image) {
                    const image = el('img', 'ord-detail-img');
                    image.src = item.image;
                    image.alt = '';
                    image.loading = 'lazy';
                    row.appendChild(image);
                } else {
                    row.appendChild(el('div', 'ord-detail-img is-empty', 'No image'));
                }
                const description = el('div');
                description.appendChild(el('div', 'ord-detail-item-name', safeText(item.name)));
                const meta = [];
                if (item.color) meta.push('Color: ' + item.color);
                if (item.size) meta.push('Size: ' + item.size);
                meta.push('Qty: ' + safeText(item.quantity));
                description.appendChild(el('div', 'ord-detail-item-meta', meta.join(' · ')));
                row.appendChild(description);
                const price = el('div', 'ord-detail-item-price');
                price.appendChild(el('div', '', money(item.line_total)));
                price.appendChild(el('small', 'ord-muted', money(item.unit_price) + ' each'));
                row.appendChild(price);
                itemsWrap.appendChild(row);
            });
        }
        body.appendChild(itemsWrap);

        body.appendChild(el('h3', 'ord-detail-section-title', 'Payment records'));
        const payments = Array.isArray(order.payments) ? order.payments : [];
        if (!payments.length) {
            body.appendChild(el('p', 'ord-muted', 'No payment record is attached to this order.'));
        } else {
            payments.forEach(payment => {
                const row = el('div', 'ord-pay');
                const details = el('div');
                details.appendChild(el('div', 'ord-pay-method', String(payment.method || 'Payment').replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())));
                if (payment.amount !== null && payment.amount !== undefined) details.appendChild(el('div', 'ord-pay-amount', money(payment.amount)));
                row.appendChild(details);
                // Use the same order-level payment result shown in the modal header.
                // This prevents a stale/blank individual payment status from showing
                // 'Unpaid' when the order itself is already classified as 'Paid'.
                const orderPaymentStatus = String(order.payment_status || 'unpaid').toLowerCase();
                const rawStatus = String(payment.status || 'unpaid').toLowerCase();
                const paidWords = ['paid','completed','complete','success','succeeded','settled','captured','approved'];
                const failedWords = ['failed','cancelled','canceled','refunded','declined','expired'];
                const orderIsPaid = paidWords.includes(orderPaymentStatus);
                const paymentIsFailed = failedWords.includes(rawStatus);
                const statusKey = orderIsPaid ? 'paid' : (paidWords.includes(rawStatus) ? 'paid' : (paymentIsFailed ? 'failed' : 'unpaid'));
                const statusLabel = statusKey === 'paid' ? 'Paid' : (statusKey === 'failed' ? 'Failed' : 'Unpaid');
                row.appendChild(badge(statusKey, statusLabel));
                body.appendChild(row);
            });
        }

        const summary = el('div', 'ord-detail-summary');
        const summaryRow = (label, value) => {
            const row = el('div', 'ord-detail-summary-row');
            row.appendChild(el('span', '', label));
            row.appendChild(el('span', '', value));
            return row;
        };
        summary.appendChild(summaryRow('Items subtotal', money(items.reduce((sum, item) => sum + Number(item.line_total || 0), 0))));
        summary.appendChild(summaryRow('Order total', money(order.total)));
        body.appendChild(summary);

        modal.hidden = false;
        document.body.classList.add('ord-modal-open');
        closeBtn.focus();
    }
    function closeModal() {
        modal.hidden = true;
        document.body.classList.remove('ord-modal-open');
        if (lastFocus && typeof lastFocus.focus === 'function') lastFocus.focus();
    }
    document.querySelectorAll('.js-view-order').forEach(button => {
        button.addEventListener('click', function () {
            try {
                openOrder(JSON.parse(this.dataset.order));
            } catch (error) {
                console.error('Unable to read order details.', error);
                window.alert('Unable to display this order. Please open the full order details page.');
            }
        });
    });
    closeBtn.addEventListener('click', closeModal);
    dismissBtn.addEventListener('click', closeModal);
    modal.addEventListener('mousedown', event => { if (event.target === modal) closeModal(); });
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && !modal.hidden) closeModal(); });
})();
</script>
@endsection