@extends('admin.layouts.app')

@section('title', 'Order #' . $order->id)

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

    // Steps shown in the progress track (cancelled is handled separately)
    $steps = array_diff_key($statuses, ['cancelled' => true]);
    $stepKeys = array_keys($steps);
    $isCancelled = $order->status === 'cancelled';
    $currentStep = array_search($order->status, $stepKeys, true);
    $currentStep = $currentStep === false ? -1 : $currentStep;

    $paidWords   = ['paid', 'completed', 'complete', 'success', 'succeeded', 'settled', 'captured', 'approved', 'successful'];
    $failedWords = ['failed', 'cancelled', 'canceled', 'refunded', 'declined', 'expired'];

    // One payment row -> [css class, label]
    $payOne = function ($p) use ($paidWords, $failedWords) {
        $raw = strtolower(trim((string) ($p->status ?? $p->payment_status ?? '')));
        if (in_array($raw, $paidWords, true))   return ['paid', ucfirst($raw)];
        if (in_array($raw, $failedWords, true)) return ['failed', ucfirst($raw)];
        return ['unpaid', $raw !== '' ? ucfirst($raw) : 'Unpaid'];
    };

    // Whole order -> 'paid' | 'failed' | 'unpaid'
    $orderPay = (function () use ($order, $paidWords, $failedWords) {
        $values = $order->payments
            ->map(fn ($p) => strtolower(trim((string) ($p->status ?? $p->payment_status ?? ''))))
            ->filter()->all();
        if (!empty($order->payment_status)) $values[] = strtolower(trim((string) $order->payment_status));

        if (!empty($order->paid_at) || ($order->is_paid ?? false) || count(array_intersect($values, $paidWords))) return 'paid';
        if (count(array_intersect($values, $failedWords))) return 'failed';
        return 'unpaid';
    })();

    $imgUrl = function ($image) {
        if (!$image) return null;
        return str_starts_with($image, 'http') ? $image : url('/api/product-image/' . $image);
    };

    $lines = $order->items->map(function ($item) {
        $variant = $item->productVariant;
        $price   = (float) ($item->price ?? $item->unit_price ?? optional($variant)->selling_price ?? 0);
        $qty     = (int) ($item->quantity ?? 1);
        return (object) [
            'product' => optional($variant)->product,
            'variant' => $variant,
            'price'   => $price,
            'qty'     => $qty,
            'total'   => $price * $qty,
        ];
    });

    $subtotal = $lines->sum('total');
    $shipping = (float) ($order->shipping_fee ?? $order->shipping_cost ?? 0);
    $discount = (float) ($order->discount ?? $order->discount_amount ?? 0);
    $grand    = (float) ($order->total_amount ?? $order->total ?? $order->grand_total ?? ($subtotal + $shipping - $discount));

    $customer = $order->user;
    $address  = $order->shipping_address ?? $order->address ?? null;
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

    .ord-back { display: inline-block; margin-bottom: 12px; color: var(--ord-muted); text-decoration: none; font-weight: 500; }
    .ord-back:hover { color: var(--ord-ink); text-decoration: none; }
    .ord-page-actions { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-bottom:16px; }
    .ord-action-btn { display:inline-flex; align-items:center; justify-content:center; gap:9px; min-height:42px; padding:0 16px; border-radius:10px; border:1px solid var(--ord-line); font:inherit; font-weight:700; text-decoration:none; cursor:pointer; }
    .ord-action-btn--ghost { color:var(--ord-ink); background:#fff; }
    .ord-action-btn--ghost:hover { background:var(--ord-soft); }
    .ord-action-btn--primary { color:#fff; background:var(--ord-accent); border-color:var(--ord-accent); }
    .ord-action-btn--primary:hover { filter:brightness(.95); }

    /* Receipt is hidden on screen and shown only in the browser print dialog. */
    #order-receipt-print { display:none; }

    .ord-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 20px; }
    .ord-title { font-size: 26px; font-weight: 700; margin: 0; letter-spacing: -0.01em; }
    .ord-sub { margin: 4px 0 0; color: var(--ord-muted); }
    .ord-head-main { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }

    /* Dialog (same look as the Add Product modal) */
    .ord-modal {
        position: fixed; inset: 0; z-index: 9999; display: grid; place-items: center; padding: 20px;
        background: rgba(42, 33, 27, .55);
    }
    .ord-modal[hidden] { display: none; }
    .ord-modal-card {
        width: min(560px, 100%); max-height: calc(100vh - 40px); display: flex; flex-direction: column;
        background: #ffffff; border-radius: 18px; overflow: hidden; color: var(--ord-ink);
        box-shadow: 0 24px 64px rgba(42, 33, 27, .35);
    }
    .ord-modal-head {
        display: flex; justify-content: space-between; align-items: flex-start; gap: 16px;
        padding: 26px 28px 20px; border-bottom: 1px solid var(--ord-line);
    }
    .ord-modal-eyebrow { font-size: 12px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; color: var(--ord-accent); margin-bottom: 4px; }
    .ord-modal-head h2 { margin: 0; font-size: 26px; font-weight: 800; letter-spacing: -0.01em; }
    .ord-modal-head p { margin: 6px 0 0; color: var(--ord-muted); font-size: 14.5px; }
    .ord-modal-x {
        flex: none; width: 44px; height: 44px; border-radius: 10px; border: 1px solid var(--ord-line);
        background: #fff; color: var(--ord-ink); font-size: 22px; line-height: 1; cursor: pointer;
    }
    .ord-modal-x:hover { background: var(--ord-soft); }
    .ord-modal-body { padding: 24px 28px; overflow-y: auto; }
    .ord-compare { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .ord-compare.is-single { grid-template-columns: 1fr; }
    .ord-compare-cell { border: 1px solid var(--ord-line); border-radius: 14px; padding: 16px 18px; background: #fff; }
    .ord-compare-cell.is-new { background: var(--ord-soft); border-left: 4px solid var(--ord-accent); }
    .ord-compare-label { display: block; font-weight: 800; color: var(--ord-accent); margin-bottom: 10px; }
    .ord-modal-note { margin: 16px 0 0; color: var(--ord-muted); }
    .ord-modal-foot { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 28px; border-top: 1px solid var(--ord-line); }
    .ord-modal-btn {
        height: 46px; padding: 0 22px; border-radius: 10px; border: 1px solid var(--ord-accent);
        background: var(--ord-accent); color: var(--ord-accent-ink); font: inherit; font-weight: 800; cursor: pointer;
    }
    .ord-modal-btn.is-ghost { background: #fff; color: var(--ord-ink); border-color: var(--ord-line); }
    .ord-modal-btn.is-ghost:hover { background: var(--ord-soft); }
    .ord-modal-btn.is-danger { background: #b83a2f; border-color: #b83a2f; }
    .ord-modal-btn:disabled { opacity: .6; cursor: wait; }
    .ord-modal-btn:focus-visible, .ord-modal-x:focus-visible { outline: 2px solid var(--ord-accent); outline-offset: 2px; }
    @media (max-width: 560px) {
        .ord-modal-head { padding: 20px; }
        .ord-modal-head h2 { font-size: 22px; }
        .ord-modal-body { padding: 18px 20px; }
        .ord-compare { grid-template-columns: 1fr; }
        .ord-modal-foot { padding: 14px 20px; flex-direction: column-reverse; }
        .ord-modal-btn { width: 100%; }
    }
    @media (prefers-reduced-motion: no-preference) {
        .ord-modal { animation: ord-fade .18s ease-out; }
        .ord-modal-card { animation: ord-pop .22s ease-out; }
        @keyframes ord-fade { from { opacity: 0; } to { opacity: 1; } }
        @keyframes ord-pop { from { opacity: 0; transform: translateY(10px) scale(.98); } to { opacity: 1; transform: none; } }
    }

    .ord-alert { padding: 12px 16px; border-radius: 10px; margin-bottom: 16px; font-weight: 500; }
    .ord-alert--ok  { background: #e0f4e6; color: #1f6b38; }
    .ord-alert--err { background: #fbe4e2; color: #a2342b; }
    .ord-alert--err ul { margin: 0; padding-left: 18px; }

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

    .ord-panel { background: var(--ord-surface); border: 1px solid var(--ord-line); border-radius: 14px; margin-bottom: 18px; }
    .ord-panel-head { padding: 16px 20px; border-bottom: 1px solid var(--ord-line); display: flex; justify-content: space-between; align-items: center; gap: 12px; }
    .ord-panel-title { margin: 0; font-size: 16px; font-weight: 700; }
    .ord-panel-body { padding: 20px; }

    /* Progress track: the focal point of this page */
    .ord-track { list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(5, 1fr); }
    .ord-step { position: relative; text-align: center; padding-top: 38px; color: var(--ord-muted); font-weight: 500; }
    .ord-step::before {
        content: ""; position: absolute; top: 11px; left: -50%; width: 100%; height: 3px; background: var(--ord-line);
    }
    .ord-step:first-child::before { display: none; }
    .ord-step::after {
        content: ""; position: absolute; top: 0; left: calc(50% - 12px); width: 24px; height: 24px; border-radius: 50%;
        background: var(--ord-surface); border: 3px solid var(--ord-line);
    }
    .ord-step.is-done { color: var(--ord-ink); }
    .ord-step.is-done::before { background: var(--ord-accent); }
    .ord-step.is-done::after { background: var(--ord-accent); border-color: var(--ord-accent); }
    .ord-step.is-current { color: var(--ord-accent); font-weight: 700; }
    .ord-step.is-current::after { background: var(--ord-surface); border-color: var(--ord-accent); box-shadow: 0 0 0 5px rgba(107, 74, 53, .15); }
    .ord-track.is-cancelled .ord-step { opacity: .45; }

    .ord-cancelled-note { margin-top: 18px; padding: 12px 16px; border-radius: 10px; background: #fbe4e2; color: #a2342b; font-weight: 500; }

    .ord-layout { display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 18px; align-items: start; }

    /* Items */
    .ord-lines { list-style: none; margin: 0; padding: 0; }
    .ord-line { display: grid; grid-template-columns: 64px minmax(0, 1fr) auto; gap: 16px; align-items: center; padding: 16px 20px; border-bottom: 1px solid var(--ord-line); }
    .ord-line:last-child { border-bottom: 0; }
    .ord-thumb { width: 64px; height: 78px; border-radius: 10px; object-fit: cover; background: var(--ord-soft); border: 1px solid var(--ord-line); }
    .ord-thumb.is-empty { display: grid; place-items: center; color: var(--ord-muted); font-size: 11px; }
    .ord-line-name { font-weight: 600; margin-bottom: 4px; }
    .ord-line-meta { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; color: var(--ord-muted); }
    .ord-chip { padding: 2px 9px; border-radius: 999px; background: var(--ord-soft); border: 1px solid var(--ord-line); font-size: 12.5px; color: var(--ord-ink); }
    .ord-line-price { text-align: right; font-variant-numeric: tabular-nums; }
    .ord-line-price strong { display: block; }
    .ord-line-price small { color: var(--ord-muted); }

    .ord-sum { padding: 16px 20px; border-top: 1px solid var(--ord-line); background: var(--ord-soft); border-radius: 0 0 14px 14px; }
    .ord-sum-row { display: flex; justify-content: space-between; padding: 4px 0; font-variant-numeric: tabular-nums; }
    .ord-sum-row span:first-child { color: var(--ord-muted); }
    .ord-sum-row.is-grand { margin-top: 8px; padding-top: 12px; border-top: 1px solid var(--ord-line); font-size: 17px; font-weight: 700; }
    .ord-sum-row.is-grand span:first-child { color: var(--ord-ink); }

    /* Side cards */
    .ord-field { display: block; font-weight: 600; margin-bottom: 6px; }
    .ord-select {
        width: 100%; height: 42px; padding: 0 12px; border: 1px solid var(--ord-line); border-radius: 10px;
        background: var(--ord-surface); color: var(--ord-ink); font: inherit; margin-bottom: 12px;
    }
    .ord-select:focus, .ord-btn:focus-visible { outline: 2px solid var(--ord-accent); outline-offset: 2px; }
    .ord-btn {
        width: 100%; height: 42px; border-radius: 10px; border: 1px solid var(--ord-accent);
        background: var(--ord-accent); color: var(--ord-accent-ink); font: inherit; font-weight: 600; cursor: pointer;
    }
    .ord-btn:disabled { opacity: .5; cursor: not-allowed; }
    .ord-hint { margin: 10px 0 0; color: var(--ord-muted); font-size: 13px; }

    .ord-person { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
    .ord-avatar {
        width: 44px; height: 44px; border-radius: 50%; flex: none; background: var(--ord-soft);
        border: 1px solid var(--ord-line); color: var(--ord-accent); display: grid; place-items: center; font-weight: 700; font-size: 17px;
    }
    .ord-person-name { font-weight: 700; }
    .ord-person-mail { color: var(--ord-muted); word-break: break-all; }

    .ord-dl { margin: 0; }
    .ord-dl div { padding: 10px 0; border-top: 1px solid var(--ord-line); }
    .ord-dl dt { color: var(--ord-muted); font-size: 13px; margin-bottom: 2px; }
    .ord-dl dd { margin: 0; font-weight: 500; word-break: break-word; }

    .ord-pay { padding: 12px 0; border-top: 1px solid var(--ord-line); display: flex; justify-content: space-between; align-items: center; gap: 10px; }
    .ord-pay:first-child { border-top: 0; padding-top: 0; }
    .ord-pay-method { font-weight: 600; }
    .ord-pay-amount { color: var(--ord-muted); font-size: 13px; font-variant-numeric: tabular-nums; }
    .ord-muted { color: var(--ord-muted); margin: 0; }

    @media (max-width: 960px) {
        .ord-layout { grid-template-columns: 1fr; }
    }
    @media (max-width: 560px) {
        .ord-title { font-size: 22px; }
        .ord-step { font-size: 11.5px; }
        .ord-line { grid-template-columns: 52px minmax(0, 1fr); }
        .ord-thumb { width: 52px; height: 64px; }
        .ord-line-price { grid-column: 2; text-align: left; }
    }
    @media (prefers-reduced-motion: no-preference) {
        .ord-step::after, .ord-step::before { transition: background .2s, border-color .2s; }
    }
    @media print {
        @page { size: auto; margin: 12mm; }
        body * { visibility: hidden !important; }
        #order-receipt-print, #order-receipt-print * { visibility: visible !important; }
        #order-receipt-print { display:block !important; position:absolute; left:0; top:0; width:100%; color:#2a211b; background:#fff; font:12px/1.45 Arial,sans-serif; }
        .receipt-sheet { max-width:760px; margin:0 auto; padding:8px; }
        .receipt-top { display:flex; justify-content:space-between; gap:24px; align-items:flex-start; border-bottom:2px solid #6b4a35; padding-bottom:18px; margin-bottom:18px; }
        .receipt-brand { font-size:25px; font-weight:800; letter-spacing:.04em; }
        .receipt-kicker { color:#7d7068; font-size:11px; text-transform:uppercase; letter-spacing:.16em; margin-bottom:4px; }
        .receipt-title { font-size:32px; margin:0; font-weight:400; }
        .receipt-meta { display:grid; grid-template-columns:1fr 1fr; gap:14px 24px; margin:18px 0 24px; }
        .receipt-label { display:block; color:#7d7068; font-size:10px; text-transform:uppercase; letter-spacing:.08em; margin-bottom:3px; }
        .receipt-value { font-weight:700; overflow-wrap:anywhere; }
        .receipt-table { width:100%; border-collapse:collapse; margin:14px 0 18px; }
        .receipt-table th { text-align:left; color:#7d7068; font-size:10px; text-transform:uppercase; padding:9px 6px; border-bottom:1px solid #d9d0c8; }
        .receipt-table td { padding:10px 6px; border-bottom:1px solid #eee8e2; vertical-align:top; }
        .receipt-num { text-align:right; white-space:nowrap; }
        .receipt-total-wrap { width:min(330px,100%); margin-left:auto; }
        .receipt-total-row { display:flex; justify-content:space-between; gap:16px; padding:5px 0; }
        .receipt-total-grand { border-top:1px solid #6b4a35; margin-top:8px; padding-top:12px; font-size:18px; font-weight:800; }
        .receipt-bottom { margin-top:36px; padding-top:14px; border-top:1px solid #eee8e2; display:flex; justify-content:space-between; gap:20px; color:#7d7068; font-size:11px; }
        .receipt-empty { color:#7d7068; font-style:italic; }
    }
</style>

<div class="ord">

    <div class="ord-page-actions">
        <a href="{{ route('admin.orders.index') }}" class="ord-back ord-action-btn ord-action-btn--ghost">
            <span aria-hidden="true">←</span> Back to orders
        </a>
        <button type="button" class="ord-action-btn ord-action-btn--primary" onclick="window.print()">
            <span aria-hidden="true">▤</span> Print receipt
        </button>
    </div>

    {{-- Header --}}
    <div class="ord-head">
        <div class="ord-head-main">
            <div>
                <h1 class="ord-title">Order #{{ $order->id }}</h1>
                <p class="ord-sub">
                    Placed on {{ $order->created_at->format('M d, Y') }} at {{ $order->created_at->format('h:i A') }}
                </p>
            </div>
            <span class="ord-badge ord-badge--{{ $order->status }}">
                {{ $statuses[$order->status] ?? ucfirst($order->status) }}
            </span>
            <span class="ord-badge ord-badge--{{ $orderPay }}">{{ ucfirst($orderPay) }}</span>
        </div>
    </div>

    {{-- Messages --}}
    {{-- Dialog: used for the "save status" confirmation and the success message --}}
    <div class="ord-modal" id="ord-modal" role="dialog" aria-modal="true" aria-labelledby="ord-modal-title" hidden>
        <div class="ord-modal-card">
            <div class="ord-modal-head">
                <div>
                    <div class="ord-modal-eyebrow">Order status</div>
                    <h2 id="ord-modal-title"></h2>
                    <p id="ord-modal-desc"></p>
                </div>
                <button type="button" class="ord-modal-x" id="ord-modal-x" aria-label="Close">&times;</button>
            </div>
            <div class="ord-modal-body" id="ord-modal-body"></div>
            <div class="ord-modal-foot">
                <button type="button" class="ord-modal-btn is-ghost" id="ord-modal-cancel">Go back</button>
                <button type="button" class="ord-modal-btn" id="ord-modal-confirm">Save status</button>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="ord-alert ord-alert--err" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Progress track --}}
    <section class="ord-panel" aria-label="Order progress">
        <div class="ord-panel-body">
            <ol class="ord-track {{ $isCancelled ? 'is-cancelled' : '' }}">
                @foreach ($steps as $key => $label)
                    @php
                        $i = array_search($key, $stepKeys, true);
                        $cls = '';
                        if (!$isCancelled) {
                            if ($i < $currentStep) $cls = 'is-done';
                            elseif ($i === $currentStep) $cls = 'is-current is-done';
                        }
                    @endphp
                    <li class="ord-step {{ $cls }}" @if (!$isCancelled && $i === $currentStep) aria-current="step" @endif>
                        {{ $label }}
                    </li>
                @endforeach
            </ol>

            @if ($isCancelled)
                <div class="ord-cancelled-note">
                    This order was cancelled. Change the status below if it was cancelled by mistake.
                </div>
            @endif
        </div>
    </section>

    <div class="ord-layout">

        {{-- LEFT: items + totals --}}
        <div>
            <section class="ord-panel">
                <div class="ord-panel-head">
                    <h2 class="ord-panel-title">Items</h2>
                    <span class="ord-muted">{{ $lines->sum('qty') }} {{ \Illuminate\Support\Str::plural('piece', $lines->sum('qty')) }}</span>
                </div>

                <ul class="ord-lines">
                    @forelse ($lines as $line)
                        @php
                            $product = $line->product;
                            $variant = $line->variant;
                            $image = $imgUrl(optional($product)->image ?? optional($variant)->image ?? null);
                            $color = optional($variant)->color ?? null;
                            $size = optional($variant)->size ?? null;
                        @endphp
                        <li class="ord-line">
                            @if ($image)
                                <img class="ord-thumb" src="{{ $image }}" alt="{{ optional($product)->name ?? 'Product image' }}" loading="lazy">
                            @else
                                <div class="ord-thumb is-empty" aria-label="No product image">No image</div>
                            @endif
                            <div>
                                <div class="ord-line-name">{{ optional($product)->name ?? 'Product unavailable' }}</div>
                                <div class="ord-line-meta">
                                    @if ($color)<span class="ord-chip">Color: {{ $color }}</span>@endif
                                    @if ($size)<span class="ord-chip">Size: {{ $size }}</span>@endif
                                    <span>{{ $line->qty }} {{ \Illuminate\Support\Str::plural('item', $line->qty) }}</span>
                                </div>
                            </div>
                            <div class="ord-line-price">
                                <strong>${{ number_format($line->total, 2) }}</strong>
                                <small>{{ $line->qty }} × ${{ number_format($line->price, 2) }}</small>
                            </div>
                        </li>
                    @empty
                        <li class="ord-line">
                            <p class="ord-muted">No items were recorded for this order.</p>
                        </li>
                    @endforelse
                </ul>

                <div class="ord-sum">
                    <div class="ord-sum-row"><span>Subtotal</span><span>${{ number_format($subtotal, 2) }}</span></div>
                    @if ($shipping > 0)
                        <div class="ord-sum-row"><span>Shipping</span><span>${{ number_format($shipping, 2) }}</span></div>
                    @endif
                    @if ($discount > 0)
                        <div class="ord-sum-row"><span>Discount</span><span>-${{ number_format($discount, 2) }}</span></div>
                    @endif
                    <div class="ord-sum-row is-grand"><span>Total</span><span>${{ number_format($grand, 2) }}</span></div>
                </div>
            </section>
        </div>

        {{-- RIGHT: status, customer, payment --}}
        <aside>

            {{-- Update status --}}
            <section class="ord-panel">
                <div class="ord-panel-head"><h2 class="ord-panel-title">Update status</h2></div>
                <div class="ord-panel-body">
                    <form method="POST" id="ord-status-form"
                          action="{{ route('admin.orders.update-status', $order) }}">
                        @csrf
                        @method('PATCH')

                        <label for="ord-status" class="ord-field">Order status</label>
                        <select id="ord-status" name="status" class="ord-select">
                            @foreach ($statuses as $key => $label)
                                <option value="{{ $key }}" @selected(old('status', $order->status) === $key)>{{ $label }}</option>
                            @endforeach
                        </select>

                        <button type="submit" class="ord-btn">Save status</button>
                    </form>
                    <p class="ord-hint">The customer sees this status in the shop app.</p>
                </div>
            </section>

            {{-- Customer --}}
            <section class="ord-panel">
                <div class="ord-panel-head"><h2 class="ord-panel-title">Customer</h2></div>
                <div class="ord-panel-body">
                    @if ($customer)
                        <div class="ord-person">
                            <span class="ord-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($customer->name, 0, 1)) }}</span>
                            <div>
                                <div class="ord-person-name">{{ $customer->name }}</div>
                                <div class="ord-person-mail">{{ $customer->email }}</div>
                            </div>
                        </div>

                        <dl class="ord-dl">
                            @if ($customer->phone ?? null)
                                <div><dt>Phone</dt><dd>{{ $customer->phone }}</dd></div>
                            @endif
                            @if ($address)
                                <div><dt>Delivery address</dt><dd>{{ is_array($address) ? implode(', ', $address) : $address }}</dd></div>
                            @endif
                            @if ($order->note ?? $order->notes ?? null)
                                <div><dt>Customer note</dt><dd>{{ $order->note ?? $order->notes }}</dd></div>
                            @endif
                        </dl>
                    @else
                        <p class="ord-muted">This customer account was deleted.</p>
                    @endif
                </div>
            </section>

            {{-- Payment --}}
            <section class="ord-panel">
                <div class="ord-panel-head"><h2 class="ord-panel-title">Payment</h2></div>
                <div class="ord-panel-body">
                    @forelse ($order->payments as $payment)
                        @php
                            [$pc, $pl] = $payOne($payment);

                            // Keep the single payment badge consistent with the
                            // order-level status shown in the header.
                            if ($order->payments->count() === 1) {
                                $pc = $orderPay;
                                $pl = match ($orderPay) {
                                    'paid' => 'Paid',
                                    'failed' => 'Failed',
                                    default => 'Unpaid',
                                };
                            }

                            $method = $payment->payment_method ?? $payment->method ?? 'Payment';
                        @endphp
                        <div class="ord-pay">
                            <div>
                                <div class="ord-pay-method">{{ ucwords(str_replace('_', ' ', $method)) }}</div>
                                @if ($payment->amount ?? null)
                                    <div class="ord-pay-amount">${{ number_format((float) $payment->amount, 2) }}</div>
                                @endif
                            </div>
                            <span class="ord-badge ord-badge--{{ $pc }}">{{ $pl }}</span>
                        </div>
                    @empty
                        <p class="ord-muted">No payment has been recorded for this order.</p>
                    @endforelse
                </div>
            </section>
        </aside>
    </div>
</div>

{{-- Printable invoice uses the same real order data shown above. --}}
<section id="order-receipt-print" aria-label="Order receipt">
    <div class="receipt-sheet">
        <header class="receipt-top">
            <div>
                <div class="receipt-brand">ALWAY WIN</div>
                <div class="receipt-kicker">Clothing Store</div>
            </div>
            <div style="text-align:right">
                <div class="receipt-title">Receipt</div>
                <div>Order #{{ $order->id }}</div>
            </div>
        </header>

        <div class="receipt-meta">
            <div>
                <span class="receipt-label">Receipt number</span>
                <div class="receipt-value">#{{ $order->id }}</div>
            </div>
            <div>
                <span class="receipt-label">Order date</span>
                <div class="receipt-value">{{ optional($order->created_at)->format('M d, Y h:i A') ?? '—' }}</div>
            </div>
            <div>
                <span class="receipt-label">Billed to</span>
                <div class="receipt-value">{{ $customer->name ?? 'Customer' }}</div>
                @if ($customer?->email)<div>{{ $customer->email }}</div>@endif
                @if ($customer?->phone)<div>{{ $customer->phone }}</div>@endif
                @if ($address)<div>{{ is_array($address) ? implode(', ', $address) : $address }}</div>@endif
            </div>
            <div>
                <span class="receipt-label">Payment information</span>
                <div class="receipt-value">{{ ucfirst($orderPay) }}</div>
                @forelse ($order->payments as $payment)
                    <div>{{ ucwords(str_replace('_', ' ', $payment->payment_method ?? $payment->method ?? 'Payment')) }}@if(isset($payment->amount)) — ${{ number_format((float) $payment->amount, 2) }}@endif</div>
                @empty
                    <div class="receipt-empty">No payment record attached</div>
                @endforelse
            </div>
        </div>

        <table class="receipt-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th class="receipt-num">Qty</th>
                    <th class="receipt-num">Unit price</th>
                    <th class="receipt-num">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($lines as $line)
                    <tr>
                        <td>
                            <strong>{{ optional($line->product)->name ?? 'Product unavailable' }}</strong>
                            @if (optional($line->variant)->color || optional($line->variant)->size)
                                <div class="receipt-empty">
                                    @if (optional($line->variant)->color)Color: {{ $line->variant->color }}@endif
                                    @if (optional($line->variant)->color && optional($line->variant)->size) · @endif
                                    @if (optional($line->variant)->size)Size: {{ $line->variant->size }}@endif
                                </div>
                            @endif
                        </td>
                        <td class="receipt-num">{{ $line->qty }}</td>
                        <td class="receipt-num">${{ number_format($line->price, 2) }}</td>
                        <td class="receipt-num">${{ number_format($line->total, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="receipt-empty">No items were recorded for this order.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="receipt-total-wrap">
            <div class="receipt-total-row"><span>Subtotal</span><strong>${{ number_format($subtotal, 2) }}</strong></div>
            @if ($shipping > 0)
                <div class="receipt-total-row"><span>Shipping</span><strong>${{ number_format($shipping, 2) }}</strong></div>
            @endif
            @if ($discount > 0)
                <div class="receipt-total-row"><span>Discount</span><strong>-${{ number_format($discount, 2) }}</strong></div>
            @endif
            <div class="receipt-total-row receipt-total-grand"><span>Total</span><strong>${{ number_format($grand, 2) }}</strong></div>
        </div>

        <footer class="receipt-bottom">
            <span>Thank you for shopping with ALWAY WIN.</span>
            <span>Order status: {{ $statuses[$order->status] ?? ucfirst($order->status) }}</span>
        </footer>
    </div>
</section>

<script>
    (function () {
        var STATUSES = @json($statuses);
        var ORDER_ID = {{ $order->id }};
        var CURRENT  = @json($order->status);
        var SAVED    = @json((bool) session('success'));

        var modal   = document.getElementById('ord-modal');
        var title   = document.getElementById('ord-modal-title');
        var desc    = document.getElementById('ord-modal-desc');
        var body    = document.getElementById('ord-modal-body');
        var btnX    = document.getElementById('ord-modal-x');
        var btnBack = document.getElementById('ord-modal-cancel');
        var btnOk   = document.getElementById('ord-modal-confirm');
        var form    = document.getElementById('ord-status-form');
        var select  = document.getElementById('ord-status');

        if (!modal || !form || !select) return;

        // Move the dialog to <body> so no parent (overflow, transform, z-index) can clip or hide it.
        document.body.appendChild(modal);

        var lastFocus = null;
        var onConfirm = null;

        function label(key) { return STATUSES[key] || key; }

        function cell(heading, key, isNew) {
            var box = document.createElement('div');
            box.className = 'ord-compare-cell' + (isNew ? ' is-new' : '');
            var h = document.createElement('span');
            h.className = 'ord-compare-label';
            h.textContent = heading;
            var b = document.createElement('span');
            b.className = 'ord-badge ord-badge--' + key;
            b.textContent = label(key);
            box.appendChild(h);
            box.appendChild(b);
            return box;
        }

        function open(cfg) {
            lastFocus = document.activeElement;
            title.textContent = cfg.title;
            desc.textContent = cfg.desc;

            body.innerHTML = '';
            var grid = document.createElement('div');
            grid.className = 'ord-compare' + (cfg.next ? '' : ' is-single');
            grid.appendChild(cell(cfg.next ? 'Current status' : 'Status', cfg.current, false));
            if (cfg.next) grid.appendChild(cell('New status', cfg.next, true));
            body.appendChild(grid);

            if (cfg.note) {
                var n = document.createElement('p');
                n.className = 'ord-modal-note';
                n.textContent = cfg.note;
                body.appendChild(n);
            }

            btnBack.hidden = !cfg.backLabel;
            btnBack.textContent = cfg.backLabel || '';
            btnOk.textContent = cfg.okLabel;
            btnOk.disabled = false;
            btnOk.className = 'ord-modal-btn' + (cfg.danger ? ' is-danger' : '');
            onConfirm = cfg.onConfirm || close;

            modal.hidden = false;
            document.body.style.overflow = 'hidden';
            btnOk.focus();
        }

        function close() {
            modal.hidden = true;
            document.body.style.overflow = '';
            if (lastFocus && lastFocus.focus) lastFocus.focus();
        }

        btnX.addEventListener('click', close);
        btnBack.addEventListener('click', close);
        btnOk.addEventListener('click', function () { onConfirm(); });
        modal.addEventListener('mousedown', function (e) { if (e.target === modal) close(); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !modal.hidden) close(); });

        // Save status: confirm first
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            var next = select.value;

            if (next === CURRENT) {
                open({
                    title: 'Status not changed',
                    desc: 'Order #' + ORDER_ID + ' is already ' + label(CURRENT) + '.',
                    current: CURRENT,
                    okLabel: 'OK'
                });
                return;
            }

            var cancelling = next === 'cancelled';
            open({
                title: cancelling ? 'Cancel this order?' : 'Update order status?',
                desc: 'Order #' + ORDER_ID,
                current: CURRENT,
                next: next,
                note: cancelling
                    ? 'The customer will see this order as cancelled in the shop app.'
                    : 'The customer will see the new status in the shop app.',
                backLabel: 'Go back',
                okLabel: cancelling ? 'Cancel order' : 'Save status',
                danger: cancelling,
                onConfirm: function () {
                    btnOk.disabled = true;
                    btnOk.textContent = 'Saving...';
                    form.submit();
                }
            });
        }, true);

        // After saving: success message
        if (SAVED) {
            open({
                title: 'Status updated',
                desc: 'Order #' + ORDER_ID + ' is now ' + label(CURRENT) + '.',
                current: CURRENT,
                note: 'The customer can see this status in the shop app.',
                okLabel: 'Done'
            });
        }
    })();
</script>
@endsection