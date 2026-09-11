@extends('admin.layouts.app')

@section('title', 'Add Product')
@section('page-title', 'Add Product')

@section('content')

<div class="product-create-page">

    {{-- HEADER --}}
    <div class="product-create-header">

        <div>
            <span class="product-create-eyebrow">
                PRODUCT CATALOG
            </span>

            <h1>Add New Product</h1>

            <p>
                Create a product, variant and pricing information.
            </p>
        </div>

        <a
            href="{{ route('admin.products.index') }}"
            class="product-back-button"
        >
            ← Back to Products
        </a>

    </div>


    {{-- ERRORS --}}
    @if($errors->any())

        <div class="product-error">

            <strong>Please check the following:</strong>

            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

        </div>

    @endif


    {{-- FORM --}}
    <form
        method="POST"
        action="{{ route('admin.products.store') }}"
        enctype="multipart/form-data"
        class="product-form"
    >

        @csrf


        {{-- ========================================================= --}}
        {{-- PRODUCT INFORMATION --}}
        {{-- ========================================================= --}}

        <section class="product-form-card">

            <div class="product-form-card-header">

                <div>

                    <span>PRODUCT INFORMATION</span>

                    <h2>Basic Details</h2>

                </div>

            </div>


            <div class="product-form-grid">


                {{-- PRODUCT NAME --}}
                <div class="product-field product-field-full">

                    <label for="name">
                        Product Name
                        <span>*</span>
                    </label>

                    <input
                        id="name"
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        placeholder="Enter product name"
                        required
                    >

                </div>


                {{-- CATEGORY --}}
                <div class="product-field">

                    <label for="category_id">
                        Category
                        <span>*</span>
                    </label>

                    <select
                        id="category_id"
                        name="category_id"
                        required
                    >

                        <option value="">
                            Select category
                        </option>

                        @foreach($categories as $category)

                            <option
                                value="{{ $category->id }}"
                                @selected(old('category_id') == $category->id)
                            >
                                {{ $category->name }}
                            </option>

                        @endforeach

                    </select>

                </div>

                {{-- GENDER --}}
                <div class="product-field">

                    <label for="gender">
                        Gender
                    </label>

                    <select
                        id="gender"
                        name="gender"
                    >

                        <option value="">
                            Select gender
                        </option>

                        <option
                            value="Men"
                            @selected(old('gender') === 'Men')
                        >
                            Men
                        </option>

                        <option
                            value="Women"
                            @selected(old('gender') === 'Women')
                        >
                            Women
                        </option>

                        <option
                            value="Unisex"
                            @selected(old('gender') === 'Unisex')
                        >
                            Unisex
                        </option>

                        <option
                            value="Kids"
                            @selected(old('gender') === 'Kids')
                        >
                            Kids
                        </option>

                    </select>

                </div>


                {{-- STATUS --}}
                <div class="product-field">

                    <label for="status">
                        Product Status
                    </label>

                    <select
                        id="status"
                        name="status"
                    >

                        <option
                            value="1"
                            @selected(old('status', '1') == '1')
                        >
                            Active
                        </option>

                        <option
                            value="0"
                            @selected(old('status') == '0')
                        >
                            Inactive
                        </option>

                    </select>

                </div>


                {{-- DESCRIPTION --}}
                <div class="product-field product-field-full">

                    <label for="description">
                        Description
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        rows="5"
                        placeholder="Enter product description..."
                    >{{ old('description') }}</textarea>

                </div>


                {{-- IMAGE --}}
                <div class="product-field product-field-full">

                    <label for="image">
                        Product Image
                    </label>

                    <input
                        id="image"
                        type="file"
                        name="image"
                        accept="image/jpeg,image/png,image/webp"
                    >

                    <small>
                        JPG, PNG or WEBP. Maximum 2MB.
                    </small>

                </div>

            </div>

        </section>


        {{-- ========================================================= --}}
        {{-- PRODUCT VARIANT --}}
        {{-- ========================================================= --}}

        <section class="product-form-card">

            <div class="product-form-card-header">

                <div>

                    <span>PRODUCT VARIANT</span>

                    <h2>SKU & Pricing</h2>

                </div>

            </div>


            <div class="variant-info-box">

                <div class="variant-info-icon">
                    $
                </div>

                <div>

                    <strong>
                        Pricing is stored per variant
                    </strong>

                    <p>
                        Cost Price is your purchase cost.
                        Selling Price is the customer selling price.
                        These values will be used to calculate gross profit.
                    </p>

                </div>

            </div>


            <div class="product-form-grid">


                {{-- SKU --}}
                <div class="product-field">

                    <label for="sku">
                        SKU
                        <span>*</span>
                    </label>

                    <input
                        id="sku"
                        type="text"
                        name="sku"
                        value="{{ old('sku') }}"
                        placeholder="e.g. TS-BLK-M"
                        required
                    >

                    <small>
                        Unique product variant code.
                    </small>

                </div>


                {{-- BARCODE --}}
                <div class="product-field">

                    <label for="barcode">
                        Barcode
                    </label>

                    <input
                        id="barcode"
                        type="text"
                        name="barcode"
                        value="{{ old('barcode') }}"
                        placeholder="Scan or enter barcode"
                    >

                </div>


                {{-- SIZE --}}
                <div class="product-field">

                    <label for="size">
                        Size
                    </label>

                    <select
                        id="size"
                        name="size"
                    >

                        <option value="">
                            Select size
                        </option>

                        <option
                            value="XS"
                            @selected(old('size') === 'XS')
                        >
                            XS
                        </option>

                        <option
                            value="S"
                            @selected(old('size') === 'S')
                        >
                            S
                        </option>

                        <option
                            value="M"
                            @selected(old('size') === 'M')
                        >
                            M
                        </option>

                        <option
                            value="L"
                            @selected(old('size') === 'L')
                        >
                            L
                        </option>

                        <option
                            value="XL"
                            @selected(old('size') === 'XL')
                        >
                            XL
                        </option>

                        <option
                            value="XXL"
                            @selected(old('size') === 'XXL')
                        >
                            XXL
                        </option>

                    </select>

                </div>


                {{-- COLOR --}}
                <div class="product-field">

                    <label for="color">
                        Color
                    </label>

                    <input
                        id="color"
                        type="text"
                        name="color"
                        value="{{ old('color') }}"
                        placeholder="e.g. Black"
                    >

                </div>


                {{-- COST PRICE --}}
                <div class="product-field price-field cost-price-field">

                    <label for="cost_price">
                        Cost Price
                        <span>*</span>
                    </label>

                    <div class="price-input">

                        <span>$</span>

                        <input
                            id="cost_price"
                            type="number"
                            name="cost_price"
                            value="{{ old('cost_price') }}"
                            placeholder="0.00"
                            min="0"
                            step="0.01"
                            required
                        >

                    </div>

                    <small>
                        Your purchase cost per unit.
                    </small>

                </div>


                {{-- SELLING PRICE --}}
                <div class="product-field price-field selling-price-field">

                    <label for="selling_price">
                        Selling Price
                        <span>*</span>
                    </label>

                    <div class="price-input">

                        <span>$</span>

                        <input
                            id="selling_price"
                            type="number"
                            name="selling_price"
                            value="{{ old('selling_price') }}"
                            placeholder="0.00"
                            min="0"
                            step="0.01"
                            required
                        >

                    </div>

                    <small>
                        Customer selling price per unit.
                    </small>

                </div>


                {{-- VARIANT STATUS --}}
                <div class="product-field">

                    <label for="variant_status">
                        Variant Status
                    </label>

                    <select
                        id="variant_status"
                        name="variant_status"
                    >

                        <option
                            value="1"
                            @selected(old('variant_status', '1') == '1')
                        >
                            Active
                        </option>

                        <option
                            value="0"
                            @selected(old('variant_status') == '0')
                        >
                            Inactive
                        </option>

                    </select>

                </div>


            </div>
            {{-- DISCOUNT TYPE --}}
            <div class="product-field">

                <label for="discount_type">
                    Discount Type
                    <span>*</span>
                </label>

                <select
                    id="discount_type"
                    name="discount_type"
                    required
                >

                    <option
                        value="percentage"
                        @selected(old('discount_type', 'percentage') === 'percentage')
                    >
                        Percentage (%)
                    </option>

                    <option
                        value="fixed"
                        @selected(old('discount_type') === 'fixed')
                    >
                        Fixed Amount ($)
                    </option>

                </select>

            </div>


            {{-- DISCOUNT VALUE --}}
            <div class="product-field">

                <label for="discount_value">
                    Discount
                    <span>*</span>
                </label>

                <div class="price-input">

                    <span id="discountSymbol">%</span>

                    <input
                        id="discount_value"
                        type="number"
                        name="discount_value"
                        value="{{ old('discount_value', 0) }}"
                        placeholder="0"
                        min="0"
                        step="0.01"
                        required
                    >

                </div>

                <small>
                    Enter 0 if this product has no discount.
                </small>

            </div>


            {{-- FINAL PRICE --}}
            <div class="product-field">

                <label>
                    Final Selling Price
                </label>

                <div class="price-input">

                    <span>$</span>

                    <input
                        id="final_price"
                        type="text"
                        value="$0.00"
                        readonly
                    >

                </div>

                <small>
                    Price after discount.
                </small>

            </div>


            {{-- PROFIT PREVIEW --}}
            <div class="profit-preview">

                <div>

                    <span>UNIT GROSS PROFIT</span>

                    <strong id="profitPreview">
                        $0.00
                    </strong>

                </div>

                <div>

                    <span>GROSS MARGIN</span>

                    <strong id="marginPreview">
                        0.00%
                    </strong>

                </div>

            </div>

        </section>


        {{-- ACTIONS --}}
        <div class="product-form-actions">

            <a
                href="{{ route('admin.products.index') }}"
                class="product-cancel-button"
            >
                Cancel
            </a>

            <button
                type="submit"
                class="product-save-button"
            >
                Save Product
            </button>

        </div>

    </form>

</div>

@endsection


@push('styles')

<style>

.product-create-page {
    width: 100%;
    color: #24201E;
}

.product-create-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 22px;
}

.product-create-eyebrow {
    display: block;
    margin-bottom: 6px;
    color: #A66A44;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.4px;
}

.product-create-header h1 {
    margin: 0;
    color: #2C1E17;
    font-size: 28px;
    font-weight: 800;
}

.product-create-header p {
    margin: 7px 0 0;
    color: #8B8580;
    font-size: 13px;
}

.product-back-button {
    min-height: 40px;
    padding: 0 15px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(44,30,23,.09);
    border-radius: 10px;
    background: #FFFFFF;
    color: #6F4E37;
    text-decoration: none;
    font-size: 11px;
    font-weight: 700;
}

.product-back-button:hover {
    background: #F5EDE3;
}

.product-error {
    margin-bottom: 18px;
    padding: 14px 16px;
    border: 1px solid rgba(200,92,74,.20);
    border-radius: 12px;
    background: rgba(200,92,74,.06);
    color: #C85C4A;
    font-size: 12px;
}

.product-error ul {
    margin: 8px 0 0 18px;
    padding: 0;
}

.product-form {
    width: 100%;
}

.product-form-card {
    margin-bottom: 18px;
    padding: 22px;
    border: 1px solid rgba(44,30,23,.07);
    border-radius: 17px;
    background: #FFFFFF;
    box-shadow: 0 8px 28px rgba(44,30,23,.045);
}

.product-form-card-header {
    margin-bottom: 20px;
    padding-bottom: 14px;
    border-bottom: 1px solid rgba(44,30,23,.06);
}

.product-form-card-header span {
    color: #A66A44;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 1.2px;
}

.product-form-card-header h2 {
    margin: 5px 0 0;
    color: #2C1E17;
    font-size: 17px;
    font-weight: 800;
}

.product-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
}

.product-field-full {
    grid-column: 1 / -1;
}

.product-field label {
    display: block;
    margin-bottom: 7px;
    color: #2C1E17;
    font-size: 11px;
    font-weight: 750;
}

.product-field label span {
    color: #C85C4A;
}

.product-field input,
.product-field select,
.product-field textarea {
    width: 100%;
    box-sizing: border-box;

    border: 1px solid rgba(44,30,23,.10);
    border-radius: 10px;

    background: #FAF8F5;
    color: #24201E;

    outline: none;
    font-family: inherit;
    font-size: 12px;

    transition: .2s ease;
}

.product-field input,
.product-field select {
    height: 43px;
    padding: 0 12px;
}

.product-field textarea {
    padding: 12px;
    resize: vertical;
}

.product-field input:focus,
.product-field select:focus,
.product-field textarea:focus {
    border-color: #A66A44;
    background: #FFFFFF;
    box-shadow: 0 0 0 3px rgba(166,106,68,.09);
}

.product-field input::placeholder,
.product-field textarea::placeholder {
    color: #AAA39E;
}

.product-field small {
    display: block;
    margin-top: 6px;
    color: #8B8580;
    font-size: 10px;
}


/* =========================================================
   VARIANT INFO
   ========================================================= */

.variant-info-box {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 20px;
    padding: 14px;
    border: 1px solid rgba(166,106,68,.15);
    border-radius: 12px;
    background: #F5EDE3;
}

.variant-info-icon {
    width: 32px;
    height: 32px;
    flex: 0 0 32px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 9px;
    background: #6F4E37;
    color: #FFFFFF;

    font-size: 14px;
    font-weight: 800;
}

.variant-info-box strong {
    display: block;
    margin-bottom: 4px;
    color: #2C1E17;
    font-size: 12px;
}

.variant-info-box p {
    margin: 0;
    color: #8B8580;
    font-size: 10px;
    line-height: 1.6;
}


/* =========================================================
   PRICE
   ========================================================= */

.price-input {
    position: relative;
}

.price-input > span {
    position: absolute;
    left: 13px;
    top: 50%;
    transform: translateY(-50%);

    color: #8B8580;
    font-size: 12px;
    font-weight: 700;

    pointer-events: none;
}

.price-input input {
    padding-left: 30px !important;
}

.cost-price-field .price-input input:focus {
    border-color: #A66A44;
}

.selling-price-field .price-input input:focus {
    border-color: #5F8D5A;
}


/* =========================================================
   PROFIT PREVIEW
   ========================================================= */

.profit-preview {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    margin-top: 20px;
    padding: 16px;

    border: 1px solid rgba(44,30,23,.07);
    border-radius: 13px;

    background: #FAF8F5;
}

.profit-preview > div {
    padding: 12px 14px;
    border-radius: 10px;
    background: #FFFFFF;
}

.profit-preview span {
    display: block;
    margin-bottom: 5px;

    color: #8B8580;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: .8px;
}

.profit-preview strong {
    color: #5F8D5A;
    font-size: 20px;
    font-weight: 800;
}


/* =========================================================
   ACTIONS
   ========================================================= */

.product-form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 18px;
}

.product-cancel-button,
.product-save-button {
    min-height: 42px;
    padding: 0 18px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    border-radius: 10px;

    font-family: inherit;
    font-size: 11px;
    font-weight: 800;

    text-decoration: none;
    cursor: pointer;
}

.product-cancel-button {
    border: 1px solid rgba(44,30,23,.09);
    background: #FFFFFF;
    color: #6F4E37;
}

.product-cancel-button:hover {
    background: #F5EDE3;
}

.product-save-button {
    border: none;
    background: #6F4E37;
    color: #FFFFFF;
    box-shadow: 0 5px 14px rgba(111,78,55,.15);
}

.product-save-button:hover {
    background: #2C1E17;
}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 700px) {

    .product-create-header {
        align-items: stretch;
        flex-direction: column;
    }

    .product-back-button {
        width: 100%;
    }

    .product-form-grid {
        grid-template-columns: 1fr;
    }

    .product-field-full {
        grid-column: auto;
    }

    .profit-preview {
        grid-template-columns: 1fr;
    }

    .product-form-actions {
        flex-direction: column-reverse;
    }

    .product-cancel-button,
    .product-save-button {
        width: 100%;
    }
}

</style>

@endpush


@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {

    const costInput =
        document.getElementById('cost_price');

    const sellingInput =
        document.getElementById('selling_price');

    const discountType =
        document.getElementById('discount_type');

    const discountInput =
        document.getElementById('discount_value');

    const discountSymbol =
        document.getElementById('discountSymbol');

    const finalPriceInput =
        document.getElementById('final_price');

    const profitPreview =
        document.getElementById('profitPreview');

    const marginPreview =
        document.getElementById('marginPreview');


    function updatePricing() {

        const cost =
            parseFloat(costInput.value) || 0;

        const selling =
            parseFloat(sellingInput.value) || 0;

        const discount =
            parseFloat(discountInput.value) || 0;


        let discountAmount = 0;


        /*
        |--------------------------------------------------------------------------
        | Calculate Discount
        |--------------------------------------------------------------------------
        */

        if (
            discountType.value === 'percentage'
        ) {

            discountAmount =
                selling * discount / 100;

            discountSymbol.textContent = '%';

        } else {

            discountAmount = discount;

            discountSymbol.textContent = '$';

        }


        /*
        |--------------------------------------------------------------------------
        | Final Price
        |--------------------------------------------------------------------------
        */

        const finalPrice =
            Math.max(
                0,
                selling - discountAmount
            );


        /*
        |--------------------------------------------------------------------------
        | Gross Profit
        |--------------------------------------------------------------------------
        */

        const profit =
            finalPrice - cost;


        /*
        |--------------------------------------------------------------------------
        | Gross Margin
        |--------------------------------------------------------------------------
        */

        const margin =
            finalPrice > 0
                ? (profit / finalPrice) * 100
                : 0;


        /*
        |--------------------------------------------------------------------------
        | UI
        |--------------------------------------------------------------------------
        */

        finalPriceInput.value =
            '$' + finalPrice.toFixed(2);


        profitPreview.textContent =
            '$' + profit.toFixed(2);


        marginPreview.textContent =
            margin.toFixed(2) + '%';


        /*
        |--------------------------------------------------------------------------
        | Profit Color
        |--------------------------------------------------------------------------
        */

        if (profit < 0) {

            profitPreview.style.color =
                '#C85C4A';

            marginPreview.style.color =
                '#C85C4A';

        } else {

            profitPreview.style.color =
                '#5F8D5A';

            marginPreview.style.color =
                '#5F8D5A';

        }

    }


    costInput.addEventListener(
        'input',
        updatePricing
    );

    sellingInput.addEventListener(
        'input',
        updatePricing
    );

    discountInput.addEventListener(
        'input',
        updatePricing
    );

    discountType.addEventListener(
        'change',
        updatePricing
    );


    updatePricing();

});

</script>

@endpush