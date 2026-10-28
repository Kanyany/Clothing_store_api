@extends('admin.layouts.app')

@section('title', 'Products')
@section('page-title', 'Products')

@section('content')
<div class="cp-page">

    <div class="cp-header">
        <div>
            <span class="cp-eyebrow">PRODUCT CATALOG</span>
            <h1>Products</h1>
            <p>Manage clothing, pants, bags, shoes and accessories in one clean workspace.</p>
        </div>

        <button class="cp-primary" type="button" onclick="openProductModal('add')">
            ＋ Add Product
        </button>
    </div>

    @if(session('success'))
        <div class="cp-alert success">
            <strong>✓</strong>
            <span>{{ session('success') }}</span>
            <button type="button" onclick="this.parentElement.remove()">×</button>
        </div>
    @endif

    @if(session('error'))
        <div class="cp-alert error">
            <strong>!</strong>
            <span>{{ session('error') }}</span>
            <button type="button" onclick="this.parentElement.remove()">×</button>
        </div>
    @endif

    @if($errors->any())
        <div class="cp-alert error">
            <strong>!</strong>
            <span>Please review the highlighted fields and try again.</span>
            <button type="button" onclick="this.parentElement.remove()">×</button>
        </div>
    @endif

    <div class="cp-stats">
        <div>
            <span>Products</span>
            <strong>{{ number_format($totalProducts) }}</strong>
        </div>
        <div>
            <span>Active</span>
            <strong>{{ number_format($activeProducts) }}</strong>
        </div>
        <div>
            <span>Inactive</span>
            <strong>{{ number_format($inactiveProducts) }}</strong>
        </div>
        <div>
            <span>Variants</span>
            <strong>{{ number_format($totalVariants) }}</strong>
        </div>
    </div>

    <div class="cp-filter">
        <form method="GET" action="{{ route('admin.products.index') }}" id="productFilterForm">

            <div class="cp-search">
                <span aria-hidden="true">⌕</span>
                <input
                    type="search"
                    name="search"
                    aria-label="Search products by any keyword"
                    value="{{ request('search') }}"
                    placeholder="Search anything: product, SKU, barcode, category, color, size, price, stock..."
                >
            </div>

            <select name="category_id" aria-label="Filter by category" onchange="submitProductFilters()">
                <option value="">All Categories</option>

                @foreach($categories as $category)
                    <option
                        value="{{ $category->id }}"
                        @selected((string) request('category_id') === (string) $category->id)
                    >
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>

            <select name="status" aria-label="Filter by status" onchange="submitProductFilters()">
                <option value="">All Status</option>
                <option value="active" @selected(request('status') === 'active')>
                    Active
                </option>
                <option value="inactive" @selected(request('status') === 'inactive')>
                    Inactive
                </option>
            </select>

            <select name="sort" aria-label="Sort products" onchange="submitProductFilters()">
                <option value="latest" @selected(request('sort', 'latest') === 'latest')>
                    Latest
                </option>
                <option value="name" @selected(request('sort') === 'name')>
                    Name A–Z
                </option>
                <option value="oldest" @selected(request('sort') === 'oldest')>
                    Oldest
                </option>
            </select>

            <button class="cp-filter-btn" type="submit">
                Search
            </button>

            @if(request()->hasAny(['search', 'category_id', 'status', 'sort']))
                <a href="{{ route('admin.products.index') }}">
                    Reset
                </a>
            @endif

        </form>
    </div>

    <div class="cp-card">

        <div class="cp-card-head">
            <div>
                <h2>Product List</h2>
                <p>
                    {{ number_format($products->total()) }}
                    {{ $products->total() === 1 ? 'product' : 'products' }}
                    found
                </p>
            </div>

            <div class="cp-card-tools">
                <div class="cp-view-toggle" aria-label="Product view">
                    <button
                        type="button"
                        id="listBtn"
                        title="List view"
                        aria-label="List view"
                        onclick="setProductView('list')"
                    >
                        <span class="view-icon list-icon"></span>
                    </button>

                    <button
                        type="button"
                        id="gridBtn"
                        class="active"
                        title="Grid view"
                        aria-label="Grid view"
                        onclick="setProductView('grid')"
                    >
                        <span class="view-icon grid-icon"></span>
                    </button>
                </div>

                <label class="cp-sort-control">
                    <span class="sort-icon">☷</span>
                    <span class="sort-label">Sort by</span>
                    <select
                        id="quickSort"
                        aria-label="Sort products"
                        onchange="changeProductSort(this.value)"
                    >
                        <option value="latest" @selected(request('sort', 'latest') === 'latest')>
                            Latest
                        </option>
                        <option value="name" @selected(request('sort') === 'name')>
                            Name A–Z
                        </option>
                        <option value="oldest" @selected(request('sort') === 'oldest')>
                            Oldest
                        </option>
                    </select>
                    <span class="sort-chevron">⌄</span>
                </label>
            </div>
        </div>

        @if($products->count())

            <div id="listView" class="cp-table-wrap" hidden>
                <table class="cp-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Colors / Sizes</th>
                            <th>Variants</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($products as $product)

                            @php
                                $stock = $product->variants->sum(
                                    fn($v) => (int) ($v->inventory?->quantity ?? 0)
                                );

                                $first = $product->variants->first();
                                $colors = $product->variants->pluck('color')->filter(fn($v) => filled($v))->unique()->values();
                                $sizes = $product->variants->pluck('size')->filter(fn($v) => filled($v))->unique()->values();
                            @endphp

                            <tr
                                data-product-id="{{ $product->id }}"
                                data-product-name="{{ $product->name }}"
                            >
                                <td>
                                    <div class="cp-product">
                                        <div class="cp-thumb">
                                            @if($product->image)
                                                <img
                                                    src="{{ \Illuminate\Support\Facades\Storage::url($product->image) }}"
                                                    alt="{{ $product->name }}"
                                                >
                                            @else
                                                {{ strtoupper(substr($product->name, 0, 1)) }}
                                            @endif
                                        </div>

                                        <div>
                                            <strong>{{ $product->name }}</strong>
                                            <span>SKU {{ $first?->sku ?? '—' }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <span class="cp-chip">
                                        {{ $product->category?->name ?? 'Uncategorized' }}
                                    </span>
                                </td>

                                <td>
                                    <strong>
                                        ${{ $first?->selling_price !== null
                                            ? number_format((float) $first->selling_price, 2)
                                            : '0.00' }}
                                    </strong>
                                </td>

                                <td>
                                    <div class="cp-option-summary">
                                        <div>
                                            <span class="cp-option-label">Color</span>
                                            <span class="cp-option-values">
                                                {{ $colors->isNotEmpty() ? $colors->implode(', ') : '—' }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="cp-option-label">Size</span>
                                            <span class="cp-option-values">
                                                {{ $sizes->isNotEmpty() ? $sizes->implode(', ') : '—' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    {{ number_format($product->variants->count()) }}
                                </td>

                                <td>
                                    <span class="cp-stock {{ $stock <= 0 ? 'zero' : '' }}">
                                        {{ number_format($stock) }}
                                    </span>
                                </td>

                                <td>
                                    <span class="cp-status {{ $product->status ? 'on' : 'off' }}">
                                        ● {{ $product->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>

                                <td>
                                    <div class="cp-actions">
                                        <button
                                            type="button"
                                            onclick="openProductModal('view', {{ $product->id }})"
                                        >
                                            View
                                        </button>

                                        <button
                                            type="button"
                                            onclick="openProductModal('edit', {{ $product->id }})"
                                        >
                                            Edit
                                        </button>

                                        <button
                                            type="button"
                                            class="danger"
                                            onclick="openProductModal('delete', {{ $product->id }})"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>

                        @endforeach
                    </tbody>
                </table>
            </div>

            <div id="gridView" class="cp-grid">

                @foreach($products as $product)

                    @php
                        $stock = $product->variants->sum(
                            fn($v) => (int) ($v->inventory?->quantity ?? 0)
                        );

                        $first = $product->variants->first();
                        $colors = $product->variants->pluck('color')->filter(fn($v) => filled($v))->unique()->values();
                        $sizes = $product->variants->pluck('size')->filter(fn($v) => filled($v))->unique()->values();
                    @endphp

                    <article class="cp-grid-card">

                        <div class="cp-grid-image">
                            @if($product->image)
                                <img
                                    src="{{ \Illuminate\Support\Facades\Storage::url($product->image) }}"
                                    alt="{{ $product->name }}"
                                >
                            @else
                                <span>{{ strtoupper(substr($product->name, 0, 1)) }}</span>
                            @endif
                        </div>

                        <div class="cp-grid-title">
                            <div>
                                <h3>{{ $product->name }}</h3>
                                <span>
                                    {{ $product->category?->name ?? 'Uncategorized' }}
                                </span>
                            </div>

                            <span class="cp-status {{ $product->status ? 'on' : 'off' }}">
                                ●
                            </span>
                        </div>

                        <div class="cp-grid-meta">
                            <strong>
                                ${{ $first?->selling_price !== null
                                    ? number_format((float) $first->selling_price, 2)
                                    : '0.00' }}
                            </strong>

                            <span class="cp-grid-options">
                                <b>Color:</b> {{ $colors->isNotEmpty() ? $colors->implode(', ') : '—' }}
                                <br>
                                <b>Size:</b> {{ $sizes->isNotEmpty() ? $sizes->implode(', ') : '—' }}
                            </span>

                            <span>
                                {{ number_format($product->variants->count()) }} variants
                            </span>

                            <span>
                                {{ number_format($stock) }} stock
                            </span>
                        </div>

                        <div class="cp-grid-actions">
                            <button
                                type="button"
                                onclick="openProductModal('view', {{ $product->id }})"
                            >
                                View
                            </button>

                            <button
                                type="button"
                                onclick="openProductModal('edit', {{ $product->id }})"
                            >
                                Edit
                            </button>

                            <button
                                type="button"
                                class="danger"
                                onclick="openProductModal('delete', {{ $product->id }})"
                            >
                                Delete
                            </button>
                        </div>

                    </article>

                @endforeach

            </div>

            <div class="cp-pagination">
                <span>
                    Showing {{ $products->firstItem() }}–{{ $products->lastItem() }}
                    of {{ $products->total() }}
                </span>

                <div>
                    @if(!$products->onFirstPage())
                        <a href="{{ $products->previousPageUrl() }}">‹</a>
                    @endif

                    <b>{{ $products->currentPage() }}</b>

                    @if($products->hasMorePages())
                        <a href="{{ $products->nextPageUrl() }}">›</a>
                    @endif
                </div>
            </div>

        @else

            <div class="cp-empty">
                <div>◫</div>
                <h3>No products found</h3>

                <p>
                    {{ request()->hasAny(['search', 'category_id', 'status'])
                        ? 'Try changing your filters.'
                        : 'There are no products in the database yet.' }}
                </p>

                <button
                    class="cp-primary"
                    type="button"
                    onclick="openProductModal('add')"
                >
                    ＋ Add Product
                </button>
            </div>

        @endif

    </div>
</div>

<div
    class="cp-modal-backdrop"
    id="productModalBackdrop"
    onclick="backdropClose(event)"
>
    <div class="cp-modal" id="addModal">

        <div class="cp-modal-head">
            <div>
                <span>PRODUCT SETUP</span>
                <h2>Add Product</h2>
                <p>
                    Select the real category first, then add only the variants this product needs.
                </p>
            </div>

            <button type="button" onclick="closeProductModal()">×</button>
        </div>

        <form
            method="POST"
            action="{{ route('admin.products.store') }}"
            enctype="multipart/form-data"
            id="addProductForm"
        >
            @csrf

            <div class="cp-modal-body">

                @if($errors->any() && old('name'))
                    <div class="cp-form-error">
                        {{ $errors->first() }}
                    </div>
                @endif

                <section>
                    <div class="cp-step">
                        <b>01</b>

                        <div>
                            <h3>Basic information</h3>
                            <p>
                                Choose the department, category and subcategory from your existing categories.
                            </p>
                        </div>
                    </div>

                    <div class="cp-form-grid">

                        <label class="full">
                            Product name *
                            <input
                                name="name"
                                value="{{ old('name') }}"
                                required
                                placeholder="e.g. Classic Cotton T-Shirt"
                            >
                        </label>

                        <div class="category-flow full">

                            <div>
                                <label>Department *</label>
                                <select id="add-root">
                                    <option value="">Choose department</option>
                                </select>
                            </div>

                            <div>
                                <label>Category</label>
                                <select id="add-level2" disabled>
                                    <option value="">Choose category</option>
                                </select>
                            </div>

                            <div>
                                <label>Subcategory</label>
                                <select id="add-level3" disabled>
                                    <option value="">Choose subcategory</option>
                                </select>
                            </div>

                            <input
                                type="hidden"
                                name="category_id"
                                id="add-category-id"
                                value="{{ old('category_id') }}"
                            >
                        </div>

                        <div
                            class="category-hint full"
                            id="categoryHint"
                        >
                            Select a category to see the recommended variant setup.
                        </div>

                        <label>
                            Gender
                            <select name="gender">
                                <option value="">Select</option>

                                @foreach([
                                    'male' => 'Men',
                                    'female' => 'Women',
                                    'unisex' => 'Unisex',
                                    'kids' => 'Kids',
                                ] as $value => $label)
                                    <option value="{{ $value }}">
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label>
                            Status
                            <select name="status">
                                <option value="1">Active</option>
                                <option
                                    value="0"
                                    @selected(old('status') === '0')
                                >
                                    Inactive
                                </option>
                            </select>
                        </label>

                        <label class="full">
                            Description
                            <textarea
                                name="description"
                                rows="3"
                                placeholder="Short product description"
                            >{{ old('description') }}</textarea>
                        </label>

                    </div>
                </section>

                <section>
                    <div class="cp-step">
                        <b>02</b>
                        <div>
                            <h3>Collections</h3>
                            <p>Select the collections where this product should appear.</p>
                        </div>
                    </div>

                    <div class="cp-collection-list">
                        @forelse($collections as $collection)
                            <label class="cp-collection-option">
                                <input
                                    type="checkbox"
                                    name="collection_ids[]"
                                    value="{{ $collection->id }}"
                                    @checked(in_array((string) $collection->id, array_map('strval', (array) old('collection_ids', [])), true))
                                >
                                <span class="cp-collection-option-content">
                                    <strong>{{ $collection->name }}</strong>
                                    <small>{{ $collection->status ? 'Active collection' : 'Inactive collection' }}</small>
                                </span>
                                <span class="cp-collection-check" aria-hidden="true">✓</span>
                            </label>
                        @empty
                            <p class="cp-collection-empty">No collections available. Create a collection first.</p>
                        @endforelse
                    </div>
                </section>

                <section>
                    <div class="cp-step">
                        <b>03</b>

                        <div>
                            <h3>Product image</h3>
                            <p>
                                Upload any image dimensions. The preview keeps the full picture visible.
                            </p>
                        </div>
                    </div>

                    <div class="image-upload">

                        <div
                            class="image-preview"
                            id="addImagePreview"
                        >
                            <span>No image</span>
                        </div>

                        <div class="image-upload-control">
                            <input
                                type="file"
                                name="image"
                                id="addImage"
                                accept="image/jpeg,image/png,image/webp"
                                onchange="previewImageInput(this, 'addImagePreview')"
                            >

                            <small>
                                JPG, JPEG, PNG or WebP · any dimensions · max 2 MB
                            </small>

                            <div id="addImageMessage"></div>
                        </div>

                    </div>
                </section>

                <section>
                    <div class="cp-step">
                        <b>04</b>
                        <div>
                            <h3>Color images</h3>
                            <p>When a product has multiple colors, upload one image for each color. The same color image is shared by all sizes of that color.</p>
                        </div>
                    </div>

                    <div id="addColorImages" class="color-image-list">
                        <div class="color-image-empty">Add colors below to upload an image for each color.</div>
                    </div>
                </section>

                <section>
                    <div class="cp-step">
                        <b>05</b>
                        <div>
                            <h3>Product detail images</h3>
                            <p>Add as many detail images as you need. These images are shown as a swipeable gallery in the Flutter Product Detail screen.</p>
                        </div>
                    </div>

                    <div class="detail-image-manager">
                        <div id="addDetailImages" class="detail-image-preview-grid"></div>
                        <div id="addDetailImageInputs" class="detail-image-inputs"></div>
                        <button type="button" class="add-detail-image-button" onclick="addDetailImage('add')">
                            ＋ Add Image
                        </button>
                    </div>
                </section>

                <section>
                    <div class="cp-step">
                        <b>06</b>

                        <div>
                            <h3>Default pricing</h3>
                            <p>
                                These values are copied to every generated variant.
                            </p>
                        </div>
                    </div>

                    <div class="cp-form-grid">

                        <label>
                            Cost price *
                            <input
                                type="number"
                                id="baseCost"
                                min="0"
                                step="0.01"
                                value="0"
                            >
                        </label>

                        <label>
                            Selling price *
                            <input
                                type="number"
                                id="baseSelling"
                                min="0"
                                step="0.01"
                                value="0"
                            >
                        </label>

                        <label>
                            Discount type
                            <select id="baseDiscountType">
                                <option value="percentage">
                                    Percentage (%)
                                </option>
                                <option value="fixed">
                                    Fixed ($)
                                </option>
                            </select>
                        </label>

                        <label>
                            Discount
                            <input
                                type="number"
                                id="baseDiscount"
                                min="0"
                                step="0.01"
                                value="0"
                            >
                        </label>

                    </div>
                </section>

                <section>
                    <div class="cp-step">
                        <b>07</b>

                        <div>
                            <h3>Product options</h3>
                            <p>
                                Clothing and pants can use Color × Size. Shoes can use shoe size × Color.
                                Bags and accessories can use Color only or one standard variant.
                            </p>
                        </div>
                    </div>

                    <div class="variant-build">

                        <div class="option-panel">
                            <div class="build-head">
                                <div>
                                    <b>Colors</b>
                                    <small>Select the colors this product has.</small>
                                </div>
                                <button type="button" onclick="addCustomOption('colors')">
                                    ＋ New color
                                </button>
                            </div>

                            <div id="colorPresets" class="preset-chips color-presets"></div>
                            <div id="colorsList" class="selected-options"></div>
                        </div>

                        <div class="option-panel">
                            <div class="build-head">
                                <div>
                                    <b>Sizes</b>
                                    <small>Select the sizes this product has.</small>
                                </div>
                                <button type="button" onclick="addCustomOption('sizes')">
                                    ＋ Custom size
                                </button>
                            </div>

                            <div id="sizePresets" class="preset-chips size-presets"></div>
                            <div id="sizesList" class="selected-options"></div>
                        </div>

                    </div>

                    <div class="generate-row">
                        <span id="variantCount">0 variants</span>

                        <button
                            type="button"
                            class="cp-secondary"
                            onclick="generateVariants()"
                        >
                            Generate variants
                        </button>
                    </div>

                    <div
                        class="variant-cards"
                        id="addVariantRows"
                    >
                        <p class="variant-empty">
                            Add a color, a size, or both. You do not need both for bags and accessories.
                        </p>
                    </div>
                </section>

            </div>

            <div class="cp-modal-foot">
                <button
                    type="button"
                    class="cp-secondary"
                    onclick="closeProductModal()"
                >
                    Cancel
                </button>

                <button
                    class="cp-primary"
                    type="submit"
                >
                    Save Product
                </button>
            </div>
        </form>
    </div>


    @foreach($products as $product)

        @php
            $editVariants = $product->variants->map(
                fn($v) => [
                    'id' => $v->id,
                    'sku' => $v->sku,
                    'barcode' => $v->barcode,
                    'size' => $v->size,
                    'color' => $v->color,
                    'cost_price' => (float) $v->cost_price,
                    'selling_price' => (float) $v->selling_price,
                    'discount_type' => $v->discount_type ?? 'percentage',
                    'discount_value' => (float) ($v->discount_value ?? 0),
                    'stock' => (int) ($v->inventory?->quantity ?? 0),
                    'low_stock_threshold' => (int) ($v->inventory?->low_stock_threshold ?? 0),
                    'status' => (bool) $v->status,
                ]
            )->values();
        @endphp

        <div
            class="cp-modal"
            id="viewModal-{{ $product->id }}"
        >
            <div class="cp-modal-head">
                <div>
                    <span>PRODUCT DETAILS</span>
                    <h2>{{ $product->name }}</h2>
                    <p>Live product and inventory data.</p>
                </div>

                <button type="button" onclick="closeProductModal()">×</button>
            </div>

            <div class="cp-modal-body">

                <div class="product-detail-hero">
                    <div class="detail-image">
                        @if($product->image)
                            <img
                                src="{{ \Illuminate\Support\Facades\Storage::url($product->image) }}"
                                alt="{{ $product->name }}"
                            >
                        @else
                            {{ strtoupper(substr($product->name, 0, 1)) }}
                        @endif
                    </div>

                    <div>
                        <h3>{{ $product->name }}</h3>
                        <p>{{ $product->category?->name ?? 'Uncategorized' }}</p>

                        <span class="cp-status {{ $product->status ? 'on' : 'off' }}">
                            ● {{ $product->status ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>

                <div class="detail-grid">
                    <div>
                        <small>Gender</small>
                        <b>{{ $product->gender ?: '—' }}</b>
                    </div>

                    <div>
                        <small>Variants</small>
                        <b>{{ $product->variants->count() }}</b>
                    </div>

                    <div>
                        <small>Total stock</small>
                        <b>
                            {{ number_format(
                                $product->variants->sum(
                                    fn($v) => (int) ($v->inventory?->quantity ?? 0)
                                )
                            ) }}
                        </b>
                    </div>

                    <div>
                        <small>Description</small>
                        <b>{{ $product->description ?: '—' }}</b>
                    </div>
                </div>

                <h3 class="detail-title">
                    Color × Size inventory
                </h3>

                <div class="detail-variant-list">
                    @forelse($product->variants as $v)
                        <div>
                            <strong>
                                {{ $v->color ?: 'No Color' }}
                                @if($v->size)
                                    · {{ $v->size }}
                                @endif
                            </strong>

                            <span>SKU {{ $v->sku }}</span>

                            <span>
                                {{ number_format((int) ($v->inventory?->quantity ?? 0)) }}
                                in stock
                            </span>

                            <b>
                                ${{ number_format((float) $v->selling_price, 2) }}
                            </b>
                        </div>
                    @empty
                        <div class="cp-empty-inline">
                            No variants.
                        </div>
                    @endforelse
                </div>

            </div>

            <div class="cp-modal-foot">
                <button
                    type="button"
                    class="cp-secondary"
                    onclick="closeProductModal()"
                >
                    Close
                </button>

                <button
                    type="button"
                    class="cp-danger-outline"
                    onclick="openProductModal('delete', {{ $product->id }})"
                >
                    Delete
                </button>

                <button
                    type="button"
                    class="cp-primary"
                    onclick="openProductModal('edit', {{ $product->id }})"
                >
                    Edit Product
                </button>
            </div>
        </div>

        <div
            class="cp-modal"
            id="editModal-{{ $product->id }}"
        >
            <div class="cp-modal-head">
                <div>
                    <span>PRODUCT CATALOG</span>
                    <h2>Edit Product</h2>
                    <p>Update product details, variants and stock.</p>
                </div>

                <button type="button" onclick="closeProductModal()">×</button>
            </div>

            <form
                method="POST"
                action="{{ route('admin.products.update', $product) }}"
                enctype="multipart/form-data"
            >
                @csrf
                @method('PUT')

                <div class="cp-modal-body">

                    <div class="cp-form-grid">

                        <label class="full">
                            Product name *
                            <input
                                name="name"
                                value="{{ $product->name }}"
                                required
                            >
                        </label>

                        <div
                            class="category-flow full"
                            data-edit-category="{{ $product->category_id }}"
                        >
                            <div>
                                <label>Department *</label>
                                <select class="edit-root">
                                    <option value="">Choose department</option>
                                </select>
                            </div>

                            <div>
                                <label>Category</label>
                                <select class="edit-level2" disabled>
                                    <option value="">Choose category</option>
                                </select>
                            </div>

                            <div>
                                <label>Subcategory</label>
                                <select class="edit-level3" disabled>
                                    <option value="">Choose subcategory</option>
                                </select>
                            </div>

                            <input
                                type="hidden"
                                name="category_id"
                                class="edit-category-id"
                                value="{{ $product->category_id }}"
                            >
                        </div>

                        <label>
                            Gender
                            <select name="gender">
                                <option value="">Select</option>

                                @foreach(['Men', 'Women', 'Unisex', 'Kids'] as $gender)
                                    <option
                                        value="{{ $gender }}"
                                        @selected($product->gender === $gender)
                                    >
                                        {{ $gender }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label>
                            Status
                            <select name="status">
                                <option
                                    value="1"
                                    @selected($product->status)
                                >
                                    Active
                                </option>

                                <option
                                    value="0"
                                    @selected(!$product->status)
                                >
                                    Inactive
                                </option>
                            </select>
                        </label>

                        <div class="full cp-edit-collections-section">
                            <div class="cp-step">
                                <b>02</b>
                                <div>
                                    <h3>Collections</h3>
                                    <p>Choose the collections assigned to this product.</p>
                                </div>
                            </div>

                            <div class="cp-collection-list">
                                @forelse($collections as $collection)
                                    <label class="cp-collection-option">
                                        <input
                                            type="checkbox"
                                            name="collection_ids[]"
                                            value="{{ $collection->id }}"
                                            @checked($product->collections->contains('id', $collection->id))
                                        >
                                        <span class="cp-collection-option-content">
                                            <strong>{{ $collection->name }}</strong>
                                            <small>{{ $collection->status ? 'Active collection' : 'Inactive collection' }}</small>
                                        </span>
                                        <span class="cp-collection-check" aria-hidden="true">✓</span>
                                    </label>
                                @empty
                                    <p class="cp-collection-empty">No collections available. Create a collection first.</p>
                                @endforelse
                            </div>
                        </div>

                        <label class="full">
                            Product image

                            <div class="image-upload">
                                <div
                                    class="image-preview"
                                    id="editImagePreview-{{ $product->id }}"
                                >
                                    @if($product->image)
                                        <img
                                            src="{{ \Illuminate\Support\Facades\Storage::url($product->image) }}"
                                            alt="{{ $product->name }}"
                                        >
                                    @else
                                        <span>No image</span>
                                    @endif
                                </div>

                                <div class="image-upload-control">
                                    <input
                                        type="file"
                                        name="image"
                                        accept="image/jpeg,image/png,image/webp"
                                        onchange="previewImageInput(this, 'editImagePreview-{{ $product->id }}')"
                                    >

                                    <small>
                                        JPG, JPEG, PNG or WebP · any dimensions · max 2 MB ·
                                        leave empty to keep the current image
                                    </small>
                                </div>
                            </div>
                        </label>

                        <div class="full edit-color-image-section">
                            <div class="edit-section-title">
                                <strong>Color images</strong>
                                <span>Upload one image per color. Existing images stay unchanged unless you replace them.</span>
                            </div>
                            <div id="editColorImages-{{ $product->id }}" class="color-image-list"></div>
                        </div>

                        <div class="full edit-detail-image-section">
                            <div class="edit-section-title">
                                <strong>Product detail images</strong>
                                <span>Current images stay unchanged. Use + Add Image to add more gallery images.</span>
                            </div>
                            <div id="editDetailImages-{{ $product->id }}" class="detail-image-preview-grid"></div>
                            <div id="editDetailImageInputs-{{ $product->id }}" class="detail-image-inputs"></div>
                            <button type="button" class="add-detail-image-button" onclick="addDetailImage('edit', {{ $product->id }})">
                                ＋ Add Image
                            </button>
                        </div>

                        <label class="full">
                            Description
                            <textarea
                                name="description"
                                rows="3"
                            >{{ $product->description }}</textarea>
                        </label>

                    </div>

                    <h3 class="detail-title">
                        Variants & stock
                    </h3>

                    <div
                        class="variant-cards"
                        id="editRows-{{ $product->id }}"
                    ></div>

                    <button
                        type="button"
                        class="cp-secondary add-edit"
                        onclick="addEditRow({{ $product->id }})"
                    >
                        ＋ Add Variant
                    </button>

                </div>

                <div class="cp-modal-foot">
                    <button
                        type="button"
                        class="cp-secondary"
                        onclick="closeProductModal()"
                    >
                        Cancel
                    </button>

                    <button
                        class="cp-primary"
                        type="submit"
                    >
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

    @endforeach

    <div class="cp-modal cp-delete" id="deleteModal">
        <div class="delete-mark">!</div>

        <h2>Delete product?</h2>

        <p id="deleteText">
            This action permanently removes the product and its unused variants.
        </p>

        <form id="deleteForm" method="POST">
            @csrf
            @method('DELETE')

            <div class="cp-modal-foot">
                <button
                    type="button"
                    class="cp-secondary"
                    onclick="closeProductModal()"
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    class="cp-danger"
                >
                    Delete Product
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('styles')
<style>
    :root {
        --cp-dark: #111827;
        --cp-brown: #6F4E37;
        --cp-brown-dark: #573A29;
        --cp-warm: #9A6A4A;
        --cp-cream: #F6EEE5;
        --cp-bg: #FAF8F5;
        --cp-muted: #5F6875;
        --cp-border: #E5DED7;
        --cp-green: #16A34A;
        --cp-green-bg: #ECFDF3;
        --cp-red: #DC2626;
        --cp-red-bg: #FEF2F2;
        --cp-white: #FFFFFF;
    }

    * {
        box-sizing: border-box;
    }

    .cp-page {
        max-width: 1500px;
        margin: 0 auto;
        padding: 28px 30px 48px;
        color: var(--cp-dark);
        font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont,
            "Segoe UI", sans-serif;
    }

    .cp-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 24px;
        margin-bottom: 20px;
    }

    .cp-eyebrow {
        display: inline-block;
        color: var(--cp-brown);
        font-size: 12px;
        font-weight: 900;
        letter-spacing: 1.8px;
    }

    .cp-header h1 {
        margin: 6px 0 5px;
        color: var(--cp-dark);
        font-size: 32px;
        line-height: 1.15;
        font-weight: 900;
    }

    .cp-header p {
        margin: 0;
        color: var(--cp-muted);
        font-size: 14px;
        line-height: 1.5;
    }

    .cp-primary {
        border: 0;
        border-radius: 10px;
        background: var(--cp-brown);
        color: #FFFFFF;
        padding: 12px 18px;
        font-size: 14px;
        font-weight: 800;
        line-height: 1;
        cursor: pointer;
        transition: background .15s ease, transform .15s ease;
    }

    .cp-primary:hover {
        background: var(--cp-brown-dark);
        transform: translateY(-1px);
    }

    .cp-alert {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 15px;
        padding: 13px 15px;
        border-radius: 10px;
        font-size: 14px;
        line-height: 1.4;
    }

    .cp-alert button {
        margin-left: auto;
        border: 0;
        background: transparent;
        color: inherit;
        font-size: 20px;
        line-height: 1;
        cursor: pointer;
    }

    .cp-alert.success {
        border: 1px solid #BBE7C7;
        background: var(--cp-green-bg);
        color: var(--cp-green);
    }

    .cp-alert.error {
        border: 1px solid #F5C2C2;
        background: var(--cp-red-bg);
        color: var(--cp-red);
    }

    .cp-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 13px;
        margin-bottom: 15px;
    }

    .cp-stats > div,
    .cp-filter,
    .cp-card {
        border: 1px solid var(--cp-border);
        border-radius: 15px;
        background: var(--cp-white);
    }

    .cp-stats > div {
        padding: 17px;
    }

    .cp-stats span {
        display: block;
        color: var(--cp-muted);
        font-size: 13px;
        font-weight: 600;
    }

    .cp-stats strong {
        display: block;
        margin-top: 5px;
        color: var(--cp-dark);
        font-size: 28px;
        line-height: 1.15;
        font-weight: 900;
    }

    .cp-filter {
        margin-bottom: 15px;
        padding: 14px;
    }

    .cp-filter form {
        display: grid;
        grid-template-columns: minmax(260px, 1.7fr) repeat(3, 150px) auto auto;
        gap: 9px;
    }

    .cp-search {
        display: flex;
        align-items: center;
        gap: 9px;
        min-height: 43px;
        padding: 0 12px;
        border: 1px solid var(--cp-border);
        border-radius: 9px;
        background: var(--cp-bg);
    }

    .cp-search span {
        color: var(--cp-brown);
        font-size: 20px;
        font-weight: 700;
    }

    .cp-search input {
        width: 100%;
        border: 0;
        outline: 0;
        background: transparent;
        color: var(--cp-dark);
        font: inherit;
        font-size: 14px;
    }

    .cp-filter select,
    .cp-filter-btn,
    .cp-filter a {
        min-height: 43px;
        border: 1px solid var(--cp-border);
        border-radius: 9px;
        background: var(--cp-bg);
        padding: 0 12px;
        color: var(--cp-dark);
        font-size: 13px;
    }

    .cp-filter-btn {
        border-color: var(--cp-brown);
        background: var(--cp-brown);
        color: #FFFFFF;
        font-weight: 800;
        cursor: pointer;
    }

    .cp-filter a {
        display: grid;
        place-items: center;
        padding: 0 15px;
        color: var(--cp-brown);
        text-decoration: none;
        font-weight: 700;
    }

    .cp-card {
        overflow: hidden;
    }

    .cp-card-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        padding: 18px 20px;
        border-bottom: 1px solid var(--cp-border);
    }

    .cp-card-head h2 {
        margin: 0;
        color: var(--cp-dark);
        font-size: 19px;
        line-height: 1.25;
        font-weight: 900;
    }

    .cp-card-head p {
        margin: 4px 0 0;
        color: var(--cp-muted);
        font-size: 13px;
    }

    .cp-card-tools {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .cp-view-toggle {
        display: flex;
        align-items: center;
        gap: 2px;
        padding: 3px;
        border: 1px solid #E5E7EB;
        border-radius: 9px;
        background: #FFFFFF;
        height: 38px;
    }

    .cp-view-toggle button {
        width: 32px;
        height: 30px;
        display: grid;
        place-items: center;
        border: 0;
        border-radius: 6px;
        background: transparent;
        color: #6B7280;
        cursor: pointer;
        transition: background .15s ease, color .15s ease;
    }

    .cp-view-toggle button:hover {
        background: #F3F4F6;
        color: #111827;
    }

    .cp-view-toggle button.active {
        background: #F3F4F6;
        color: #111827;
    }

    .view-icon {
        position: relative;
        display: block;
        width: 16px;
        height: 16px;
    }

    .list-icon::before,
    .list-icon::after {
        content: "";
        position: absolute;
        left: 4px;
        right: 0;
        height: 2px;
        border-radius: 2px;
        background: currentColor;
        box-shadow: 0 5px 0 currentColor, 0 10px 0 currentColor;
    }

    .list-icon::before {
        top: 0;
    }

    .list-icon::after {
        left: 0;
        width: 2px;
        height: 2px;
        right: auto;
        top: 0;
        box-shadow: 0 5px 0 currentColor, 0 10px 0 currentColor;
    }

    .grid-icon::before {
        content: "";
        position: absolute;
        inset: 1px;
        background:
            linear-gradient(currentColor 0 0) 0 0 / 6px 6px no-repeat,
            linear-gradient(currentColor 0 0) 100% 0 / 6px 6px no-repeat,
            linear-gradient(currentColor 0 0) 0 100% / 6px 6px no-repeat,
            linear-gradient(currentColor 0 0) 100% 100% / 6px 6px no-repeat;
    }

    .cp-sort-control {
        position: relative;
        height: 38px;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 0 10px;
        border: 1px solid #E5E7EB;
        border-radius: 9px;
        background: #FFFFFF;
        color: #111827;
        cursor: pointer;
    }

    .sort-icon {
        color: #111827;
        font-size: 15px;
        line-height: 1;
        font-weight: 800;
    }

    .sort-label {
        color: #111827;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .cp-sort-control select {
        appearance: none;
        border: 0;
        outline: 0;
        background: transparent;
        color: #111827;
        font: inherit;
        font-size: 12px;
        font-weight: 700;
        padding: 0 17px 0 0;
        cursor: pointer;
    }

    .sort-chevron {
        position: absolute;
        right: 9px;
        pointer-events: none;
        color: #6B7280;
        font-size: 14px;
        line-height: 1;
    }

    .cp-table {
        width: 100%;
        border-collapse: collapse;
    }

    .cp-table th {
        padding: 12px 15px;
        background: var(--cp-cream);
        color: var(--cp-brown);
        text-align: left;
        font-size: 12px;
        font-weight: 900;
    }

    .cp-table td {
        padding: 14px 15px;
        border-bottom: 1px solid #F0EAE5;
        color: var(--cp-dark);
        font-size: 13px;
        vertical-align: middle;
    }

    .cp-product {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .cp-thumb {
        width: 58px;
        height: 58px;
        flex: 0 0 58px;
        display: grid;
        place-items: center;
        overflow: hidden;
        border: 1px solid var(--cp-border);
        border-radius: 10px;
        background: var(--cp-cream);
        color: var(--cp-brown);
        font-size: 18px;
        font-weight: 900;
    }

    .cp-thumb img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .cp-product strong {
        display: block;
        color: var(--cp-dark);
        font-size: 14px;
        line-height: 1.3;
        font-weight: 800;
    }

    .cp-product span {
        display: block;
        margin-top: 4px;
        color: var(--cp-muted);
        font-size: 12px;
    }

    .cp-option-summary {
        display: flex;
        flex-direction: column;
        gap: 5px;
        min-width: 150px;
        max-width: 260px;
    }

    .cp-option-summary > div {
        display: flex;
        gap: 6px;
        align-items: flex-start;
    }

    .cp-option-label {
        flex: 0 0 44px;
        color: var(--cp-brown);
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .cp-option-values {
        color: var(--cp-dark);
        font-size: 12px;
        line-height: 1.35;
    }

    .cp-chip {
        display: inline-flex;
        padding: 6px 9px;
        border-radius: 7px;
        background: var(--cp-cream);
        color: var(--cp-brown);
        font-size: 12px;
        font-weight: 800;
    }

    .cp-stock {
        display: inline-flex;
        padding: 6px 9px;
        border-radius: 7px;
        background: var(--cp-green-bg);
        color: var(--cp-green);
        font-size: 12px;
        font-weight: 900;
    }

    .cp-stock.zero {
        background: var(--cp-red-bg);
        color: var(--cp-red);
    }

    .cp-status {
        font-size: 13px;
        font-weight: 900;
    }

    .cp-status.on {
        color: var(--cp-green);
    }

    .cp-status.off {
        color: var(--cp-red);
    }

    .cp-actions,
    .cp-grid-options {
        flex: 1 1 100%;
        line-height: 1.55;
        color: var(--cp-dark);
    }

    .cp-grid-options b {
        color: var(--cp-brown);
        font-weight: 900;
    }

    .cp-grid-actions {
        display: flex;
        gap: 7px;
    }

    .cp-actions button,
    .cp-grid-actions button {
        border: 1px solid var(--cp-border);
        border-radius: 8px;
        background: #FFFFFF;
        color: var(--cp-brown);
        padding: 7px 10px;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
    }

    .cp-actions button:hover,
    .cp-grid-actions button:hover {
        border-color: var(--cp-brown);
    }

    .cp-actions .danger,
    .cp-grid-actions .danger {
        border-color: #F1B7B7;
        color: var(--cp-red);
    }

    .cp-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        padding: 18px;
    }

    .cp-grid-card {
        overflow: hidden;
        border: 1px solid var(--cp-border);
        border-radius: 14px;
        background: #FFFFFF;
    }

    .cp-grid-image {
        height: 230px;
        display: grid;
        place-items: center;
        overflow: hidden;
        background: #F7F3EF;
        color: var(--cp-brown);
        font-size: 42px;
        font-weight: 900;
    }

    .cp-grid-image img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .cp-grid-title {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        padding: 14px 15px 0;
    }

    .cp-grid-title h3 {
        margin: 0;
        color: var(--cp-dark);
        font-size: 17px;
        line-height: 1.3;
        font-weight: 900;
    }

    .cp-grid-title span:not(.cp-status) {
        display: block;
        margin-top: 5px;
        color: var(--cp-muted);
        font-size: 13px;
    }

    .cp-grid-meta {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        padding: 13px 15px;
        color: var(--cp-muted);
        font-size: 12px;
    }

    .cp-grid-meta strong {
        color: var(--cp-dark);
        font-size: 14px;
        font-weight: 900;
    }

    .cp-grid-actions {
        padding: 0 15px 15px;
    }

    .cp-grid-actions button {
        flex: 1;
    }

    .cp-pagination {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        padding: 14px 18px;
        color: var(--cp-muted);
        font-size: 12px;
    }

    .cp-pagination a,
    .cp-pagination b {
        display: inline-grid;
        place-items: center;
        width: 31px;
        height: 31px;
        margin-left: 4px;
        border: 1px solid var(--cp-border);
        border-radius: 7px;
        color: var(--cp-brown);
        text-decoration: none;
    }

    .cp-pagination b {
        border-color: var(--cp-brown);
        background: var(--cp-brown);
        color: #FFFFFF;
    }

    .cp-empty {
        padding: 70px 20px;
        text-align: center;
    }

    .cp-empty > div {
        width: 54px;
        height: 54px;
        display: grid;
        place-items: center;
        margin: 0 auto;
        border-radius: 14px;
        background: var(--cp-cream);
        color: var(--cp-brown);
        font-size: 22px;
        font-weight: 900;
    }

    .cp-empty h3 {
        margin: 14px 0 5px;
        font-size: 19px;
    }

    .cp-empty p {
        margin: 0 0 18px;
        color: var(--cp-muted);
        font-size: 13px;
    }

    .cp-modal-backdrop {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 18px;
        background: rgba(17, 24, 39, .64);
        backdrop-filter: blur(5px);
    }

    .cp-modal-backdrop.show {
        display: flex;
    }

    .cp-modal {
        display: none;
        width: min(1120px, 100%);
        max-height: calc(100vh - 36px);
        overflow: auto;
        border-radius: 17px;
        background: #FFFFFF;
        box-shadow: 0 25px 80px rgba(17, 24, 39, .28);
    }

    .cp-modal.show {
        display: block;
    }

    .cp-modal-head {
        position: sticky;
        top: 0;
        z-index: 2;
        display: flex;
        justify-content: space-between;
        gap: 20px;
        padding: 20px 22px;
        border-bottom: 1px solid var(--cp-border);
        background: #FFFFFF;
    }

    .cp-modal-head span {
        color: var(--cp-brown);
        font-size: 11px;
        font-weight: 900;
        letter-spacing: 1.4px;
    }

    .cp-modal-head h2 {
        margin: 5px 0;
        color: var(--cp-dark);
        font-size: 24px;
        line-height: 1.2;
        font-weight: 900;
    }

    .cp-modal-head p {
        margin: 0;
        color: var(--cp-muted);
        font-size: 13px;
        line-height: 1.45;
    }

    .cp-modal-head > button {
        width: 36px;
        height: 36px;
        flex: 0 0 36px;
        border: 1px solid var(--cp-border);
        border-radius: 8px;
        background: var(--cp-bg);
        color: var(--cp-dark);
        font-size: 22px;
        line-height: 1;
        cursor: pointer;
    }

    .cp-modal-body {
        padding: 20px 22px;
    }

    .cp-modal-body section {
        margin-bottom: 15px;
        padding: 17px;
        border: 1px solid var(--cp-border);
        border-radius: 12px;
        background: #FFFFFF;
    }

    .cp-collection-list {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-top: 16px;
    }

    .cp-collection-option {
        position: relative;
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
        padding: 14px;
        border: 1px solid #E6D9CB;
        border-radius: 10px;
        background: #FFFFFF;
        cursor: pointer;
        transition: border-color .18s ease, background .18s ease;
    }

    .cp-collection-option:has(input:checked) {
        border-color: #6F4E37;
        background: #FAF6F1;
    }

    .cp-collection-option input {
        width: 17px;
        height: 17px;
        flex: 0 0 17px;
        accent-color: #6F4E37;
        cursor: pointer;
    }

    .cp-collection-option-content {
        display: flex;
        flex: 1;
        flex-direction: column;
        gap: 4px;
        min-width: 0;
    }

    .cp-collection-option-content strong {
        overflow-wrap: anywhere;
        color: #302820;
        font-size: 13px;
        font-weight: 700;
    }

    .cp-collection-option-content small {
        color: #8B7B6D;
        font-size: 11px;
    }

    .cp-collection-check {
        display: none;
        color: #6F4E37;
        font-weight: 800;
    }

    .cp-collection-option:has(input:checked) .cp-collection-check {
        display: inline;
    }

    .cp-collection-empty {
        grid-column: 1 / -1;
        margin: 0;
        padding: 16px;
        border: 1px dashed #D9C8B7;
        border-radius: 9px;
        color: #827367;
        font-size: 12px;
    }

    @media (max-width: 640px) {
        .cp-collection-list {
            grid-template-columns: 1fr;
        }
    }

    .cp-step {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 15px;
    }

    .cp-step > b {
        width: 32px;
        height: 32px;
        flex: 0 0 32px;
        display: grid;
        place-items: center;
        border-radius: 8px;
        background: var(--cp-cream);
        color: var(--cp-brown);
        font-size: 12px;
        font-weight: 900;
    }

    .cp-step h3 {
        margin: 0;
        color: var(--cp-dark);
        font-size: 16px;
        line-height: 1.3;
        font-weight: 900;
    }

    .cp-step p {
        margin: 4px 0 0;
        color: var(--cp-muted);
        font-size: 12px;
        line-height: 1.5;
    }

    .cp-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .cp-form-grid label,
    .category-flow label {
        color: var(--cp-brown);
        font-size: 12px;
        font-weight: 900;
    }

    .cp-form-grid label.full {
        grid-column: 1 / -1;
    }

    .cp-form-grid input,
    .cp-form-grid select,
    .cp-form-grid textarea,
    .category-flow select {
        display: block;
        width: 100%;
        margin-top: 6px;
        border: 1px solid #D5D9E0;
        border-radius: 8px;
        background: #FFFFFF;
        padding: 10px 11px;
        color: var(--cp-dark);
        font: inherit;
        font-size: 13px;
        line-height: 1.4;
        outline: 0;
    }

    .cp-form-grid input:focus,
    .cp-form-grid select:focus,
    .cp-form-grid textarea:focus,
    .category-flow select:focus {
        border-color: var(--cp-brown);
        box-shadow: 0 0 0 3px rgba(111, 78, 55, .10);
    }

    .category-flow {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .category-flow > div {
        min-width: 0;
    }

    .category-flow select:disabled {
        color: #8A929E;
        background: #F3F4F6;
        cursor: not-allowed;
    }

    .category-hint {
        padding: 11px 12px;
        border-left: 3px solid var(--cp-brown);
        border-radius: 7px;
        background: var(--cp-cream);
        color: var(--cp-dark);
        font-size: 12px;
        line-height: 1.5;
    }

    .image-upload {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .image-preview {
        width: 240px;
        height: 135px;
        flex: 0 0 240px;
        display: grid;
        place-items: center;
        overflow: hidden;
        border: 1px solid var(--cp-border);
        border-radius: 10px;
        background: #F7F3EF;
        color: var(--cp-muted);
        font-size: 13px;
        font-weight: 700;
    }

    .image-preview img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .image-upload-control {
        min-width: 0;
    }

    .image-upload input {
        max-width: 100%;
        font-size: 13px;
    }

    .image-upload small {
        display: block;
        margin-top: 7px;
        color: var(--cp-muted);
        font-size: 12px;
        line-height: 1.5;
    }

    #addImageMessage {
        margin-top: 6px;
        font-size: 12px;
        font-weight: 800;
    }

    .variant-build {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 13px;
    }

    .variant-build > div {
        padding: 13px;
        border: 1px solid var(--cp-border);
        border-radius: 10px;
        background: var(--cp-bg);
    }

    .build-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        margin-bottom: 9px;
        color: var(--cp-dark);
        font-size: 13px;
    }

    .build-head button,
    .cp-secondary {
        border: 1px solid #D1D5DB;
        border-radius: 8px;
        background: #FFFFFF;
        color: var(--cp-dark);
        padding: 8px 11px;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
    }

    .build-head button:hover,
    .cp-secondary:hover {
        border-color: var(--cp-brown);
        color: var(--cp-brown);
    }

    .option-row {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 7px;
        margin-bottom: 7px;
    }

    .option-row input {
        width: 100%;
        border: 1px solid #D5D9E0;
        border-radius: 7px;
        padding: 9px;
        color: var(--cp-dark);
        font-size: 13px;
        outline: 0;
    }

    .option-row button {
        width: 35px;
        border: 0;
        background: transparent;
        color: var(--cp-red);
        font-size: 18px;
        cursor: pointer;
    }

    .option-panel {
        min-width: 0;
    }

    .build-head > div {
        min-width: 0;
    }

    .build-head small {
        display: block;
        margin-top: 3px;
        color: var(--cp-muted);
        font-size: 11px;
        font-weight: 600;
    }

    .preset-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
        margin-bottom: 10px;
    }

    .preset-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 1px solid #D5D9E0;
        border-radius: 999px;
        background: #fff;
        color: var(--cp-dark);
        padding: 7px 10px;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
        transition: .15s ease;
    }

    .preset-chip:hover {
        border-color: var(--cp-brown);
        color: var(--cp-brown);
    }

    .preset-chip.selected {
        border-color: var(--cp-brown);
        background: #F7F1EC;
        color: var(--cp-brown);
    }

    .color-dot {
        width: 15px;
        height: 15px;
        border-radius: 50%;
        border: 1px solid rgba(17,24,39,.22);
        flex: 0 0 15px;
    }

    .selected-options {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
    }

    .selected-option {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 8px 6px 9px;
        border: 1px solid var(--cp-border);
        border-radius: 999px;
        background: #fff;
        font-size: 12px;
        font-weight: 800;
    }

    .selected-option button {
        width: 18px;
        height: 18px;
        padding: 0;
        border: 0;
        border-radius: 50%;
        background: transparent;
        color: var(--cp-red);
        font-size: 16px;
        line-height: 18px;
        cursor: pointer;
    }


    .generate-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin: 14px 0 9px;
        color: var(--cp-muted);
        font-size: 13px;
        font-weight: 700;
    }

    .variant-cards {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .variant-empty {
        margin: 0;
        padding: 22px;
        border: 1px dashed var(--cp-border);
        border-radius: 10px;
        background: var(--cp-bg);
        color: var(--cp-muted);
        text-align: center;
        font-size: 13px;
        line-height: 1.5;
    }

    .variant-card {
        overflow: hidden;
        border: 1px solid var(--cp-border);
        border-radius: 11px;
        background: var(--cp-bg);
    }

    .variant-card-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        padding: 11px 13px;
        background: var(--cp-cream);
        color: var(--cp-brown);
        font-size: 13px;
        font-weight: 900;
    }

    .variant-card-head button {
        border: 0;
        background: transparent;
        color: var(--cp-red);
        font-size: 19px;
        line-height: 1;
        cursor: pointer;
    }

    .variant-card-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        padding: 13px;
    }

    .variant-card-grid .cell {
        min-width: 0;
    }

    .variant-card-grid .cell.wide {
        grid-column: span 2;
    }

    .variant-card-grid label {
        display: block;
        margin-bottom: 5px;
        color: var(--cp-brown);
        font-size: 11px;
        font-weight: 900;
    }

    .variant-card-grid input,
    .variant-card-grid select {
        width: 100%;
        border: 1px solid #D5D9E0;
        border-radius: 7px;
        background: #FFFFFF;
        padding: 9px;
        color: var(--cp-dark);
        font-size: 13px;
        outline: 0;
    }

    .variant-card-grid input:focus,
    .variant-card-grid select:focus {
        border-color: var(--cp-brown);
        box-shadow: 0 0 0 3px rgba(111, 78, 55, .08);
    }

    .variant-discount-pair {
        display: grid;
        grid-template-columns: 70px 1fr;
        gap: 6px;
    }

    .cp-modal-foot {
        position: sticky;
        bottom: 0;
        z-index: 2;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 8px;
        padding: 14px 22px;
        border-top: 1px solid var(--cp-border);
        background: #FFFFFF;
    }

    .cp-danger,
    .cp-danger-outline {
        border: 1px solid #F1B7B7;
        border-radius: 8px;
        padding: 10px 15px;
        color: var(--cp-red);
        font-size: 13px;
        font-weight: 900;
        cursor: pointer;
    }

    .cp-danger {
        border-color: var(--cp-red);
        background: var(--cp-red);
        color: #FFFFFF;
    }

    .cp-danger-outline {
        background: #FFFFFF;
    }

    .cp-form-error {
        margin-bottom: 13px;
        padding: 11px 12px;
        border: 1px solid #F5C2C2;
        border-radius: 8px;
        background: var(--cp-red-bg);
        color: var(--cp-red);
        font-size: 13px;
        font-weight: 700;
    }

    .product-detail-hero {
        display: flex;
        align-items: center;
        gap: 16px;
        padding-bottom: 17px;
    }

    .detail-image {
        width: 180px;
        height: 100px;
        flex: 0 0 180px;
        display: grid;
        place-items: center;
        overflow: hidden;
        border: 1px solid var(--cp-border);
        border-radius: 10px;
        background: #F7F3EF;
        color: var(--cp-brown);
        font-size: 24px;
        font-weight: 900;
    }

    .detail-image img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .product-detail-hero h3 {
        margin: 0;
        color: var(--cp-dark);
        font-size: 21px;
        font-weight: 900;
    }

    .product-detail-hero p {
        margin: 5px 0 8px;
        color: var(--cp-muted);
        font-size: 13px;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
    }

    .detail-grid > div {
        min-width: 0;
        padding: 12px;
        border: 1px solid var(--cp-border);
        border-radius: 9px;
    }

    .detail-grid small {
        display: block;
        color: var(--cp-muted);
        font-size: 11px;
        font-weight: 700;
    }

    .detail-grid b {
        display: block;
        margin-top: 5px;
        color: var(--cp-dark);
        font-size: 13px;
        line-height: 1.45;
    }

    .detail-title {
        margin: 19px 0 9px;
        color: var(--cp-dark);
        font-size: 16px;
        font-weight: 900;
    }

    .detail-variant-list {
        overflow: hidden;
        border: 1px solid var(--cp-border);
        border-radius: 9px;
    }

    .detail-variant-list > div {
        display: grid;
        grid-template-columns: 1.5fr 1fr 1fr auto;
        gap: 10px;
        align-items: center;
        padding: 12px;
        border-bottom: 1px solid #F0EAE5;
        color: var(--cp-dark);
        font-size: 12px;
    }

    .detail-variant-list > div:last-child {
        border-bottom: 0;
    }

    .detail-variant-list span {
        color: var(--cp-muted);
    }

    .cp-empty-inline {
        display: block !important;
        padding: 24px !important;
        color: var(--cp-muted) !important;
        text-align: center;
    }

    .cp-delete {
        width: min(430px, 100%);
        padding: 30px;
        text-align: center;
    }

    .cp-delete .cp-modal-foot {
        position: static;
        margin: 22px -30px -30px;
    }

    .delete-mark {
        width: 54px;
        height: 54px;
        display: grid;
        place-items: center;
        margin: 0 auto;
        border-radius: 50%;
        background: var(--cp-red-bg);
        color: var(--cp-red);
        font-size: 22px;
        font-weight: 900;
    }

    .cp-delete h2 {
        margin: 14px 0 6px;
        color: var(--cp-dark);
        font-size: 21px;
    }

    .cp-delete p {
        color: var(--cp-muted);
        font-size: 13px;
        line-height: 1.6;
    }

    .add-edit {
        margin-top: 10px;
    }

    .color-image-list {
        display: grid;
        gap: 10px;
    }

    .color-image-empty {
        padding: 14px;
        border: 1px dashed var(--cp-border);
        border-radius: 10px;
        background: var(--cp-bg);
        color: var(--cp-muted);
        font-size: 12px;
    }

    .color-image-row {
        display: grid;
        grid-template-columns: 92px minmax(0, 1fr);
        gap: 13px;
        align-items: center;
        padding: 12px;
        border: 1px solid var(--cp-border);
        border-radius: 11px;
        background: #fff;
    }

    .color-image-preview {
        width: 92px;
        height: 92px;
        display: grid;
        place-items: center;
        overflow: hidden;
        border: 1px solid var(--cp-border);
        border-radius: 9px;
        background: #F7F3EF;
        color: var(--cp-muted);
        font-size: 11px;
        font-weight: 800;
    }

    .color-image-preview img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .color-image-info {
        min-width: 0;
    }

    .color-image-info strong {
        display: block;
        color: var(--cp-dark);
        font-size: 14px;
        font-weight: 900;
    }

    .color-image-info small {
        display: block;
        margin: 4px 0 8px;
        color: var(--cp-muted);
        font-size: 11px;
        line-height: 1.4;
    }

    .color-image-info input[type="file"] {
        width: 100%;
        font-size: 12px;
    }

    .detail-image-manager {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .detail-image-preview-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
        gap: 10px;
    }

    .detail-image-card {
        position: relative;
        aspect-ratio: 1 / 1;
        overflow: hidden;
        border: 1px solid var(--cp-border);
        border-radius: 10px;
        background: #F7F3EF;
    }

    .detail-image-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .detail-image-number {
        position: absolute;
        left: 7px;
        bottom: 7px;
        min-width: 24px;
        padding: 3px 6px;
        border-radius: 6px;
        background: rgba(0,0,0,.62);
        color: #fff;
        font-size: 10px;
        font-weight: 800;
        text-align: center;
    }

    .add-detail-image-button {
        align-self: flex-start;
        min-height: 40px;
        padding: 0 15px;
        border: 1px dashed var(--cp-primary);
        border-radius: 9px;
        background: #fff;
        color: var(--cp-primary);
        font-size: 12px;
        font-weight: 900;
        cursor: pointer;
    }

    .add-detail-image-button:hover {
        background: #FAF6F1;
    }

    .detail-image-inputs {
        display: none;
    }

    .edit-detail-image-section {
        padding-top: 4px;
    }

    .edit-section-title {
        display: flex;
        flex-direction: column;
        gap: 3px;
        margin-bottom: 10px;
    }

    .edit-section-title strong {
        color: var(--cp-dark);
        font-size: 15px;
        font-weight: 900;
    }

    .edit-section-title span {
        color: var(--cp-muted);
        font-size: 12px;
    }

    @media (max-width: 700px) {
        .cp-card-head {
            align-items: flex-start;
        }

        .cp-card-tools {
            width: 100%;
            justify-content: space-between;
        }

        .cp-sort-control {
            flex: 1;
            justify-content: center;
        }
    }

    @media (max-width: 1100px) {
        .cp-filter form {
            grid-template-columns: 1fr 1fr;
        }

        .cp-stats {
            grid-template-columns: repeat(2, 1fr);
        }

        .cp-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .variant-card-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .variant-card-grid .cell.wide {
            grid-column: span 2;
        }
    }

    @media (max-width: 760px) {
        .cp-page {
            padding: 18px 14px 35px;
        }

        .cp-header {
            align-items: stretch;
            flex-direction: column;
        }

        .cp-primary {
            width: 100%;
        }

        .cp-filter form,
        .cp-stats,
        .cp-form-grid,
        .category-flow,
        .variant-build,
        .detail-grid {
            grid-template-columns: 1fr;
        }

        .cp-form-grid label.full {
            grid-column: auto;
        }

        .cp-grid {
            grid-template-columns: 1fr;
            padding: 12px;
        }

        .cp-table-wrap {
            overflow-x: auto;
        }

        .cp-table {
            min-width: 900px;
        }

        .cp-modal-body {
            padding: 14px;
        }

        .image-upload {
            align-items: flex-start;
            flex-direction: column;
        }

        .image-preview {
            width: 100%;
            max-width: 320px;
            height: 180px;
            flex-basis: auto;
        }

        .variant-card-grid {
            grid-template-columns: 1fr;
        }

        .variant-card-grid .cell.wide {
            grid-column: auto;
        }

        .detail-variant-list > div {
            grid-template-columns: 1fr;
            gap: 4px;
        }

        .cp-modal-foot {
            padding: 12px 14px;
        }
    }
            /* =========================================================
        PRODUCT LIST / GRID
        NEVER SHOW BOTH AT THE SAME TIME
        ========================================================= */

        #listView[hidden],
        #gridView[hidden] {
            display: none !important;
        }

        #listView {
            display: none;
        }

        #gridView {
            display: grid;
        }

        #listView:not([hidden]) {
            display: block !important;
        }

        #gridView:not([hidden]) {
            display: grid !important;
        }
    </style>
@endpush

@push('scripts')
<script>
    // Auto-hide server success/error alerts after 5 seconds.
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.cp-alert').forEach(function (alert) {
            setTimeout(function () {
                if (alert && alert.parentElement) {
                    alert.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-4px)';
                    setTimeout(function () {
                        if (alert && alert.parentElement) alert.remove();
                    }, 300);
                }
            }, 5000);
        });
    });

    const categoryTree = @json($categoryTree ?? []);
    const oldCategory = @json(old('category_id'));

    let colors = [];
    let sizes = [];
    let addColorImageState = {};
    let detailImageInputCounter = 0;

    function flatten(nodes, parentId = null) {
        let result = [];

        nodes.forEach(node => {
            result.push({
                id: node.id,
                name: node.name,
                parent_id: parentId,
                children: node.children || []
            });

            result = result.concat(
                flatten(node.children || [], node.id)
            );
        });

        return result;
    }

    function findNode(id, nodes = categoryTree) {
        for (const node of nodes) {
            if (String(node.id) === String(id)) {
                return node;
            }

            const found = findNode(
                id,
                node.children || []
            );

            if (found) {
                return found;
            }
        }

        return null;
    }

    function childrenOf(parentId) {
        return flatten(categoryTree)
            .filter(item => String(item.parent_id) === String(parentId));
    }

    function fillSelect(select, items, placeholder) {
        if (!select) {
            return;
        }

        select.innerHTML =
            '<option value="">' +
            placeholder +
            '</option>' +
            items
                .map(item =>
                    `<option value="${item.id}">
                        ${escapeHtml(item.name)}
                    </option>`
                )
                .join('');

        select.disabled = items.length === 0;
    }

    function setupCategoryFlow(root, level2, level3, hidden, initial = '') {
        if (!root || !level2 || !level3 || !hidden) {
            return;
        }

        fillSelect(
            root,
            categoryTree,
            'Choose department'
        );

        root.addEventListener('change', () => {
            fillSelect(
                level2,
                childrenOf(root.value),
                'Choose category'
            );

            fillSelect(
                level3,
                [],
                'Choose subcategory'
            );

            hidden.value = root.value || '';

            updateCategoryHint();
        });

        level2.addEventListener('change', () => {
            fillSelect(
                level3,
                childrenOf(level2.value),
                'Choose subcategory'
            );

            hidden.value =
                level2.value ||
                root.value ||
                '';

            updateCategoryHint();
        });

        level3.addEventListener('change', () => {
            hidden.value =
                level3.value ||
                level2.value ||
                root.value ||
                '';

            updateCategoryHint();
        });

        if (initial) {
            const node = findNode(initial);

            if (node) {
                const chain = [];
                let current = node;

                while (current) {
                    chain.unshift(current);
                    current = findNode(current.parent_id);
                }

                if (chain.length) {
                    root.value = chain[0].id;

                    fillSelect(
                        level2,
                        childrenOf(root.value),
                        'Choose category'
                    );

                    if (chain[1]) {
                        level2.value = chain[1].id;
                    }

                    fillSelect(
                        level3,
                        childrenOf(level2.value),
                        'Choose subcategory'
                    );

                    if (chain[2]) {
                        level3.value = chain[2].id;
                    }

                    hidden.value = initial;
                }
            }
        }
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(
            /[&<>'"]/g,
            character => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                "'": '&#039;',
                '"': '&quot;'
            }[character])
        );
    }

    function openProductModal(type, id = null) {
        const backdrop = document.getElementById(
            'productModalBackdrop'
        );

        document
            .querySelectorAll('.cp-modal')
            .forEach(modal => modal.classList.remove('show'));

        let modal;

        if (type === 'add') {
            modal = document.getElementById('addModal');
        } else if (type === 'delete') {
            modal = document.getElementById('deleteModal');
        } else {
            modal = document.getElementById(
                `${type}Modal-${id}`
            );
        }

        if (!modal) {
            return;
        }

        if (type === 'delete') {
            const row = document.querySelector(
                `[data-product-id="${id}"]`
            );

            const productName =
                row?.dataset?.productName ||
                'this product';

            document.getElementById('deleteText').textContent =
                `Are you sure you want to delete “${productName}”? This cannot be undone.`;

            document.getElementById('deleteForm').action =
                `{{ url('/admin/products') }}/${id}`;
        }

        modal.classList.add('show');

        if (backdrop) {
            backdrop.classList.add('show');
        }

        document.body.style.overflow = 'hidden';

        if (type === 'edit') {
            renderEditRows(id);
        }

        if (type === 'add') {
            colors = [];
            sizes = [];
            addColorImageState = {};
            resetDetailImages('add');
            renderOptions();
            updateCategoryHint();
        }
    }

    function closeProductModal() {
        document
            .getElementById('productModalBackdrop')
            ?.classList.remove('show');

        document
            .querySelectorAll('.cp-modal')
            .forEach(modal => modal.classList.remove('show'));

        document.body.style.overflow = '';
    }

    function backdropClose(event) {
        if (event.target.id === 'productModalBackdrop') {
            closeProductModal();
        }
    }

    function submitProductFilters() {
        const form = document.getElementById('productFilterForm');
        if (!form) return;

        const pageInput = form.querySelector('input[name="page"]');
        if (pageInput) pageInput.remove();

        form.submit();
    }

    function setProductView(view) {
        const listView = document.getElementById('listView');
        const gridView = document.getElementById('gridView');

        const listBtn = document.getElementById('listBtn');
        const gridBtn = document.getElementById('gridBtn');

        if (!listView || !gridView) {
            return;
        }

        if (view === 'list') {
            listView.hidden = false;
            gridView.hidden = true;

            listBtn?.classList.add('active');
            gridBtn?.classList.remove('active');
        } else {
            listView.hidden = true;
            gridView.hidden = false;

            listBtn?.classList.remove('active');
            gridBtn?.classList.add('active');
        }
    }

    function changeProductSort(value) {
        const url = new URL(window.location.href);

        if (value) {
            url.searchParams.set('sort', value);
        } else {
            url.searchParams.delete('sort');
        }

        url.searchParams.delete('page');
        window.location.href = url.toString();
    }

    const COLOR_PRESETS = [
        { name: 'Black', hex: '#111111' },
        { name: 'White', hex: '#FFFFFF' },
        { name: 'Gray', hex: '#9CA3AF' },
        { name: 'Cream', hex: '#F5E6C8' },
        { name: 'Beige', hex: '#D6C2A3' },
        { name: 'Brown', hex: '#6F4E37' },
        { name: 'Navy', hex: '#1E3A5F' },
        { name: 'Blue', hex: '#3B82F6' },
        { name: 'Green', hex: '#22C55E' },
        { name: 'Olive', hex: '#808000' },
        { name: 'Red', hex: '#EF4444' },
        { name: 'Pink', hex: '#EC4899' },
        { name: 'Purple', hex: '#8B5CF6' },
        { name: 'Yellow', hex: '#FACC15' },
        { name: 'Orange', hex: '#F97316' },
    ];

    const SIZE_PRESETS = [
        'XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL',
        'One Size'
    ];

    function isSelected(type, value) {
        const key = normalizeColor(value);
        const list = type === 'colors' ? colors : sizes;
        return list.some(item => normalizeColor(item) === key);
    }

    function togglePreset(type, value) {
        const list = type === 'colors' ? colors : sizes;
        const key = normalizeColor(value);
        const index = list.findIndex(item => normalizeColor(item) === key);

        if (index >= 0) {
            if (type === 'colors') {
                colors.splice(index, 1);
                syncAddColorImagesAfterNameChange();
            } else {
                sizes.splice(index, 1);
            }
        } else {
            list.push(value);
        }

        renderOptions();
    }

    function addCustomOption(type) {
        const label = type === 'colors' ? 'Enter new color:' : 'Enter new size:';
        const value = window.prompt(label);
        if (!value || !value.trim()) return;

        const cleaned = value.trim();
        const list = type === 'colors' ? colors : sizes;
        const exists = list.some(item => normalizeColor(item) === normalizeColor(cleaned));

        if (exists) {
            toast(`${cleaned} is already selected.`, 'error');
            return;
        }

        list.push(cleaned);
        renderOptions();
        if (type === 'colors') syncAddColorImagesAfterNameChange();
    }

    function removeOption(type, index) {
        if (type === 'colors') {
            colors.splice(index, 1);
            syncAddColorImagesAfterNameChange();
        } else {
            sizes.splice(index, 1);
        }
        renderOptions();
    }

    function renderPresetButtons() {
        const colorPresets = document.getElementById('colorPresets');
        const sizePresets = document.getElementById('sizePresets');

        if (colorPresets) {
            colorPresets.innerHTML = COLOR_PRESETS.map(color => `
                <button
                    type="button"
                    class="preset-chip ${isSelected('colors', color.name) ? 'selected' : ''}"
                    onclick="togglePreset('colors', '${escapeHtml(color.name)}')"
                >
                    <span class="color-dot" style="background:${color.hex}"></span>
                    ${escapeHtml(color.name)}
                </button>
            `).join('');
        }

        if (sizePresets) {
            const uniqueSizes = [...new Set(SIZE_PRESETS)];
            sizePresets.innerHTML = uniqueSizes.map(size => `
                <button
                    type="button"
                    class="preset-chip ${isSelected('sizes', size) ? 'selected' : ''}"
                    onclick="togglePreset('sizes', '${escapeHtml(size)}')"
                >
                    ${escapeHtml(size)}
                </button>
            `).join('');
        }
    }

    function renderOptions() {
        const colorsList = document.getElementById('colorsList');
        const sizesList = document.getElementById('sizesList');

        renderPresetButtons();

        if (colorsList) {
            colorsList.innerHTML = colors.map((value, index) => `
                <span class="selected-option">
                    <span class="color-dot" style="background:${colorHex(value)}"></span>
                    ${escapeHtml(value)}
                    <button type="button" onclick="removeOption('colors', ${index})" aria-label="Remove color">×</button>
                </span>
            `).join('');
        }

        if (sizesList) {
            sizesList.innerHTML = sizes.map((value, index) => `
                <span class="selected-option">
                    ${escapeHtml(value)}
                    <button type="button" onclick="removeOption('sizes', ${index})" aria-label="Remove size">×</button>
                </span>
            `).join('');
        }

        renderColorImageInputs('add');
    }

    function colorHex(value) {
        const found = COLOR_PRESETS.find(color => normalizeColor(color.name) === normalizeColor(value));
        return found?.hex || '#D1D5DB';
    }

    function normalizeColor(value) {
        return String(value || '').trim().toLowerCase().replace(/\s+/g, ' ');
    }

    function renderColorImageInputs(mode, productId = null) {
        const container = mode === 'add'
            ? document.getElementById('addColorImages')
            : document.getElementById(`editColorImages-${productId}`);

        if (!container) return;

        const names = unique(colors).filter(Boolean);
        if (!names.length) {
            container.innerHTML = '<div class="color-image-empty">Add colors to upload color-specific images.</div>';
            return;
        }

        const existing = mode === 'edit' ? (colorImageData[productId] || {}) : {};

        container.innerHTML = names.map((color, index) => {
            const key = normalizeColor(color);
            const current = existing[key] || '';
            const state = mode === 'add' ? (addColorImageState[key] || {}) : {};
            const preview = state.preview || (current ? `{{ url('/storage') }}/${current}` : '');

            return `
                <div class="color-image-row" data-color-image-key="${escapeHtml(key)}">
                    <div class="color-image-preview" id="${mode}ColorPreview-${productId || 'new'}-${index}">
                        ${preview ? `<img src="${escapeHtml(preview)}" alt="${escapeHtml(color)}">` : '<span>Image</span>'}
                    </div>
                    <div class="color-image-info">
                        <strong>${escapeHtml(color)}</strong>
                        <small>${current ? 'Current image — choose a new file to replace it.' : 'Upload the image used when this color is selected.'}</small>
                        <input type="hidden" name="${mode === 'add' ? 'color_image_colors' : 'edit_color_image_colors'}[]" value="${escapeHtml(color)}">
                        <input type="file" name="${mode === 'add' ? 'color_images' : 'edit_color_images'}[]" accept="image/jpeg,image/png,image/webp" onchange="previewColorImageInput(this, '${mode}', '${productId || ''}', '${index}', '${escapeHtml(key)}')">
                    </div>
                </div>
            `;
        }).join('');

        if (mode === 'add') {
            container.querySelectorAll('input[type="file"]').forEach(input => {
                const key = normalizeColor(input.closest('.color-image-row')?.dataset?.colorImageKey);
                const file = addColorImageState[key]?.file;
                if (!file) return;
                try {
                    const transfer = new DataTransfer();
                    transfer.items.add(file);
                    input.files = transfer.files;
                } catch (error) {
                    // If the browser blocks programmatic file restoration, the preview remains valid.
                }
            });
        }
    }

    function previewColorImageInput(input, mode, productId, index, colorKey) {
        const file = input.files?.[0];
        if (!file) return;

        const allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!allowed.includes(file.type)) {
            toast('Please choose a JPG, JPEG, PNG or WebP image.', 'error');
            input.value = '';
            return;
        }
        if (file.size > 2 * 1024 * 1024) {
            toast('Color image must be 2 MB or smaller.', 'error');
            input.value = '';
            return;
        }

        const url = URL.createObjectURL(file);
        if (mode === 'add') {
            addColorImageState[colorKey] = { preview: url, file: file };
        }

        const preview = document.getElementById(`${mode}ColorPreview-${productId || 'new'}-${index}`);
        if (preview) preview.innerHTML = `<img src="${url}" alt="Color image preview">`;
    }

    function syncAddColorImagesAfterNameChange() {
        const next = {};
        colors.forEach(color => {
            const key = normalizeColor(color);
            if (key && addColorImageState[key]) next[key] = addColorImageState[key];
        });
        addColorImageState = next;
        renderColorImageInputs('add');
    }

    function unique(values) {
        return [
            ...new Map(
                values
                    .map(value => [
                        value.trim().toLowerCase(),
                        value.trim()
                    ])
                    .filter(([key]) => key)
            ).values()
        ];
    }

    function slug(value) {
        return value
            .toUpperCase()
            .replace(/[^A-Z0-9]+/g, '-')
            .replace(/^-|-$/g, '')
            .slice(0, 24) || 'PRODUCT';
    }

    function selectedCategoryName() {
        const hidden =
            document.getElementById('add-category-id');

        if (!hidden?.value) {
            return '';
        }

        return findNode(hidden.value)?.name || '';
    }

    function updateCategoryHint() {
        const hint =
            document.getElementById('categoryHint');

        if (!hint) {
            return;
        }

        const name =
            selectedCategoryName().toLowerCase();

        if (!name) {
            hint.textContent =
                'Select a category to see the recommended variant setup.';
            return;
        }

        if (name.includes('shoe')) {
            hint.textContent =
                'Shoes: use shoe size and color when your products have both options. You can also use only size or only color.';
            return;
        }

        if (name.includes('pant')) {
            hint.textContent =
                'Pants: use waist/size and color when needed. Example: Black × 32, Black × 34.';
            return;
        }

        if (name.includes('bag')) {
            hint.textContent =
                'Bags: size is optional. Use color only if each color is tracked separately.';
            return;
        }

        if (name.includes('accessor')) {
            hint.textContent =
                'Accessories: use color or size only when it matters. A single standard variant is also supported.';
            return;
        }

        hint.textContent =
            'This category can use color, size, or both. Add only the options that this product actually sells.';
    }

    function variantCardHtml(
        namePrefix,
        index,
        variant,
        removable
    ) {
        const discountSelect = `
            <select
                name="${namePrefix}[${index}][discount_type]"
            >
                <option
                    value="percentage"
                    ${variant.discount_type === 'percentage' ? 'selected' : ''}
                >
                    %
                </option>

                <option
                    value="fixed"
                    ${variant.discount_type === 'fixed' ? 'selected' : ''}
                >
                    $
                </option>
            </select>
        `;

        const optionName =
            [variant.color, variant.size]
                .filter(Boolean)
                .join(' · ') ||
            'Standard variant';

        return `
            <div class="variant-card">

                <div class="variant-card-head">
                    <span>
                        ${escapeHtml(optionName)}
                    </span>

                    ${
                        removable
                            ? `
                                <button
                                    type="button"
                                    onclick="this.closest('.variant-card').remove()"
                                    title="Remove variant"
                                    aria-label="Remove variant"
                                >
                                    ×
                                </button>
                            `
                            : ''
                    }
                </div>

                <div class="variant-card-grid">

                    ${
                        variant.id !== undefined
                            ? `
                                <input
                                    type="hidden"
                                    name="${namePrefix}[${index}][id]"
                                    value="${variant.id || ''}"
                                >
                            `
                            : ''
                    }

                    <div class="cell">
                        <label>Color</label>

                        <input
                            name="${namePrefix}[${index}][color]"
                            value="${escapeHtml(variant.color || '')}"
                            placeholder="e.g. Black"
                            ${variant.lockColorSize ? 'readonly' : ''}
                        >
                    </div>

                    <div class="cell">
                        <label>Size</label>

                        <input
                            name="${namePrefix}[${index}][size]"
                            value="${escapeHtml(variant.size || '')}"
                            placeholder="e.g. M / 32 / 38"
                            ${variant.lockColorSize ? 'readonly' : ''}
                        >
                    </div>

                    <div class="cell wide">
                        <label>SKU *</label>

                        <input
                            name="${namePrefix}[${index}][sku]"
                            value="${escapeHtml(variant.sku || '')}"
                            required
                        >
                    </div>

                    <div class="cell wide">
                        <label>Barcode</label>

                        <input
                            name="${namePrefix}[${index}][barcode]"
                            value="${escapeHtml(variant.barcode || '')}"
                            placeholder="Optional"
                        >
                    </div>

                    <div class="cell">
                        <label>Cost price *</label>

                        <input
                            type="number"
                            name="${namePrefix}[${index}][cost_price]"
                            value="${variant.cost_price ?? 0}"
                            min="0"
                            step="0.01"
                            required
                        >
                    </div>

                    <div class="cell">
                        <label>Selling price *</label>

                        <input
                            type="number"
                            name="${namePrefix}[${index}][selling_price]"
                            value="${variant.selling_price ?? 0}"
                            min="0"
                            step="0.01"
                            required
                        >
                    </div>

                    <div class="cell wide">
                        <label>Discount</label>

                        <div class="variant-discount-pair">
                            ${discountSelect}

                            <input
                                type="number"
                                name="${namePrefix}[${index}][discount_value]"
                                value="${variant.discount_value ?? 0}"
                                min="0"
                                step="0.01"
                                required
                            >
                        </div>
                    </div>

                    <div class="cell">
                        <label>Stock *</label>

                        <input
                            type="number"
                            name="${namePrefix}[${index}][stock]"
                            value="${variant.stock ?? 0}"
                            min="0"
                            required
                        >
                    </div>

                    <div class="cell">
                        <label>Low stock alert</label>

                        <input
                            type="number"
                            name="${namePrefix}[${index}][low_stock_threshold]"
                            value="${variant.low_stock_threshold ?? 0}"
                            min="0"
                            required
                        >
                    </div>

                    <div class="cell">
                        <label>Status</label>

                        <select
                            name="${namePrefix}[${index}][status]"
                        >
                            <option
                                value="1"
                                ${variant.status ? 'selected' : ''}
                            >
                                Active
                            </option>

                            <option
                                value="0"
                                ${!variant.status ? 'selected' : ''}
                            >
                                Inactive
                            </option>
                        </select>
                    </div>

                </div>
            </div>
        `;
    }

    function generateVariants() {
        colors = unique(colors);
        sizes = unique(sizes);

        if (!colors.length && !sizes.length) {
            createGeneratedVariants([
                {
                    color: '',
                    size: ''
                }
            ]);

            return;
        }

        const combinations = [];

        if (colors.length && sizes.length) {
            colors.forEach(color => {
                sizes.forEach(size => {
                    combinations.push({
                        color,
                        size
                    });
                });
            });
        } else if (colors.length) {
            colors.forEach(color => {
                combinations.push({
                    color,
                    size: ''
                });
            });
        } else {
            sizes.forEach(size => {
                combinations.push({
                    color: '',
                    size
                });
            });
        }

        createGeneratedVariants(combinations);
    }

    function createGeneratedVariants(combinations) {
        const form =
            document.getElementById('addProductForm');

        const baseSku =
            slug(
                form?.querySelector('[name="name"]')?.value ||
                'PRODUCT'
            );

        const cost =
            document.getElementById('baseCost')?.value || 0;

        const selling =
            document.getElementById('baseSelling')?.value || 0;

        const discountType =
            document.getElementById('baseDiscountType')?.value ||
            'percentage';

        const discountValue =
            document.getElementById('baseDiscount')?.value || 0;

        const variantCount =
            document.getElementById('variantCount');

        const variantRows =
            document.getElementById('addVariantRows');

        if (variantCount) {
            variantCount.textContent =
                `${combinations.length} ${
                    combinations.length === 1
                        ? 'variant'
                        : 'variants'
                }`;
        }

        if (!variantRows) {
            return;
        }

        variantRows.innerHTML = combinations
            .map(
                (combination, index) =>
                    variantCardHtml(
                        'variants',
                        index,
                        {
                            color: combination.color,
                            size: combination.size,
                            sku:
                                `${baseSku}-${String(index + 1).padStart(3, '0')}`,
                            barcode: '',
                            cost_price: cost,
                            selling_price: selling,
                            discount_type: discountType,
                            discount_value: discountValue,
                            stock: 0,
                            low_stock_threshold: 0,
                            status: true
                        },
                        true
                    )
            )
            .join('');
    }

    const editData = @json($editData ?? []);
    const colorImageData = @json($colorImageData ?? []);
    const detailImageData = @json($detailImageData ?? []);

    function renderEditRows(id) {
        const data = editData[id] || [];
        const body =
            document.getElementById(`editRows-${id}`);

        if (!body) {
            return;
        }

        body.innerHTML = data.length
            ? data
                .map(
                    (variant, index) =>
                        variantCardHtml(
                            'variants',
                            index,
                            {
                                ...variant,
                                id: variant.id
                            },
                            true
                        )
                )
                .join('')
            : `
                <p class="variant-empty">
                    No variants yet. Add one below.
                </p>
            `;

        colors = unique(data.map(v => v.color || '').filter(Boolean));
        renderColorImageInputs('edit', id);
        renderExistingDetailImages(id);
    }

    function addEditRow(id) {
        const body =
            document.getElementById(`editRows-${id}`);

        if (!body) {
            return;
        }

        const empty =
            body.querySelector('.variant-empty');

        if (empty) {
            body.innerHTML = '';
        }

        const index =
            body.querySelectorAll('.variant-card').length;

        body.insertAdjacentHTML(
            'beforeend',
            variantCardHtml(
                'variants',
                index,
                {
                    id: '',
                    sku: '',
                    barcode: '',
                    size: '',
                    color: '',
                    cost_price: 0,
                    selling_price: 0,
                    discount_type: 'percentage',
                    discount_value: 0,
                    stock: 0,
                    low_stock_threshold: 0,
                    status: true
                },
                true
            )
        );
    }

    function resetDetailImages(mode, productId = null) {
        const previewId = mode === 'add'
            ? 'addDetailImages'
            : `editDetailImages-${productId}`;
        const inputsId = mode === 'add'
            ? 'addDetailImageInputs'
            : `editDetailImageInputs-${productId}`;

        const preview = document.getElementById(previewId);
        const inputs = document.getElementById(inputsId);

        if (preview) preview.innerHTML = '';
        if (inputs) inputs.innerHTML = '';
    }

    function renderExistingDetailImages(productId) {
        const container = document.getElementById(`editDetailImages-${productId}`);
        if (!container) return;

        const images = Array.isArray(detailImageData[productId])
            ? detailImageData[productId]
            : [];

        container.innerHTML = images.map((path, index) => `
            <div class="detail-image-card">
                <img src="{{ url('/storage') }}/${escapeHtml(path)}" alt="Detail image ${index + 1}">
                <span class="detail-image-number">${index + 1}</span>
            </div>
        `).join('');
    }

    function addDetailImage(mode = 'add', productId = null) {
        const preview = document.getElementById(
            mode === 'add' ? 'addDetailImages' : `editDetailImages-${productId}`
        );
        const inputs = document.getElementById(
            mode === 'add' ? 'addDetailImageInputs' : `editDetailImageInputs-${productId}`
        );

        if (!preview || !inputs) return;

        const input = document.createElement('input');
        input.type = 'file';
        input.name = 'detail_images[]';
        input.accept = 'image/jpeg,image/png,image/webp';
        input.dataset.detailImageInput = String(++detailImageInputCounter);

        input.addEventListener('change', function () {
            const file = this.files?.[0];
            if (!file) return;

            const allowed = ['image/jpeg', 'image/png', 'image/webp'];
            if (!allowed.includes(file.type)) {
                toast('Please choose a JPG, JPEG, PNG or WebP image.', 'error');
                this.remove();
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                toast('Detail image must be 5 MB or smaller.', 'error');
                this.remove();
                return;
            }

            const url = URL.createObjectURL(file);
            const card = document.createElement('div');
            card.className = 'detail-image-card';
            card.innerHTML = `
                <img src="${escapeHtml(url)}" alt="Detail image">
                <span class="detail-image-number">New</span>
            `;
            preview.appendChild(card);
        });

        inputs.appendChild(input);
        input.click();
    }

    function toast(message, type = 'success') {
        const alert = document.createElement('div');

        alert.className =
            `cp-alert ${type}`;

        alert.style.position = 'fixed';
        alert.style.right = '20px';
        alert.style.bottom = '20px';
        alert.style.zIndex = '10001';
        alert.style.maxWidth = '420px';

        alert.innerHTML =
            `<span>${escapeHtml(message)}</span>`;

        document.body.appendChild(alert);

        setTimeout(() => {
            alert.remove();
        }, 5000);
    }

    function previewImageInput(input, previewId) {
        const file = input.files[0];

        if (!file) {
            return;
        }

        const preview =
            document.getElementById(previewId);

        const message =
            previewId === 'addImagePreview'
                ? document.getElementById('addImageMessage')
                : null;

        const allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        if (!allowedTypes.includes(file.type)) {
            toast(
                'Please choose a JPG, JPEG, PNG or WebP image.',
                'error'
            );

            input.value = '';
            return;
        }

        if (file.size > 2 * 1024 * 1024) {
            toast(
                'Image must be 2 MB or smaller.',
                'error'
            );

            input.value = '';
            return;
        }

        const url =
            URL.createObjectURL(file);

        if (preview) {
            preview.innerHTML =
                `<img src="${url}" alt="Image preview">`;
        }

        if (message) {
            message.textContent =
                '✓ Image preview ready';

            message.style.color =
                'var(--cp-green)';
        }
    }

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            closeProductModal();
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        renderOptions();
        setupCategoryFlow(
            document.getElementById('add-root'),
            document.getElementById('add-level2'),
            document.getElementById('add-level3'),
            document.getElementById('add-category-id'),
            oldCategory
        );

        document
            .querySelectorAll('[data-edit-category]')
            .forEach(box => {
                setupCategoryFlow(
                    box.querySelector('.edit-root'),
                    box.querySelector('.edit-level2'),
                    box.querySelector('.edit-level3'),
                    box.querySelector('.edit-category-id'),
                    box.dataset.editCategory
                );
            });

        updateCategoryHint();
    });

    @if($errors->any() && old('form_type') !== 'edit')
        document.addEventListener(
            'DOMContentLoaded',
            () => openProductModal('add')
        );
    @endif
</script>
@endpush
