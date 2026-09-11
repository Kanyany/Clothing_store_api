@extends('admin.layouts.app')

@section('title', 'New Purchase')

@section('page-title', 'New Purchase')

@section('content')

<style>

    .purchase-create {
        max-width: 1100px;
    }

    .purchase-create-title {
        color: #24201E;
        font-size: 20px;
        font-weight: 800;
    }

    .purchase-create-subtitle {
        margin-top: 4px;
        margin-bottom: 18px;
        color: #8B8580;
        font-size: 10px;
    }

    .purchase-form-card {
        margin-bottom: 14px;
        padding: 20px;
        background: #FFFFFF;
        border: 1px solid rgba(44,30,23,.08);
        border-radius: 11px;
    }

    .purchase-section-title {
        margin-bottom: 15px;
        color: #24201E;
        font-size: 13px;
        font-weight: 800;
    }

    .purchase-fields {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 14px;
    }

    .purchase-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .purchase-field.full {
        grid-column: 1 / -1;
    }

    .purchase-field label {
        color: #6F4E37;
        font-size: 10px;
        font-weight: 700;
    }

    .purchase-field input,
    .purchase-field select,
    .purchase-field textarea {
        width: 100%;
        box-sizing: border-box;
        padding: 10px 11px;
        border: 1px solid #E4DDD6;
        border-radius: 8px;
        outline: none;
        background: #FFFFFF;
        color: #24201E;
        font-family: inherit;
        font-size: 10px;
    }

    .purchase-field textarea {
        min-height: 80px;
        resize: vertical;
    }

    .purchase-items {
        overflow-x: auto;
    }

    .purchase-items-table {
        width: 100%;
        min-width: 800px;
        border-collapse: collapse;
    }

    .purchase-items-table th {
        padding: 10px;
        background: #FAF8F5;
        color: #8B8580;
        text-align: left;
        font-size: 9px;
    }

    .purchase-items-table td {
        padding: 8px;
        border-bottom: 1px solid #F0EBE6;
    }

    .purchase-items-table input,
    .purchase-items-table select {
        width: 100%;
        box-sizing: border-box;
        padding: 8px;
        border: 1px solid #E4DDD6;
        border-radius: 7px;
        outline: none;
        font-family: inherit;
        font-size: 10px;
    }

    .purchase-row-total {
        font-size: 10px;
        font-weight: 800;
        white-space: nowrap;
    }

    .remove-item {
        width: 29px;
        height: 29px;
        border: 0;
        border-radius: 7px;
        background: rgba(200,92,74,.10);
        color: #ff0000;
        cursor: pointer;
    }

    .add-item {
        margin-top: 12px;
        height: 32px;
        padding: 0 12px;
        border: 1px solid #E4DDD6;
        border-radius: 7px;
        background: #FFFFFF;
        color: #6F4E37;
        font-size: 10px;
        font-weight: 700;
        cursor: pointer;
    }

    .purchase-total-box {
        display: flex;
        justify-content: flex-end;
        margin-top: 18px;
    }

    .purchase-total {
        width: 280px;
        display: flex;
        justify-content: space-between;
        padding-top: 12px;
        border-top: 1px solid #E4DDD6;
        color: #24201E;
        font-size: 14px;
        font-weight: 800;
    }

    .purchase-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        margin-top: 16px;
    }

    .purchase-cancel,
    .purchase-save {
        height: 38px;
        padding: 0 16px;
        border-radius: 8px;
        font-family: inherit;
        font-size: 10px;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
    }

    .purchase-cancel {
        border: 1px solid #E4DDD6;
        background: #FFFFFF;
        color: #6F4E37;
    }

    .purchase-save {
        border: 0;
        background: #6F4E37;
        color: #FFFFFF;
    }

    .purchase-errors {
        margin-bottom: 15px;
        padding: 12px;
        border-radius: 8px;
        background: rgba(200,92,74,.10);
        color: #ff0000;
        font-size: 10px;
    }

    @media (max-width: 700px) {

        .purchase-fields {
            grid-template-columns: 1fr;
        }

        .purchase-field.full {
            grid-column: auto;
        }
    }

</style>


<div class="purchase-create">

    <div class="purchase-create-title">
        New Purchase
    </div>

    <div class="purchase-create-subtitle">
        Create a purchase using products and variants from the database.
    </div>


    @if($errors->any())

        <div class="purchase-errors">

            <strong>
                Please fix:
            </strong>

            <ul>

                @foreach($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

        </div>

    @endif


    <form
        method="POST"
        action="{{ route('admin.purchases.store') }}"
    >

        @csrf


        {{-- PURCHASE INFORMATION --}}

        <div class="purchase-form-card">

            <div class="purchase-section-title">
                Purchase Information
            </div>


            <div class="purchase-fields">

                <div class="purchase-field">

                    <label>
                        Supplier Name
                    </label>

                    <input
                        type="text"
                        name="supplier_name"
                        value="{{ old('supplier_name') }}"
                        placeholder="Enter supplier name"
                    >

                </div>


                <div class="purchase-field">

                    <label>
                        Purchase Date
                    </label>

                    <input
                        type="date"
                        name="purchase_date"
                        value="{{ old(
                            'purchase_date',
                            now()->format('Y-m-d')
                        ) }}"
                        required
                    >

                </div>


                <div class="purchase-field">

                    <label>
                        Status
                    </label>

                    <select
                        name="status"
                        required
                    >

                        <option
                            value="draft"
                            {{ old('status', 'draft') === 'draft'
                                ? 'selected'
                                : '' }}
                        >
                            Draft
                        </option>

                        <option
                            value="received"
                            {{ old('status') === 'received'
                                ? 'selected'
                                : '' }}
                        >
                            Received
                        </option>

                        <option
                            value="cancelled"
                            {{ old('status') === 'cancelled'
                                ? 'selected'
                                : '' }}
                        >
                            Cancelled
                        </option>

                    </select>

                </div>


                <div class="purchase-field full">

                    <label>
                        Note
                    </label>

                    <textarea
                        name="note"
                        placeholder="Optional note"
                    >{{ old('note') }}</textarea>

                </div>

            </div>

        </div>


        {{-- ITEMS --}}

        <div class="purchase-form-card">

            <div class="purchase-section-title">
                Purchase Items
            </div>


            <div class="purchase-items">

                <table class="purchase-items-table">

                    <thead>

                        <tr>

                            <th>
                                Product / Variant
                            </th>

                            <th style="width:120px;">
                                Quantity
                            </th>

                            <th style="width:140px;">
                                Cost Price
                            </th>

                            <th style="width:100px;">
                                Subtotal
                            </th>

                            <th style="width:45px;">
                            </th>

                        </tr>

                    </thead>


                    <tbody id="purchase-items-body">

                        <tr class="purchase-item-row">

                            <td>

                                <select
                                    name="items[0][product_variant_id]"
                                    class="variant-select"
                                    required
                                >

                                    <option value="">
                                        Select product / variant
                                    </option>

                                    @foreach($variants as $variant)

                                        <option
                                            value="{{ $variant->id }}"
                                            data-cost="{{ $variant->cost_price }}"
                                        >

                                            {{ $variant->product?->name ?? '—' }}

                                            @if($variant->size)
                                                / {{ $variant->size }}
                                            @endif

                                            @if($variant->color)
                                                / {{ $variant->color }}
                                            @endif

                                        </option>

                                    @endforeach

                                </select>

                            </td>


                            <td>

                                <input
                                    type="number"
                                    name="items[0][quantity]"
                                    class="quantity-input"
                                    min="1"
                                    value="1"
                                    required
                                >

                            </td>


                            <td>

                                <input
                                    type="number"
                                    name="items[0][cost_price]"
                                    class="cost-input"
                                    min="0"
                                    step="0.01"
                                    value="0"
                                    required
                                >

                            </td>


                            <td>

                                <div class="purchase-row-total">
                                    $0.00
                                </div>

                            </td>


                            <td>

                                <button
                                    type="button"
                                    class="remove-item"
                                    onclick="removePurchaseItem(this)"
                                >
                                    ×
                                </button>

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>


            <button
                type="button"
                class="add-item"
                onclick="addPurchaseItem()"
            >
                + Add Item
            </button>


            <div class="purchase-total-box">

                <div class="purchase-total">

                    <span>
                        Grand Total
                    </span>

                    <span id="grand-total">
                        $0.00
                    </span>

                </div>

            </div>

        </div>


        <div class="purchase-actions">

            <a
                href="{{ route('admin.purchases.index') }}"
                class="purchase-cancel"
            >
                Cancel
            </a>

            <button
                type="submit"
                class="purchase-save"
            >
                Save Purchase
            </button>

        </div>

    </form>

</div>


<script>

    let purchaseItemIndex = 1;


    function addPurchaseItem() {

        const body =
            document.getElementById(
                'purchase-items-body'
            );

        const row =
            document.createElement('tr');

        row.className =
            'purchase-item-row';


        row.innerHTML = `

            <td>

                <select
                    name="items[${purchaseItemIndex}][product_variant_id]"
                    class="variant-select"
                    required
                >

                    <option value="">
                        Select product / variant
                    </option>

                    @foreach($variants as $variant)

                        <option
                            value="{{ $variant->id }}"
                            data-cost="{{ $variant->cost_price }}"
                        >

                            {{ $variant->product?->name ?? '—' }}

                            @if($variant->size)
                                / {{ $variant->size }}
                            @endif

                            @if($variant->color)
                                / {{ $variant->color }}
                            @endif

                        </option>

                    @endforeach

                </select>

            </td>

            <td>

                <input
                    type="number"
                    name="items[${purchaseItemIndex}][quantity]"
                    class="quantity-input"
                    min="1"
                    value="1"
                    required
                >

            </td>

            <td>

                <input
                    type="number"
                    name="items[${purchaseItemIndex}][cost_price]"
                    class="cost-input"
                    min="0"
                    step="0.01"
                    value="0"
                    required
                >

            </td>

            <td>

                <div class="purchase-row-total">
                    $0.00
                </div>

            </td>

            <td>

                <button
                    type="button"
                    class="remove-item"
                    onclick="removePurchaseItem(this)"
                >
                    ×
                </button>

            </td>

        `;


        body.appendChild(row);

        purchaseItemIndex++;

        updatePurchaseTotal();
    }


    function removePurchaseItem(button) {

        const rows =
            document.querySelectorAll(
                '.purchase-item-row'
            );

        if (rows.length <= 1) {
            return;
        }

        button
            .closest('.purchase-item-row')
            .remove();

        updatePurchaseTotal();
    }


    function updatePurchaseTotal() {

        let grandTotal = 0;


        document
            .querySelectorAll('.purchase-item-row')
            .forEach(function (row) {

                const quantity =
                    parseFloat(
                        row.querySelector(
                            '.quantity-input'
                        )?.value || 0
                    );

                const cost =
                    parseFloat(
                        row.querySelector(
                            '.cost-input'
                        )?.value || 0
                    );


                const subtotal =
                    quantity * cost;


                const totalElement =
                    row.querySelector(
                        '.purchase-row-total'
                    );


                if (totalElement) {

                    totalElement.textContent =
                        '$' +
                        subtotal.toFixed(2);
                }


                grandTotal += subtotal;

            });


        document.getElementById(
            'grand-total'
        ).textContent =
            '$' +
            grandTotal.toFixed(2);
    }


    document.addEventListener(
        'change',
        function (event) {

            if (
                event.target.classList.contains(
                    'variant-select'
                )
            ) {

                const option =
                    event.target.selectedOptions[0];

                const cost =
                    option?.dataset?.cost || '0';

                const row =
                    event.target.closest(
                        '.purchase-item-row'
                    );

                const costInput =
                    row.querySelector(
                        '.cost-input'
                    );


                if (
                    costInput &&
                    (
                        costInput.value === '' ||
                        costInput.value === '0'
                    )
                ) {

                    costInput.value = cost;
                }


                updatePurchaseTotal();
            }

        }
    );


    document.addEventListener(
        'input',
        function (event) {

            if (
                event.target.classList.contains(
                    'quantity-input'
                ) ||
                event.target.classList.contains(
                    'cost-input'
                )
            ) {

                updatePurchaseTotal();
            }

        }
    );


    updatePurchaseTotal();

</script>

@endsection