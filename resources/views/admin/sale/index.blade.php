@extends('admin.layouts.app')

@section('title', 'Sale')
@section('page-title', 'Sale')

@section('content')

<div class="sales-page">

    {{-- =========================================================
         SALE HEADER
         Online Clothing Store flow:
         Customer -> Product -> Cart -> Checkout -> Order -> Payment
         -> Order Status -> Delivered -> Sale Record
    ========================================================== --}}
    <div class="sales-page-header">
        <div>
            <span class="sales-eyebrow">SALE</span>
            <h1>Sale</h1>
            <p>Online customer sales and completed order records.</p>
        </div>
    </div>


    {{-- =========================================================
         REAL SALES OVERVIEW
         These values come from the controller/database.
         No fake/demo numbers are used.
    ========================================================== --}}
    <div class="sales-stats-grid">

        <div class="sales-stat sales-stat-sales">
            <span>Total Revenue</span>
            <strong>${{ number_format((float) ($stats['total_sales'] ?? 0), 2) }}</strong>
            <small>Paid online orders excluding cancelled orders</small>
        </div>

        <div class="sales-stat sales-stat-orders">
            <span>Paid Orders</span>
            <strong>{{ number_format((int) ($stats['paid_orders'] ?? 0)) }}</strong>
            <small>Orders with paid payment status</small>
        </div>

        <div class="sales-stat sales-stat-completed">
            <span>Completed Sales</span>
            <strong>{{ number_format((int) ($stats['completed'] ?? 0)) }}</strong>
            <small>Delivered/completed online orders</small>
        </div>

        <div class="sales-stat sales-stat-cancelled">
            <span>Cancelled Orders</span>
            <strong>{{ number_format((int) ($stats['cancelled'] ?? 0)) }}</strong>
            <small>Cancelled customer orders</small>
        </div>

    </div>


    {{-- =========================================================
         SEARCH / FILTER
         Search is against REAL orders/customer/product/payment data.
    ========================================================== --}}
    <div class="sales-filter-card">

        <form method="GET" action="{{ route('admin.sale.index') }}">

            <div class="sales-filter-row">

                <div class="sales-filter-search">
                    <label for="sales-search">Search</label>
                    <input
                        id="sales-search"
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search order, customer, email, product, SKU..."
                    >
                </div>

                <div>
                    <label for="sales-status">Status</label>
                    <select id="sales-status" name="status">
                        <option value="">All Status</option>

                        @foreach([
                            'pending' => 'Pending',
                            'confirmed' => 'Confirmed',
                            'processing' => 'Processing',
                            'shipped' => 'Shipped',
                            'delivered' => 'Delivered',
                            'cancelled' => 'Cancelled',
                        ] as $value => $label)

                            <option
                                value="{{ $value }}"
                                @selected(request('status') === $value)
                            >
                                {{ $label }}
                            </option>

                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="sales-payment">Payment</label>
                    <select id="sales-payment" name="payment_method">
                        <option value="">All Payments</option>

                        @foreach(($paymentMethods ?? []) as $paymentMethod)

                            <option
                                value="{{ $paymentMethod }}"
                                @selected(request('payment_method') === $paymentMethod)
                            >
                                {{ str_replace('_', ' ', ucfirst($paymentMethod)) }}
                            </option>

                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="sales-date-from">From</label>
                    <input
                        id="sales-date-from"
                        type="date"
                        name="date_from"
                        value="{{ request('date_from') }}"
                    >
                </div>

                <div>
                    <label for="sales-date-to">To</label>
                    <input
                        id="sales-date-to"
                        type="date"
                        name="date_to"
                        value="{{ request('date_to') }}"
                    >
                </div>

                <div>
                    <label for="sales-sort">Sort</label>
                    <select id="sales-sort" name="sort">
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
                    href="{{ route('admin.sale.index') }}"
                    class="sales-clear-button"
                >
                    Clear
                </a>

            </div>

        </form>

    </div>


    {{-- =========================================================
         SALE HISTORY
         IMPORTANT:
         This table is driven by REAL orders.
         The old separate Manual Sale / POS table is removed.
    ========================================================== --}}
    <div class="sales-card">

        <div class="sales-card-heading">
            <div>
                <h2>Sale History</h2>
                <p>{{ $orders->total() }} order(s) found</p>
            </div>
        </div>

        @if($orders->count() > 0)

            <div class="sales-table-wrap">

                <table class="sales-table">

                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Payment Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($orders as $order)

                            @php
                                // =================================================
                                // REAL DATABASE DATA
                                // orders.id is the real order identifier.
                                // There is no invented order_number here.
                                // =================================================

                                $payment = $order->payments->first();

                                $customerName = trim(
                                    ($order->user?->first_name ?? '') . ' ' .
                                    ($order->user?->last_name ?? '')
                                );

                                if ($customerName === '') {
                                    $customerName = $order->user?->name ?? 'Customer';
                                }

                                $status = strtolower((string) $order->status);
                                $paymentStatus = strtolower((string) $order->payment_status);

                                $statusClass = match ($status) {
                                    'delivered', 'completed' => 'sales-status-completed',
                                    'cancelled' => 'sales-status-cancelled',
                                    default => 'sales-status-pending',
                                };

                                $paymentClass = $paymentStatus === 'paid'
                                    ? 'sales-payment-paid'
                                    : 'sales-payment-unpaid';
                            @endphp

                            <tr>

                                {{-- REAL: orders.id --}}
                                <td>
                                    <strong class="sales-invoice">
                                        #{{ $order->id }}
                                    </strong>
                                </td>

                                {{-- REAL: orders.user_id -> users --}}
                                <td>
                                    <div class="sales-customer-block">
                                        <strong>{{ $customerName }}</strong>

                                        @if($order->user?->email)
                                            <span>{{ $order->user->email }}</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- REAL: orders.created_at (converted to Cambodia time) --}}
                                <td>
                                    {{
                                        $order->created_at
                                            ? $order->created_at
                                                ->timezone('Asia/Phnom_Penh')
                                                ->format('d M Y, H:i')
                                            : '—'
                                    }}
                                </td>

                                {{-- REAL: orders.total --}}
                                <td>
                                    <strong class="sales-total">
                                        ${{ number_format((float) $order->total, 2) }}
                                    </strong>
                                </td>

                                {{-- REAL: order_payments.payment_method --}}
                                <td>
                                    <span class="sales-payment">
                                        {{ str_replace('_', ' ', ucfirst($payment?->payment_method ?? '—')) }}
                                    </span>
                                </td>

                                {{-- REAL: orders.status --}}
                                <td>
                                    <span class="sales-status {{ $statusClass }}">
                                        <i></i>
                                        {{ ucfirst($status ?: '—') }}
                                    </span>
                                </td>

                                {{-- REAL: orders.payment_status --}}
                                <td>
                                    <span class="sales-order-payment-status {{ $paymentClass }}">
                                        {{ ucfirst($paymentStatus ?: 'Pending') }}
                                    </span>
                                </td>

                                <td>
                                    <button
                                        type="button"
                                        class="sales-order-view-button"
                                        onclick="openOrderDialog({{ $order->id }})"
                                    >
                                        <span>◉</span>
                                        View
                                    </button>
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

            <div class="sales-pagination">
                {{ $orders->links() }}
            </div>

        @else

            <div class="sales-empty">
                <div class="sales-empty-icon">S</div>
                <strong>No sale records found</strong>
                <p>
                    There are no online customer orders matching your current filters.
                </p>
            </div>

        @endif

    </div>

</div>


{{-- =========================================================
     ORDER DETAILS
     Same Sale page. Read-only detail view.
     Admin does not create a fake sale from this page.
========================================================= --}}

<div
    class="sale-dialog-backdrop"
    id="order-dialog-backdrop"
    aria-hidden="true"
>

    @foreach($orders as $order)

        @php
            $processingDays = (int) ($timelineSettings->processing_days ?? 1);
            $shippingDays = (int) ($timelineSettings->shipping_days ?? 2);
            $deliveryDays = (int) ($timelineSettings->delivery_days ?? 3);

            /*
            * REAL timestamps.
            * These come from orders table.
            */
            $confirmedActual = $order->confirmed_at;
            $processingActual = $order->processing_at;
            $shippedActual = $order->shipped_at;
            $deliveredActual = $order->delivered_at;

            /*
            * Expected dates are only calculated when the real
            * timestamp does not exist. Math is done on the
            * original (storage-timezone) instant so day-adding
            * is not affected by display timezone conversion.
            */
            $expectedProcessing = $confirmedActual
                ? $confirmedActual->copy()->addDays($processingDays)
                : null;

            $expectedShipped = $processingActual
                ? $processingActual->copy()->addDays($shippingDays)
                : (
                    $expectedProcessing
                        ? $expectedProcessing->copy()->addDays($shippingDays)
                        : null
                );

            $expectedDelivered = $shippedActual
                ? $shippedActual->copy()->addDays($deliveryDays)
                : (
                    $expectedShipped
                        ? $expectedShipped->copy()->addDays($deliveryDays)
                        : null
                );

            /*
            * FIX: Cambodia timezone.
            * These are the values actually rendered in the
            * timeline below. Everything the admin sees is
            * converted to Asia/Phnom_Penh here, in one place.
            */
            $confirmedDate = $confirmedActual
                ? $confirmedActual->copy()->timezone('Asia/Phnom_Penh')
                : null;

            $processingDate = ($processingActual ?? $expectedProcessing)
                ? ($processingActual ?? $expectedProcessing)->copy()->timezone('Asia/Phnom_Penh')
                : null;

            $shippedDate = ($shippedActual ?? $expectedShipped)
                ? ($shippedActual ?? $expectedShipped)->copy()->timezone('Asia/Phnom_Penh')
                : null;

            $deliveredDate = ($deliveredActual ?? $expectedDelivered)
                ? ($deliveredActual ?? $expectedDelivered)->copy()->timezone('Asia/Phnom_Penh')
                : null;

            $timelineStatusOrder = [
                'pending' => 0,
                'confirmed' => 1,
                'processing' => 2,
                'shipped' => 3,
                'delivered' => 4,
            ];

            $currentTimelineIndex =
                $timelineStatusOrder[$status] ?? -1;
        @endphp

        <div
            class="sale-dialog sale-order-dialog"
            id="order-dialog-{{ $order->id }}"
            role="dialog"
            aria-modal="true"
            aria-labelledby="order-dialog-title-{{ $order->id }}"
        >

            <div class="sale-dialog-header">

                <div>
                    <span class="sale-dialog-eyebrow">SALE RECORD</span>

                    {{-- REAL: orders.id --}}
                    <h2 id="order-dialog-title-{{ $order->id }}">
                        Order #{{ $order->id }}
                    </h2>

                    <p>
                        Customer order, payment and purchased products.
                    </p>
                </div>

                <button
                    type="button"
                    class="sale-dialog-close"
                    onclick="closeOrderDialog()"
                    aria-label="Close"
                >
                    ×
                </button>

            </div>


            <div class="sale-view-content">

                {{-- =================================================
                     ORDER SUMMARY
                ================================================== --}}
                <div class="sale-view-summary">

                    <div>
                        <span>Order ID</span>
                        <strong>#{{ $order->id }}</strong>
                    </div>

                    <div>
                        <span>Customer</span>
                        <strong>{{ $customerName }}</strong>
                    </div>

                    {{-- FIX: converted to Cambodia time --}}
                    <div>
                        <span>Date</span>
                        <strong>
                            {{
                                $order->created_at
                                    ? $order->created_at->timezone('Asia/Phnom_Penh')->format('d M Y, H:i')
                                    : '—'
                            }}
                        </strong>
                    </div>

                    <div>
                        <span>Status</span>
                        <strong class="{{
                            in_array($status, ['delivered', 'completed'])
                                ? 'view-success'
                                : ($status === 'cancelled' ? 'view-error' : '')
                        }}">
                            {{ ucfirst($status ?: '—') }}
                        </strong>
                    </div>

                    <div>
                        <span>Payment</span>
                        <strong>
                            {{ str_replace('_', ' ', ucfirst($payment?->payment_method ?? '—')) }}
                        </strong>
                    </div>

                </div>


                {{-- =================================================
                     ORDER TIMELINE
                     FIX: Show each order step and expected arrival date.
                     Durations come from the existing `settings` table.
                     Dates rendered below are already converted to
                     Asia/Phnom_Penh in the @php block above.
                ================================================== --}}
                <div class="sales-order-detail-section sales-order-timeline-section">

                    <div class="sales-order-section-heading">
                        <div>
                            <h3>Order Timeline</h3>
                            <p>Processing, shipping and delivery dates are calculated from the admin settings below.</p>
                        </div>
                    </div>

                    <div class="sales-order-timeline">

                        <div class="sales-timeline-item {{ $currentTimelineIndex >= 1 ? 'is-complete' : '' }}">
                            <div class="sales-timeline-marker">{{ $currentTimelineIndex >= 1 ? '✓' : '○' }}</div>
                            <div class="sales-timeline-content">
                                <strong>Confirmed</strong>
                                <span>
                                    @if($confirmedDate)
                                        {{ $confirmedDate->format('d M Y, H:i') }}
                                    @else
                                        Waiting for update
                                    @endif
                                </span>
                            </div>
                        </div>

                        <div class="sales-timeline-item {{ $currentTimelineIndex >= 2 ? 'is-complete' : '' }}">
                            <div class="sales-timeline-marker">{{ $currentTimelineIndex >= 2 ? '✓' : '○' }}</div>
                            <div class="sales-timeline-content">
                                <strong>Processing</strong>
                                <span>
                                    @if($processingActual)
                                        {{ $processingDate->format('d M Y, H:i') }}
                                    @elseif($processingDate)
                                        {{ $processingDate->format('d M Y') }}
                                        <small>Expected</small>
                                    @else
                                        Waiting for update
                                    @endif
                                </span>
                            </div>
                        </div>

                        <div class="sales-timeline-item {{ $currentTimelineIndex >= 3 ? 'is-complete' : '' }}">
                            <div class="sales-timeline-marker">{{ $currentTimelineIndex >= 3 ? '✓' : '○' }}</div>
                            <div class="sales-timeline-content">
                                <strong>Shipped</strong>
                                <span>
                                    @if($shippedActual)
                                        {{ $shippedDate->format('d M Y, H:i') }}
                                    @elseif($shippedDate)
                                        {{ $shippedDate->format('d M Y') }}
                                        <small>Expected</small>
                                    @else
                                        Waiting for update
                                    @endif
                                </span>
                            </div>
                        </div>

                        <div class="sales-timeline-item {{ $currentTimelineIndex >= 4 ? 'is-complete' : '' }}">
                            <div class="sales-timeline-marker">{{ $currentTimelineIndex >= 4 ? '✓' : '○' }}</div>
                            <div class="sales-timeline-content">
                                <strong>Delivered</strong>
                                <span>
                                    @if($deliveredActual)
                                        {{ $deliveredDate->format('d M Y, H:i') }}
                                    @elseif($deliveredDate)
                                        {{ $deliveredDate->format('d M Y') }}
                                        <small>Expected</small>
                                    @else
                                        Waiting for update
                                    @endif
                                </span>
                            </div>
                        </div>

                    </div>

                    {{-- =================================================
                         ADMIN TIMELINE SETTINGS
                         FIX: Save durations into existing `settings` table.
                    ================================================== --}}
                    <div class="sales-order-timeline-settings">

                        <div class="sales-order-section-heading">
                            <div>
                                <h3>Timeline Settings</h3>
                                <p>Admin can set how many days each step should take.</p>
                            </div>
                        </div>

                        <form
                            action="{{ route('admin.sale.order.timeline.settings') }}"
                            method="POST"
                        >
                            @csrf

                            <div class="sales-timeline-settings-grid">
                                <label>
                                    <span>Processing</span>
                                    <input
                                        type="number"
                                        name="processing_days"
                                        min="0"
                                        max="30"
                                        value="{{ (int) ($timelineSettings->processing_days ?? 1) }}"
                                        required
                                    >
                                    <small>days</small>
                                </label>

                                <label>
                                    <span>Shipping</span>
                                    <input
                                        type="number"
                                        name="shipping_days"
                                        min="0"
                                        max="30"
                                        value="{{ (int) ($timelineSettings->shipping_days ?? 2) }}"
                                        required
                                    >
                                    <small>days</small>
                                </label>

                                <label>
                                    <span>Delivery</span>
                                    <input
                                        type="number"
                                        name="delivery_days"
                                        min="0"
                                        max="30"
                                        value="{{ (int) ($timelineSettings->delivery_days ?? 3) }}"
                                        required
                                    >
                                    <small>days</small>
                                </label>

                                <button
                                    type="submit"
                                    class="sales-order-timeline-save-button"
                                >
                                    Save Timeline
                                </button>
                            </div>

                        </form>

                    </div>

                </div>


                {{-- =================================================
                     ORDER STATUS UPDATE
                     FIX: Admin can update the REAL orders.status column.
                     Flow: Pending -> Confirmed -> Processing -> Shipped -> Delivered
                ================================================== --}}
                <div class="sales-order-detail-section">

                    <h3>Order Status</h3>

                    <form
                        action="{{ route('admin.sale.order.status', $order) }}"
                        method="POST"
                    >
                        @csrf

                        <div class="sales-order-status-control">

                            <select
                                name="status"
                                class="sales-order-status-select"
                                required
                            >
                                <option value="pending" @selected($status === 'pending')>Pending</option>
                                <option value="confirmed" @selected($status === 'confirmed')>Confirmed</option>
                                <option value="processing" @selected($status === 'processing')>Processing</option>
                                <option value="shipped" @selected($status === 'shipped')>Shipped</option>
                                <option value="delivered" @selected($status === 'delivered')>Delivered</option>
                                <option value="cancelled" @selected($status === 'cancelled')>Cancelled</option>
                            </select>

                            <button
                                type="submit"
                                class="sales-order-status-button"
                            >
                                Update Status
                            </button>

                        </div>

                    </form>

                </div>


                {{-- =================================================
                     CUSTOMER
                ================================================== --}}
                <div class="sales-order-detail-section">

                    <h3>Customer</h3>

                    <div class="sales-order-detail-grid">

                        <div>
                            <span>Name</span>
                            <strong>{{ $customerName }}</strong>
                        </div>

                        <div>
                            <span>Email</span>
                            <strong>{{ $order->user?->email ?? '—' }}</strong>
                        </div>

                        <div class="sales-order-detail-full">
                            <span>Shipping Address</span>
                            <strong>
                                {{ $order->shipping_address ?: '—' }}
                            </strong>
                        </div>

                    </div>

                </div>


                {{-- =================================================
                     PRODUCTS
                     orders -> order_items -> product_variants -> products
                ================================================== --}}
                <div class="sales-order-detail-section">

                    <h3>Products Purchased</h3>

                    @forelse($order->items as $item)

                        @php
                            $variant = $item->productVariant;
                            $product = $variant?->product;
                        @endphp

                        <div class="sale-view-item">

                            <div>
                                <strong>
                                    {{ $product?->name ?? 'Product' }}
                                </strong>

                                <span>
                                    SKU: {{ $variant?->sku ?? '—' }}
                                    · Size: {{ $variant?->size ?: '—' }}
                                    · Color: {{ $variant?->color ?: '—' }}
                                    · Qty: {{ $item->quantity }}
                                </span>
                            </div>

                            <strong>
                                ${{ number_format((float) $item->subtotal, 2) }}
                            </strong>

                        </div>

                    @empty

                        <div class="sales-order-no-items">
                            No order items found.
                        </div>

                    @endforelse

                </div>


                {{-- =================================================
                     PAYMENT
                     orders -> order_payments
                ================================================== --}}
                <div class="sales-order-detail-section">

                    <h3>Payment</h3>

                    <div class="sales-order-detail-grid">

                        <div>
                            <span>Payment Status</span>
                            <strong class="{{ $paymentStatus === 'paid' ? 'view-success' : '' }}">
                                {{ ucfirst($paymentStatus ?: 'Pending') }}
                            </strong>
                        </div>

                        <div>
                            <span>Payment Method</span>
                            <strong>
                                {{ str_replace('_', ' ', ucfirst($payment?->payment_method ?? '—')) }}
                            </strong>
                        </div>

                        <div>
                            <span>Amount</span>
                            <strong>
                                ${{ number_format((float) ($payment?->amount ?? $order->total), 2) }}
                            </strong>
                        </div>

                        {{-- FIX: converted to Cambodia time --}}
                        <div>
                            <span>Paid At</span>
                            <strong>
                                {{
                                    $payment?->paid_at
                                        ? $payment->paid_at->timezone('Asia/Phnom_Penh')->format('d M Y, H:i')
                                        : '—'
                                }}
                            </strong>
                        </div>

                        @if($payment?->reference_number)
                            <div class="sales-order-detail-full">
                                <span>Reference Number</span>
                                <strong>{{ $payment->reference_number }}</strong>
                            </div>
                        @endif

                    </div>

                </div>


                @if($order->notes)

                    <div class="sale-view-note">
                        <span>Note</span>
                        <p>{{ $order->notes }}</p>
                    </div>

                @endif


                {{-- =================================================
                     TOTALS
                ================================================== --}}
                <div class="sale-view-totals">

                    <div>
                        <span>Subtotal</span>
                        <strong>
                            ${{ number_format((float) $order->subtotal, 2) }}
                        </strong>
                    </div>

                    <div>
                        <span>Discount</span>
                        <strong>
                            ${{ number_format((float) $order->discount, 2) }}
                        </strong>
                    </div>

                    <div class="sale-view-grand">
                        <span>Total</span>
                        <strong>
                            ${{ number_format((float) $order->total, 2) }}
                        </strong>
                    </div>

                </div>

            </div>

        </div>

    @endforeach

</div>


@endsection


@push('styles')

<style>

:root {
    --sales-brown: #6F4E37;
    --sales-brown-dark: #4A3123;
    --sales-brown-soft: #F3E9DF;

    --sales-text: #111827;
    --sales-muted: #667085;
    --sales-border: #E4E7EC;
    --sales-bg: #F8F7F5;
    --sales-white: #FFFFFF;

    --sales-green: #16A34A;
    --sales-green-bg: #ECFDF3;

    --sales-red: #DC2626;
    --sales-red-bg: #FEF2F2;

    --sales-amber: #D97706;
    --sales-amber-bg: #FFF7ED;
}

.sales-page,
.sales-page * {
    box-sizing: border-box;
}

.sales-page {
    width: 100%;
    max-width: 1540px;
    margin: 0 auto;
    padding: 28px 30px 48px;
    color: var(--sales-text);
    font-family: Inter, ui-sans-serif, system-ui, -apple-system,
        BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
    -webkit-font-smoothing: antialiased;
    text-rendering: optimizeLegibility;
}

.sales-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 22px;
}

.sales-eyebrow,
.sale-dialog-eyebrow {
    display: block;
    margin-bottom: 7px;
    color: var(--sales-brown);
    font-size: 11px;
    line-height: 1.2;
    font-weight: 800;
    letter-spacing: 1.1px;
}

.sales-page-header h1 {
    margin: 0;
    color: var(--sales-text);
    font-size: 28px;
    line-height: 1.2;
    font-weight: 800;
    letter-spacing: -.35px;
}

.sales-page-header p {
    margin: 7px 0 0;
    color: var(--sales-muted);
    font-size: 14px;
    line-height: 1.5;
}


/* =========================================================
   STATS
========================================================= */

.sales-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 18px;
}

.sales-stat {
    min-height: 142px;
    padding: 20px;
    border: 1px solid var(--sales-border);
    border-radius: 14px;
    background: var(--sales-white);
    box-shadow: 0 3px 14px rgba(16, 24, 40, .035);
}

.sales-stat > span {
    display: block;
    color: var(--sales-muted);
    font-size: 13px;
    line-height: 1.35;
    font-weight: 650;
}

.sales-stat strong {
    display: block;
    margin-top: 12px;
    color: var(--sales-text);
    font-size: 27px;
    line-height: 1.15;
    font-weight: 800;
    letter-spacing: -.3px;
}

.sales-stat small {
    display: block;
    margin-top: 7px;
    color: var(--sales-muted);
    font-size: 12px;
    line-height: 1.4;
}

.sales-stat-sales {
    border-top: 4px solid var(--sales-brown);
}

.sales-stat-sales strong {
    color: var(--sales-brown);
}

.sales-stat-orders {
    border-top: 4px solid var(--sales-amber);
}

.sales-stat-orders strong {
    color: var(--sales-amber);
}

.sales-stat-completed {
    border-top: 4px solid var(--sales-green);
}

.sales-stat-completed strong {
    color: var(--sales-green);
}

.sales-stat-cancelled {
    border-top: 4px solid var(--sales-red);
}

.sales-stat-cancelled strong {
    color: var(--sales-red);
}


/* =========================================================
   FILTER
========================================================= */

.sales-filter-card,
.sales-card {
    margin-bottom: 18px;
    border: 1px solid var(--sales-border);
    border-radius: 14px;
    background: var(--sales-white);
    box-shadow: 0 3px 14px rgba(16, 24, 40, .035);
}

.sales-filter-card {
    padding: 18px;
}

.sales-filter-row {
    display: grid;
    grid-template-columns:
        minmax(260px, 2fr)
        repeat(5, minmax(125px, 1fr))
        auto
        auto;
    align-items: end;
    gap: 12px;
}

.sales-filter-row label {
    display: block;
    margin-bottom: 7px;
    color: var(--sales-text);
    font-size: 12px;
    line-height: 1.25;
    font-weight: 700;
}

.sales-filter-row input,
.sales-filter-row select {
    width: 100%;
    height: 44px;
    padding: 0 13px;
    border: 1px solid #D0D5DD;
    border-radius: 9px;
    background: #fff;
    color: var(--sales-text);
    font: inherit;
    font-size: 14px;
    line-height: 1.2;
    outline: none;
    transition: border-color .15s ease, box-shadow .15s ease;
}

.sales-filter-row input::placeholder {
    color: #98A2B3;
}

.sales-filter-row input:focus,
.sales-filter-row select:focus {
    border-color: var(--sales-brown);
    box-shadow: 0 0 0 3px rgba(111, 78, 55, .10);
}

.sales-filter-button,
.sales-clear-button {
    min-height: 44px;
    padding: 0 17px;
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
    white-space: nowrap;
}

.sales-filter-button {
    border: 1px solid var(--sales-brown);
    background: var(--sales-brown);
    color: #fff;
}

.sales-filter-button:hover {
    background: var(--sales-brown-dark);
}

.sales-clear-button {
    border: 1px solid #D0D5DD;
    background: #fff;
    color: var(--sales-text);
}

.sales-clear-button:hover {
    border-color: var(--sales-brown);
    color: var(--sales-brown);
    background: var(--sales-brown-soft);
}


/* =========================================================
   TABLE
========================================================= */

.sales-card-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 19px 21px;
    border-bottom: 1px solid var(--sales-border);
}

.sales-card-heading h2 {
    margin: 0;
    color: var(--sales-text);
    font-size: 18px;
    line-height: 1.3;
    font-weight: 800;
}

.sales-card-heading p {
    margin: 5px 0 0;
    color: var(--sales-muted);
    font-size: 13px;
    line-height: 1.4;
}

.sales-table-wrap {
    width: 100%;
    overflow-x: auto;
}

.sales-table {
    width: 100%;
    min-width: 1050px;
    border-collapse: separate;
    border-spacing: 0;
}

.sales-table th {
    padding: 14px 18px;
    border-bottom: 1px solid var(--sales-border);
    background: #FAFAF9;
    color: #475467;
    font-size: 12px;
    line-height: 1.3;
    font-weight: 800;
    text-align: left;
    letter-spacing: .25px;
    white-space: nowrap;
}

.sales-table td {
    padding: 17px 18px;
    border-bottom: 1px solid #EEF0F2;
    color: #475467;
    font-size: 14px;
    line-height: 1.4;
    vertical-align: middle;
}

.sales-table tbody tr:last-child td {
    border-bottom: 0;
}

.sales-table tbody tr:hover {
    background: #FCFBF9;
}

.sales-invoice {
    color: var(--sales-text);
    font-size: 14px;
    font-weight: 800;
}

.sales-customer-block {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.sales-customer-block strong {
    color: var(--sales-text);
    font-size: 14px;
    line-height: 1.3;
}

.sales-customer-block span {
    color: var(--sales-muted);
    font-size: 12px;
    line-height: 1.3;
}

.sales-total {
    color: var(--sales-brown);
    font-size: 15px;
    font-weight: 800;
}

.sales-payment {
    display: inline-flex;
    align-items: center;
    min-height: 30px;
    padding: 5px 10px;
    border: 1px solid #E8DDD3;
    border-radius: 8px;
    background: var(--sales-brown-soft);
    color: var(--sales-brown);
    font-size: 13px;
    line-height: 1.2;
    font-weight: 750;
}

.sales-status {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    min-height: 30px;
    padding: 5px 10px;
    border-radius: 8px;
    font-size: 13px;
    line-height: 1.2;
    font-weight: 800;
}

.sales-status i {
    width: 7px;
    height: 7px;
    flex: 0 0 7px;
    border-radius: 50%;
}

.sales-status-completed {
    background: var(--sales-green-bg);
    color: var(--sales-green);
}

.sales-status-completed i {
    background: var(--sales-green);
}

.sales-status-cancelled {
    background: var(--sales-red-bg);
    color: var(--sales-red);
}

.sales-status-cancelled i {
    background: var(--sales-red);
}

.sales-status-pending {
    background: var(--sales-amber-bg);
    color: var(--sales-amber);
}

.sales-status-pending i {
    background: var(--sales-amber);
}

.sales-order-payment-status {
    display: inline-flex;
    align-items: center;
    min-height: 30px;
    padding: 5px 10px;
    border-radius: 8px;
    font-size: 13px;
    line-height: 1.2;
    font-weight: 800;
}

.sales-payment-paid {
    background: var(--sales-green-bg);
    color: var(--sales-green);
}

.sales-payment-unpaid {
    background: var(--sales-amber-bg);
    color: var(--sales-amber);
}

.sales-order-view-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    min-height: 36px;
    padding: 0 12px;
    border: 1px solid #D0D5DD;
    border-radius: 8px;
    background: #fff;
    color: var(--sales-text);
    font: inherit;
    font-size: 12px;
    line-height: 1;
    font-weight: 750;
    cursor: pointer;
    white-space: nowrap;
}

.sales-order-view-button:hover {
    border-color: var(--sales-brown);
    background: var(--sales-brown-soft);
    color: var(--sales-brown);
}

.sales-order-view-button span {
    color: var(--sales-brown);
}


/* =========================================================
   EMPTY / PAGINATION
========================================================= */

.sales-pagination {
    padding: 16px 20px;
    border-top: 1px solid #EEF0F2;
}

.sales-empty {
    min-height: 280px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 35px 20px;
    text-align: center;
}

.sales-empty-icon {
    width: 54px;
    height: 54px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 13px;
    border-radius: 14px;
    background: var(--sales-brown-soft);
    color: var(--sales-brown);
    font-size: 20px;
    font-weight: 900;
}

.sales-empty strong {
    color: var(--sales-text);
    font-size: 17px;
    line-height: 1.35;
    font-weight: 800;
}

.sales-empty p {
    max-width: 480px;
    margin: 6px 0 0;
    color: var(--sales-muted);
    font-size: 14px;
    line-height: 1.5;
}


/* =========================================================
   ORDER DETAILS
========================================================= */

.sale-dialog-backdrop {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 22px;
    background: rgba(17, 24, 39, .58);
    backdrop-filter: blur(3px);
}

.sale-dialog-backdrop.show {
    display: flex;
}

.sale-dialog {
    display: none;
    width: min(900px, 100%);
    max-height: calc(100vh - 44px);
    overflow-y: auto;
    border: 1px solid var(--sales-border);
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 28px 80px rgba(16, 24, 40, .24);
}

.sale-dialog.show {
    display: block;
}

.sale-dialog-header {
    position: sticky;
    top: 0;
    z-index: 2;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 18px;
    padding: 23px 25px 19px;
    border-bottom: 1px solid var(--sales-border);
    background: #fff;
}

.sale-dialog-header h2 {
    margin: 0;
    color: var(--sales-text);
    font-size: 23px;
    line-height: 1.25;
    font-weight: 800;
}

.sale-dialog-header p {
    margin: 6px 0 0;
    color: var(--sales-muted);
    font-size: 13px;
    line-height: 1.5;
}

.sale-dialog-close {
    width: 40px;
    height: 40px;
    flex: 0 0 40px;
    border: 1px solid #D0D5DD;
    border-radius: 9px;
    background: #fff;
    color: var(--sales-text);
    font-size: 24px;
    line-height: 1;
    cursor: pointer;
}

.sale-dialog-close:hover {
    background: var(--sales-red-bg);
    border-color: #FECACA;
    color: var(--sales-red);
}

.sale-view-content {
    padding: 22px 25px 25px;
}

.sale-view-summary {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 10px;
    margin-bottom: 21px;
}

.sale-view-summary > div {
    padding: 13px;
    border: 1px solid #EEF0F2;
    border-radius: 9px;
    background: #FAFAF9;
}

.sale-view-summary span,
.sales-order-detail-grid span {
    display: block;
    margin-bottom: 6px;
    color: var(--sales-muted);
    font-size: 12px;
    line-height: 1.25;
    font-weight: 700;
}

.sale-view-summary strong {
    color: var(--sales-text);
    font-size: 14px;
    line-height: 1.3;
    font-weight: 800;
}

.view-success {
    color: var(--sales-green) !important;
}

.view-error {
    color: var(--sales-red) !important;
}

.sale-view-items h3 {
    margin: 0 0 9px;
    color: var(--sales-text);
    font-size: 17px;
    line-height: 1.3;
    font-weight: 800;
}

.sale-view-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    padding: 13px 0;
    border-bottom: 1px solid #EEF0F2;
}

.sale-view-item div {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.sale-view-item strong {
    color: var(--sales-text);
    font-size: 14px;
    line-height: 1.35;
}

.sale-view-item span {
    color: var(--sales-muted);
    font-size: 13px;
    line-height: 1.45;
}

.sale-view-item > strong {
    color: var(--sales-brown);
}

.sales-order-detail-section {
    margin-top: 20px;
}

.sales-order-status-form {
    margin: 0;
}

.sales-order-status-control {
    display: flex;
    align-items: center;
    gap: 10px;
}

.sales-order-status-select {
    min-height: 42px;
    min-width: 190px;
    padding: 0 12px;
    border: 1px solid #D0D5DD;
    border-radius: 8px;
    background: #FFFFFF;
    color: var(--sales-text);
    font: inherit;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
}

.sales-order-status-select:focus {
    outline: none;
    border-color: var(--sales-brown);
}

.sales-order-status-button {
    min-height: 42px;
    padding: 0 16px;
    border: 0;
    border-radius: 8px;
    background: var(--sales-brown);
    color: #FFFFFF;
    font: inherit;
    font-size: 13px;
    font-weight: 800;
    cursor: pointer;
}

.sales-order-status-button:hover {
    opacity: .9;
}


.sales-order-detail-section h3 {
    margin: 0 0 10px;
    color: var(--sales-text);
    font-size: 16px;
    line-height: 1.3;
    font-weight: 800;
}

.sales-order-detail-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
}

.sales-order-detail-grid > div {
    display: flex;
    flex-direction: column;
    gap: 5px;
    padding: 12px;
    border: 1px solid #EEF0F2;
    border-radius: 9px;
    background: #FAFAF9;
}

.sales-order-detail-grid .sales-order-detail-full {
    grid-column: 1 / -1;
}

.sales-order-detail-grid strong {
    color: var(--sales-text);
    font-size: 13px;
    line-height: 1.45;
    font-weight: 750;
    overflow-wrap: anywhere;
}

.sale-view-note {
    margin-top: 16px;
    padding: 13px;
    border-radius: 9px;
    background: #F8F7F5;
}

.sale-view-note span {
    color: var(--sales-muted);
    font-size: 12px;
    line-height: 1.25;
    font-weight: 800;
}

.sale-view-note p {
    margin: 6px 0 0;
    color: var(--sales-text);
    font-size: 13px;
    line-height: 1.5;
}

.sale-view-totals {
    width: min(330px, 100%);
    margin: 18px 0 0 auto;
}

.sale-view-totals > div {
    display: flex;
    justify-content: space-between;
    gap: 14px;
    padding: 8px 0;
    color: var(--sales-muted);
    font-size: 13px;
    line-height: 1.4;
}

.sale-view-totals strong {
    color: var(--sales-text);
}

.sale-view-grand {
    margin-top: 5px;
    padding-top: 12px !important;
    border-top: 1px solid var(--sales-border);
    color: var(--sales-text) !important;
    font-size: 15px !important;
    font-weight: 800;
}

.sale-view-grand strong {
    color: var(--sales-brown) !important;
    font-size: 20px;
}

.sales-order-no-items {
    padding: 14px;
    border-radius: 9px;
    background: #F8F7F5;
    color: var(--sales-muted);
    font-size: 13px;
}

body.sale-dialog-open {
    overflow: hidden;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1250px) {

    .sales-filter-row {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .sales-filter-search {
        grid-column: span 2;
    }

    .sales-filter-row > button,
    .sales-filter-row > a {
        width: 100%;
    }

}

@media (max-width: 950px) {

    .sales-page {
        padding: 22px 20px 40px;
    }

    .sales-stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .sale-view-summary {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

}

@media (max-width: 700px) {

    .sales-page {
        padding: 18px 14px 32px;
    }

    .sales-page-header h1 {
        font-size: 24px;
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

    .sale-view-summary,
    .sales-order-detail-grid {
        grid-template-columns: 1fr;
    }

    .sales-order-status-control {
        flex-direction: column;
        align-items: stretch;
    }

    .sales-order-status-select,
    .sales-order-status-button {
        width: 100%;
    }

    .sales-order-detail-grid .sales-order-detail-full {
        grid-column: auto;
    }

    .sale-dialog-header,
    .sale-view-content {
        padding-left: 18px;
        padding-right: 18px;
    }

}

@media print {

    body * {
        visibility: hidden;
    }

    .sale-dialog.show,
    .sale-dialog.show * {
        visibility: visible;
    }

    .sale-dialog.show {
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
   ORDER TIMELINE
========================================================= */

.sales-order-section-heading {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 16px;
}

.sales-order-section-heading h3 {
    margin: 0;
    color: var(--sales-text);
    font-size: 16px;
    line-height: 1.35;
    font-weight: 800;
}

.sales-order-section-heading p {
    margin: 5px 0 0;
    color: var(--sales-muted);
    font-size: 12px;
    line-height: 1.45;
}

.sales-order-timeline {
    position: relative;
    display: grid;
    gap: 0;
    padding: 2px 0 2px 4px;
}

.sales-timeline-item {
    position: relative;
    display: grid;
    grid-template-columns: 30px minmax(0, 1fr);
    column-gap: 12px;
    min-height: 66px;
}

.sales-timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: 14px;
    top: 30px;
    bottom: 0;
    width: 2px;
    background: #E4E7EC;
}

.sales-timeline-item.is-complete:not(:last-child)::before {
    background: var(--sales-green);
}

.sales-timeline-marker {
    position: relative;
    z-index: 1;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #D0D5DD;
    border-radius: 50%;
    background: #fff;
    color: #98A2B3;
    font-size: 13px;
    font-weight: 800;
}

.sales-timeline-item.is-complete .sales-timeline-marker {
    border-color: var(--sales-green);
    background: var(--sales-green-bg);
    color: var(--sales-green);
}

.sales-timeline-content {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 14px;
    padding: 3px 0 20px;
}

.sales-timeline-content strong {
    color: var(--sales-text);
    font-size: 14px;
    line-height: 1.4;
    font-weight: 800;
}

.sales-timeline-content span {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    color: var(--sales-muted);
    font-size: 13px;
    line-height: 1.4;
    text-align: right;
    white-space: nowrap;
}

.sales-timeline-content span small {
    padding: 3px 7px;
    border-radius: 6px;
    background: var(--sales-brown-soft);
    color: var(--sales-brown);
    font-size: 10px;
    line-height: 1.2;
    font-weight: 800;
}

.sales-order-timeline-settings {
    margin-top: 18px;
    padding: 16px;
    border: 1px solid #E8DDD3;
    border-radius: 12px;
    background: #FCFAF8;
}

.sales-timeline-settings-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(110px, 1fr)) auto;
    align-items: end;
    gap: 10px;
}

.sales-timeline-settings-grid label {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.sales-timeline-settings-grid label > span {
    color: var(--sales-text);
    font-size: 11px;
    line-height: 1.25;
    font-weight: 800;
}

.sales-timeline-settings-grid input {
    width: 100%;
    height: 40px;
    padding: 0 11px;
    border: 1px solid #D0D5DD;
    border-radius: 8px;
    background: #fff;
    color: var(--sales-text);
    font: inherit;
    font-size: 13px;
    outline: none;
}

.sales-timeline-settings-grid input:focus {
    border-color: var(--sales-brown);
    box-shadow: 0 0 0 3px rgba(111, 78, 55, .10);
}

.sales-timeline-settings-grid label small {
    display: none;
}

.sales-order-timeline-save-button {
    min-height: 40px;
    padding: 0 15px;
    border: 1px solid var(--sales-brown);
    border-radius: 8px;
    background: var(--sales-brown);
    color: #fff;
    font: inherit;
    font-size: 13px;
    font-weight: 800;
    cursor: pointer;
    white-space: nowrap;
}

.sales-order-timeline-save-button:hover {
    background: var(--sales-brown-dark);
}

@media (max-width: 700px) {
    .sales-timeline-content {
        flex-direction: column;
        gap: 4px;
    }

    .sales-timeline-content span {
        text-align: left;
        white-space: normal;
    }

    .sales-timeline-settings-grid {
        grid-template-columns: 1fr;
    }

    .sales-order-timeline-save-button {
        width: 100%;
    }
}

</style>

@endpush


@push('scripts')

<script>

    // ============================================================
    // FIX: Sale page now reads online Orders only.
    // There is no New Sale / Walk-in Customer / POS form.
    // ============================================================

    function openOrderDialog(orderId) {

        const backdrop =
            document.getElementById('order-dialog-backdrop');

        const dialog =
            document.getElementById('order-dialog-' + orderId);

        if (!backdrop || !dialog) {
            return;
        }

        document
            .querySelectorAll('#order-dialog-backdrop .sale-dialog')
            .forEach(function (item) {
                item.classList.remove('show');
            });

        dialog.classList.add('show');

        backdrop.classList.add('show');
        backdrop.setAttribute('aria-hidden', 'false');

        document.body.classList.add('sale-dialog-open');
    }


    function closeOrderDialog() {

        const backdrop =
            document.getElementById('order-dialog-backdrop');

        if (!backdrop) {
            return;
        }

        document
            .querySelectorAll('#order-dialog-backdrop .sale-dialog')
            .forEach(function (item) {
                item.classList.remove('show');
            });

        backdrop.classList.remove('show');
        backdrop.setAttribute('aria-hidden', 'true');

        document.body.classList.remove('sale-dialog-open');
    }


    document.addEventListener('click', function (event) {

        const backdrop =
            document.getElementById('order-dialog-backdrop');

        if (backdrop && event.target === backdrop) {
            closeOrderDialog();
        }

    });


    document.addEventListener('keydown', function (event) {

        if (event.key === 'Escape') {
            closeOrderDialog();
        }

    });

</script>

@endpush