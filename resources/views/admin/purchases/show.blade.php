@extends('admin.layouts.app')

@section('title', 'Purchase Details')

@section('page-title', 'Purchase Details')

@section('content')

<style>

    .purchase-detail {
        width: 100%;
    }

    .purchase-detail-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 18px;
    }

    .purchase-detail-title {
        color: #24201E;
        font-size: 20px;
        font-weight: 800;
    }

    .purchase-detail-subtitle {
        margin-top: 4px;
        color: #8B8580;
        font-size: 10px;
    }

    .purchase-back {
        height: 34px;
        padding: 0 12px;
        border-radius: 8px;
        background: #F5EDE3;
        color: #6F4E37;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        font-size: 10px;
        font-weight: 700;
    }

    .purchase-detail-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 300px;
        gap: 14px;
    }

    .purchase-detail-card {
        background: #FFFFFF;
        border: 1px solid rgba(44,30,23,.08);
        border-radius: 11px;
        overflow: hidden;
    }

    .purchase-card-header {
        padding: 15px 18px;
        border-bottom: 1px solid #F0EBE6;
    }

    .purchase-card-title {
        color: #24201E;
        font-size: 13px;
        font-weight: 800;
    }

    .purchase-info {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        padding: 18px;
    }

    .purchase-info-label {
        color: #8B8580;
        font-size: 9px;
    }

    .purchase-info-value {
        margin-top: 4px;
        color: #24201E;
        font-size: 10px;
        font-weight: 700;
    }

    .purchase-table-wrapper {
        overflow-x: auto;
    }

    .purchase-detail-table {
        width: 100%;
        min-width: 600px;
        border-collapse: collapse;
    }

    .purchase-detail-table th {
        padding: 10px 12px;
        background: #FAF8F5;
        color: #8B8580;
        text-align: left;
        font-size: 9px;
    }

    .purchase-detail-table td {
        padding: 12px;
        border-bottom: 1px solid #F3EFEB;
        color: #24201E;
        font-size: 10px;
    }

    .purchase-product {
        color: #6F4E37;
        font-weight: 700;
    }

    .purchase-summary {
        padding: 18px;
    }

    .purchase-summary-row {
        display: flex;
        justify-content: space-between;
        padding: 7px 0;
        color: #6F6A66;
        font-size: 10px;
    }

    .purchase-summary-row.total {
        margin-top: 8px;
        padding-top: 12px;
        border-top: 1px solid #E7E0DA;
        color: #24201E;
        font-size: 14px;
        font-weight: 800;
    }

    .purchase-status {
        display: inline-flex;
        padding: 5px 8px;
        border-radius: 7px;
        font-size: 9px;
        font-weight: 800;
    }

    .purchase-status.draft {
        background: rgba(166,106,68,.10);
        color: #A66A44;
    }

    .purchase-status.received {
        background: rgba(95,141,90,.10);
        color: #64DD17;
    }

    .purchase-status.cancelled {
        background: rgba(200,92,74,.10);
        color: #ff0000;
    }

    .receive-box {
        padding: 18px;
        border-top: 1px solid #F0EBE6;
    }

    .receive-button {
        width: 100%;
        height: 38px;
        border: 0;
        border-radius: 8px;
        background: #6F4E37;
        color: #FFFFFF;
        font-family: inherit;
        font-size: 10px;
        font-weight: 800;
        cursor: pointer;
    }

    .received-message {
        padding: 18px;
        color: #64DD17;
        font-size: 10px;
        font-weight: 700;
    }

    @media (max-width: 850px) {

        .purchase-detail-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 550px) {

        .purchase-info {
            grid-template-columns: 1fr;
        }
    }

</style>


<div class="purchase-detail">


    {{-- HEADER --}}

    <div class="purchase-detail-header">

        <div>

            <div class="purchase-detail-title">

                Purchase
                #PO-{{
                    str_pad(
                        $purchase->id,
                        5,
                        '0',
                        STR_PAD_LEFT
                    )
                }}

            </div>

            <div class="purchase-detail-subtitle">
                Purchase details and receiving
            </div>

        </div>


        <a
            href="{{ route('admin.purchases.index') }}"
            class="purchase-back"
        >
            ← Back
        </a>

    </div>


    <div class="purchase-detail-grid">


        {{-- LEFT --}}

        <div>


            {{-- INFORMATION --}}

            <div class="purchase-detail-card">

                <div class="purchase-card-header">

                    <div class="purchase-card-title">
                        Purchase Information
                    </div>

                </div>


                <div class="purchase-info">

                    <div>

                        <div class="purchase-info-label">
                            Purchase ID
                        </div>

                        <div class="purchase-info-value">

                            #PO-{{
                                str_pad(
                                    $purchase->id,
                                    5,
                                    '0',
                                    STR_PAD_LEFT
                                )
                            }}

                        </div>

                    </div>


                    <div>

                        <div class="purchase-info-label">
                            Supplier
                        </div>

                        <div class="purchase-info-value">

                            {{
                                $purchase->supplier_name
                                ?: '—'
                            }}

                        </div>

                    </div>


                    <div>

                        <div class="purchase-info-label">
                            Purchase Date
                        </div>

                        <div class="purchase-info-value">

                            {{
                                $purchase->purchase_date
                                    ?->format('d M Y')
                            }}

                        </div>

                    </div>


                    <div>

                        <div class="purchase-info-label">
                            Status
                        </div>

                        <div class="purchase-info-value">

                            @if($purchase->status === 'received')

                                <span class="purchase-status received">
                                    Received
                                </span>

                            @elseif($purchase->status === 'cancelled')

                                <span class="purchase-status cancelled">
                                    Cancelled
                                </span>

                            @else

                                <span class="purchase-status draft">
                                    Draft
                                </span>

                            @endif

                        </div>

                    </div>

                </div>

            </div>


            {{-- PRODUCTS --}}

            <div
                class="purchase-detail-card"
                style="margin-top:14px;"
            >

                <div class="purchase-card-header">

                    <div class="purchase-card-title">
                        Products
                    </div>

                </div>


                <div class="purchase-table-wrapper">

                    <table class="purchase-detail-table">

                        <thead>

                            <tr>

                                <th>
                                    Product
                                </th>

                                <th>
                                    Variant
                                </th>

                                <th>
                                    Quantity
                                </th>

                                <th>
                                    Cost
                                </th>

                                <th>
                                    Subtotal
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @foreach($purchase->items as $item)

                                <tr>

                                    <td>

                                        <span class="purchase-product">

                                            {{
                                                $item
                                                    ->productVariant
                                                    ?->product
                                                    ?->name
                                                ?? '—'
                                            }}

                                        </span>

                                    </td>


                                    <td>

                                        {{
                                            $item
                                                ->productVariant
                                                ?->size
                                            ?? '—'
                                        }}

                                        @if(
                                            $item
                                                ->productVariant
                                                ?->color
                                        )

                                            /
                                            {{
                                                $item
                                                    ->productVariant
                                                    ->color
                                            }}

                                        @endif

                                    </td>


                                    <td>
                                        {{ $item->quantity }}
                                    </td>


                                    <td>

                                        ${{ number_format(
                                            (float) $item->cost_price,
                                            2
                                        ) }}

                                    </td>


                                    <td>

                                        ${{ number_format(
                                            (float) $item->subtotal,
                                            2
                                        ) }}

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


        {{-- RIGHT --}}

        <div>


            {{-- SUMMARY --}}

            <div class="purchase-detail-card">

                <div class="purchase-card-header">

                    <div class="purchase-card-title">
                        Purchase Summary
                    </div>

                </div>


                <div class="purchase-summary">

                    <div class="purchase-summary-row">

                        <span>
                            Total Items
                        </span>

                        <strong>
                            {{ $purchase->items->sum('quantity') }}
                        </strong>

                    </div>


                    <div class="purchase-summary-row total">

                        <span>
                            TOTAL
                        </span>

                        <strong>

                            ${{ number_format(
                                (float) $purchase->total_amount,
                                2
                            ) }}

                        </strong>

                    </div>

                </div>


                {{-- RECEIVE --}}

                @if($purchase->status === 'draft')

                    <div class="receive-box">

                        <form
                            method="POST"
                            action="{{ route(
                                'admin.purchases.receive',
                                $purchase->id
                            ) }}"
                            onsubmit="return confirm('Receive this purchase and add the quantities to inventory?');"
                        >

                            @csrf

                            <button
                                type="submit"
                                class="receive-button"
                            >
                                Receive Goods
                            </button>

                        </form>

                    </div>

                @elseif($purchase->status === 'received')

                    <div class="received-message">
                        ✓ Received — inventory updated.
                    </div>

                @endif

            </div>


            {{-- NOTE --}}

            @if($purchase->note)

                <div
                    class="purchase-detail-card"
                    style="margin-top:14px;"
                >

                    <div class="purchase-card-header">

                        <div class="purchase-card-title">
                            Note
                        </div>

                    </div>


                    <div class="purchase-summary">

                        {{ $purchase->note }}

                    </div>

                </div>

            @endif

        </div>

    </div>

</div>

@endsection