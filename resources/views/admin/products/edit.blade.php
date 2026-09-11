@extends('admin.layouts.app')

@section('title', 'Edit Product')
@section('page-title', 'Edit Product')

@section('content')

<div class="product-create-page">

    <div class="product-create-header">

        <div>

            <span class="product-create-eyebrow">
                PRODUCT CATALOG
            </span>

            <h1>Edit Product</h1>

            <p>
                Update product information, pricing and discount.
            </p>

        </div>

        <a
            href="{{ route('admin.products.index') }}"
            class="product-back-button"
        >
            ← Back to Products
        </a>

    </div>


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


    <form
        method="POST"
        action="{{ route('admin.products.update', $product) }}"
        enctype="multipart/form-data"
        class="product-form"
    >

        @csrf

        @method('PUT')


        {{-- PRODUCT INFORMATION --}}

        <section class="product-form-card">

            <div class="product-form-card-header">

                <span>PRODUCT INFORMATION</span>

                <h2>Basic Details</h2>

            </div>


            <div class="product-form-grid">


                {{-- PRODUCT ID --}}

                <div class="product-field">

                    <label>
                        Product ID
                    </label>

                    <input
                        type="text"
                        value="#{{ $product->id }}"
                        readonly
                    >

                </div>


                {{-- PRODUCT NAME --}}

                <div class="product-field">

                    <label for="name">
                        Product Name
                        <span>*</span>
                    </label>

                    <input
                        id="name"
                        type="text"
                        name="name"
                        value="{{ old('name', $product->name) }}"
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

                        @foreach($categories as $category)

                            <option
                                value="{{ $category->id }}"
                                @selected(
                                    old(
                                        'category_id',
                                        $product->category_id
                                    ) == $category->id
                                )
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

                        @foreach([
                            'Men',
                            'Women',
                            'Unisex',
                            'Kids'
                        ] as $gender)

                            <option
                                value="{{ $gender }}"
                                @selected(
                                    old(
                                        'gender',
                                        $product->gender
                                    ) === $gender
                                )
                            >
                                {{ $gender }}
                            </option>

                        @endforeach

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
                            @selected(
                                old(
                                    'status',
                                    $product->status ? '1' : '0'
                                ) == '1'
                            )
                        >
                            Active
                        </option>

                        <option
                            value="0"
                            @selected(
                                old(
                                    'status',
                                    $product->status ? '1' : '0'
                                ) == '0'
                            )
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
                    >{{ old('description', $product->description) }}</textarea>

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

                    @if($product->image)

                        <small>
                            Current image exists. Upload a new image
                            only if you want to replace it.
                        </small>

                    @else

                        <small>
                            No image uploaded.
                        </small>

                    @endif

                </div>

            </div>

        </section>


        {{-- VARIANT --}}

        <section class="product-form-card">

            <div class="product-form-card-header">

                <span>PRODUCT VARIANT</span>

                <h2>SKU, Pricing & Discount</h2>

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
                        value="{{ old('sku', $variant?->sku) }}"
                        required
                    >

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
                        value="{{ old('barcode', $variant?->barcode) }}"
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

                        @foreach([
                            'XS',
                            'S',
                            'M',
                            'L',
                            'XL',
                            'XXL'
                        ] as $size)

                            <option
                                value="{{ $size }}"
                                @selected(
                                    old(
                                        'size',
                                        $variant?->size
                                    ) === $size
                                )
                            >
                                {{ $size }}
                            </option>

                        @endforeach

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
                        value="{{ old('color', $variant?->color) }}"
                    >

                </div>


                {{-- COST PRICE --}}

                <div class="product-field">

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
                            value="{{ old('cost_price', $variant?->cost_price ?? 0) }}"
                            min="0"
                            step="0.01"
                            required
                        >

                    </div>

                </div>


                {{-- SELLING PRICE --}}

                <div class="product-field">

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
                            value="{{ old('selling_price', $variant?->selling_price ?? 0) }}"
                            min="0"
                            step="0.01"
                            required
                        >

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
                            @selected(
                                old(
                                    'discount_type',
                                    $variant?->discount_type ?? 'percentage'
                                ) === 'percentage'
                            )
                        >
                            Percentage (%)
                        </option>

                        <option
                            value="fixed"
                            @selected(
                                old(
                                    'discount_type',
                                    $variant?->discount_type
                                ) === 'fixed'
                            )
                        >
                            Fixed Amount ($)
                        </option>

                    </select>

                </div>


                {{-- DISCOUNT --}}

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
                            value="{{ old('discount_value', $variant?->discount_value ?? 0) }}"
                            min="0"
                            step="0.01"
                            required
                        >

                    </div>

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
                            @selected(
                                old(
                                    'variant_status',
                                    $variant?->status ? '1' : '0'
                                ) == '1'
                            )
                        >
                            Active
                        </option>

                        <option
                            value="0"
                            @selected(
                                old(
                                    'variant_status',
                                    $variant?->status ? '1' : '0'
                                ) == '0'
                            )
                        >
                            Inactive
                        </option>

                    </select>

                </div>

            </div>


            {{-- PRICE SUMMARY --}}

            <div class="profit-preview">

                <div>

                    <span>FINAL SELLING PRICE</span>

                    <strong id="finalPricePreview">
                        $0.00
                    </strong>

                </div>


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
                Save Changes
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

.product-field-full {
    grid-column: 1 / -1;
}

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

.profit-preview {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
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

.product-save-button {
    border: none;
    background: #6F4E37;
    color: #FFFFFF;
}

@media (max-width: 700px) {

    .product-create-header {
        align-items: stretch;
        flex-direction: column;
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

    const cost =
        document.getElementById('cost_price');

    const selling =
        document.getElementById('selling_price');

    const discountType =
        document.getElementById('discount_type');

    const discount =
        document.getElementById('discount_value');

    const symbol =
        document.getElementById('discountSymbol');

    const finalPrice =
        document.getElementById('finalPricePreview');

    const profit =
        document.getElementById('profitPreview');

    const margin =
        document.getElementById('marginPreview');


    function calculate() {

        const costValue =
            parseFloat(cost.value) || 0;

        const sellingValue =
            parseFloat(selling.value) || 0;

        const discountValue =
            parseFloat(discount.value) || 0;


        let discountAmount = 0;


        if (
            discountType.value === 'percentage'
        ) {

            discountAmount =
                sellingValue *
                discountValue /
                100;

            symbol.textContent = '%';

        } else {

            discountAmount =
                discountValue;

            symbol.textContent = '$';

        }


        const finalValue =
            Math.max(
                0,
                sellingValue - discountAmount
            );


        const profitValue =
            finalValue - costValue;


        const marginValue =
            finalValue > 0
                ? profitValue /
                    finalValue *
                    100
                : 0;


        finalPrice.textContent =
            '$' + finalValue.toFixed(2);

        profit.textContent =
            '$' + profitValue.toFixed(2);

        margin.textContent =
            marginValue.toFixed(2) + '%';


        if (profitValue < 0) {

            profit.style.color =
                '#C85C4A';

            margin.style.color =
                '#C85C4A';

        } else {

            profit.style.color =
                '#5F8D5A';

            margin.style.color =
                '#5F8D5A';

        }

    }


    cost.addEventListener(
        'input',
        calculate
    );

    selling.addEventListener(
        'input',
        calculate
    );

    discount.addEventListener(
        'input',
        calculate
    );

    discountType.addEventListener(
        'change',
        calculate
    );


    calculate();

});

</script>

@endpush