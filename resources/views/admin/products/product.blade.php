@extends('admin.layouts.app')

@section('title', 'Products')
@section('page-title', 'Products')

@section('content')
<div class="products-page">
    <div class="products-header">
        <div>
            <span class="products-eyebrow">PRODUCT CATALOG</span>
            <h1>Products</h1>
            <p>Manage products, variants, pricing and stock.</p>
        </div>
        <button type="button" class="products-add-button" onclick="openProductModal('add')">
            <span>＋</span> Add New Product
        </button>
    </div>

    <div class="products-stats">
        <div class="product-stat-card"><div class="product-stat-icon">P</div><div><span>Total Products</span><strong>{{ number_format($totalProducts ?? 0) }}</strong></div></div>
        <div class="product-stat-card"><div class="product-stat-icon active">✓</div><div><span>Active Products</span><strong>{{ number_format($activeProducts ?? 0) }}</strong></div></div>
        <div class="product-stat-card"><div class="product-stat-icon inactive">○</div><div><span>Inactive Products</span><strong>{{ number_format($inactiveProducts ?? 0) }}</strong></div></div>
        <div class="product-stat-card"><div class="product-stat-icon variant">V</div><div><span>Total Variants</span><strong>{{ number_format($totalVariants ?? 0) }}</strong></div></div>
    </div>

    <div class="products-toolbar">
        <form method="GET" action="{{ route('admin.products.index') }}" class="products-toolbar-form">
            <div class="products-search-wrap">
                <span>⌕</span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search product, SKU, barcode, color or size...">
            </div>
            <select name="category_id"><option value="">All Categories</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string)request('category_id') === (string)$category->id)>{{ $category->name }}</option>@endforeach</select>
            <select name="status"><option value="">All Status</option><option value="active" @selected(request('status')==='active')>Active</option><option value="inactive" @selected(request('status')==='inactive')>Inactive</option></select>
            <select name="sort"><option value="latest" @selected(request('sort','latest')==='latest')>Latest</option><option value="name" @selected(request('sort')==='name')>Name A-Z</option><option value="oldest" @selected(request('sort')==='oldest')>Oldest</option></select>
            <button type="submit" class="products-search-button">Search</button>
            @if(request()->hasAny(['search','category_id','status','sort']))<a href="{{ route('admin.products.index') }}" class="products-reset-button">Reset</a>@endif
        </form>
    </div>

    <div class="products-card">
        <div class="products-card-header">
            <div><h2>Product List</h2><p>{{ number_format($products->total()) }} {{ $products->total() === 1 ? 'product' : 'products' }} found</p></div>
            <div class="product-view-toggle">
                <button type="button" class="product-view-toggle-button active" id="product-list-view-button" onclick="setProductView('list')">☰</button>
                <button type="button" class="product-view-toggle-button" id="product-grid-view-button" onclick="setProductView('grid')">▦</button>
            </div>
        </div>

        @if($products->count())
            <div class="products-table-wrapper" id="products-list-view">
                <table class="products-table">
                    <thead><tr><th>Product</th><th>Category</th><th>Price</th><th>Variants</th><th>Stock</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                    @foreach($products as $product)
                        @php
                            $variantCount = $product->variants->count();
                            $totalStock = $product->variants->sum(fn($variant) => (int)($variant->inventory?->quantity ?? 0));
                            $firstVariant = $product->variants->first();
                            $sellingPrice = $firstVariant?->selling_price;
                        @endphp
                        <tr data-product-name="product-{{ $product->id }}" data-name="{{ $product->name }}">
                            <td><div class="product-cell"><div class="product-image">@if($product->image)<img src="{{ \Illuminate\Support\Facades\Storage::url($product->image) }}" alt="{{ $product->name }}">@else{{ strtoupper(substr($product->name,0,1)) }}@endif</div><div class="product-info"><strong>{{ $product->name }}</strong><span>SKU: {{ $firstVariant?->sku ?? '—' }}</span></div></div></td>
                            <td><span class="category-badge">{{ $product->category?->name ?? '—' }}</span></td>
                            <td><strong class="product-price">{{ $sellingPrice !== null ? '$'.number_format((float)$sellingPrice,2) : '—' }}</strong></td>
                            <td><span class="variant-count">{{ number_format($variantCount) }}</span></td>
                            <td><span class="stock-value {{ $totalStock <= 0 ? 'stock-zero' : '' }}">{{ number_format($totalStock) }}</span></td>
                            <td><span class="status-badge {{ $product->status ? 'status-active' : 'status-inactive' }}"><span></span>{{ $product->status ? 'Active' : 'Inactive' }}</span></td>
                            <td><div class="action-buttons"><button type="button" onclick="openProductModal('view', {{ $product->id }})">View</button><button type="button" onclick="openProductModal('edit', {{ $product->id }})">Edit</button><form action="{{ route('admin.products.destroy',$product->id) }}" method="POST" onsubmit="return confirm('Delete this product?')">@csrf @method('DELETE')<button type="submit" class="delete-button">Delete</button></form></div></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="products-grid-wrapper" id="products-grid-view" hidden>
                <div class="products-grid">
                    @foreach($products as $product)
                        @php $totalStock=$product->variants->sum(fn($v)=>(int)($v->inventory?->quantity??0)); $firstVariant=$product->variants->first(); @endphp
                        <div class="product-grid-card" data-product-name="product-{{ $product->id }}" data-name="{{ $product->name }}">
                            <div class="product-grid-image">@if($product->image)<img src="{{ \Illuminate\Support\Facades\Storage::url($product->image) }}" alt="{{ $product->name }}">@else{{ strtoupper(substr($product->name,0,1)) }}@endif</div>
                            <h3>{{ $product->name }}</h3><span>{{ $product->category?->name ?? '—' }}</span>
                            <div class="grid-meta"><b>{{ $firstVariant?->selling_price !== null ? '$'.number_format((float)$firstVariant->selling_price,2) : '—' }}</b><b>{{ number_format($product->variants->count()) }} variants</b><b>{{ number_format($totalStock) }} stock</b></div>
                            <div class="grid-actions"><button type="button" onclick="openProductModal('view',{{ $product->id }})">View</button><button type="button" onclick="openProductModal('edit',{{ $product->id }})">Edit</button><button type="button" class="delete-button" onclick="openProductModal('delete',{{ $product->id }})">Delete</button></div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="products-pagination"><span>Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ $products->total() }}</span><div>@if($products->onFirstPage())<span class="page-btn disabled">←</span>@else<a class="page-btn" href="{{ $products->previousPageUrl() }}">←</a>@endif<span class="page-current">{{ $products->currentPage() }}</span>@if($products->hasMorePages())<a class="page-btn" href="{{ $products->nextPageUrl() }}">→</a>@else<span class="page-btn disabled">→</span>@endif</div></div>
        @else
            <div class="products-empty"><div class="products-empty-icon">P</div><h3>No products found</h3><p>{{ request()->hasAny(['search','category_id','status']) ? 'No products match your filters.' : 'There are no products in the database yet.' }}</p><button type="button" class="products-add-button" onclick="openProductModal('add')">＋ Add New Product</button></div>
        @endif
    </div>
</div>

<div class="product-modal-backdrop" id="product-modal-backdrop" onclick="backdropClose(event)">
    <div class="product-modal" id="product-add-modal">
        <div class="modal-header"><div><span>PRODUCT CATALOG</span><h2>Add New Product</h2><p>Create one product with every Color + Size variant.</p></div><button type="button" onclick="closeProductModal()">×</button></div>
        <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" id="add-product-form">
            @csrf
            <div class="modal-body">
                <section><h3>1. Basic Information</h3><div class="form-grid">
                    <label class="full">Product Name *<input name="name" id="add-name" value="{{ old('name') }}" required></label>
                    <label>Category *<select name="category_id" required><option value="">Select category</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id')==$category->id)>{{ $category->name }}</option>@endforeach</select></label>
                    <label>Gender<select name="gender"><option value="">Select gender</option>@foreach(['Men','Women','Unisex','Kids'] as $gender)<option value="{{ $gender }}">{{ $gender }}</option>@endforeach</select></label>
                    <label>Status<select name="status"><option value="1">Active</option><option value="0">Inactive</option></select></label>
                    <label>Product Image<input type="file" name="image" accept="image/jpeg,image/png,image/webp"></label>
                    <label class="full">Description<textarea name="description" rows="3" placeholder="Product details...">{{ old('description') }}</textarea></label>
                </div></section>

                <section><h3>2. Pricing</h3><div class="form-grid">
                    <label>Cost Price *<input type="number" name="base_cost_price" id="base-cost" min="0" step="0.01" value="{{ old('base_cost_price',0) }}"></label>
                    <label>Selling Price *<input type="number" name="base_selling_price" id="base-selling" min="0" step="0.01" value="{{ old('base_selling_price',0) }}"></label>
                    <label>Discount Type<select id="base-discount-type"><option value="percentage">Percentage (%)</option><option value="fixed">Fixed Amount ($)</option></select></label>
                    <label>Discount<input type="number" id="base-discount" min="0" step="0.01" value="0"></label>
                </div><p class="hint">The pricing values are copied into every generated variant and submitted to the database.</p></section>

                <section><h3>3. Variants ⭐</h3><p class="section-note">Choose Colors and Sizes. The system creates one database variant for every Color + Size combination.</p>
                    <div class="variant-builder-grid">
                        <div class="builder-box"><div class="builder-title"><b>🎨 Colors</b><button type="button" onclick="addColor()">＋ Add Color</button></div><div id="colors-list"></div></div>
                        <div class="builder-box"><div class="builder-title"><b>📏 Sizes</b><button type="button" onclick="addSize()">＋ Add Size</button></div><div id="sizes-list"></div></div>
                    </div>
                    <div class="generated-head"><div><h4>Generated Variants</h4><span id="variant-count-text">0 variants</span></div><button type="button" class="generate-button" onclick="generateVariants()">Generate Variants</button></div>
                    <div class="variant-table-wrap"><table class="variant-table"><thead><tr><th>Color</th><th>Size</th><th>SKU</th><th>Cost</th><th>Selling</th><th>Discount</th><th>Stock</th><th>Low Stock</th><th>Status</th><th></th></tr></thead><tbody id="variant-rows"><tr><td colspan="10" class="empty-variants">Add at least one color and one size, then click Generate Variants.</td></tr></tbody></table></div>
                </section>
            </div>
            <div class="modal-footer"><button type="button" class="cancel-button" onclick="closeProductModal()">Cancel</button><button type="submit" class="save-button">Save Product</button></div>
        </form>
    </div>

    @foreach($products as $product)
        <div class="product-modal" id="product-view-modal-{{ $product->id }}">
            <div class="modal-header"><div><span>PRODUCT</span><h2>{{ $product->name }}</h2><p>Variant and stock details from the database.</p></div><button type="button" onclick="closeProductModal()">×</button></div>
            <div class="modal-body"><section><div class="detail-grid"><div><b>Category</b><span>{{ $product->category?->name ?? '—' }}</span></div><div><b>Gender</b><span>{{ $product->gender ?? '—' }}</span></div><div><b>Status</b><span>{{ $product->status ? 'Active' : 'Inactive' }}</span></div><div><b>Description</b><span>{{ $product->description ?? '—' }}</span></div></div></section><section><h3>Color + Size Stock</h3><div class="detail-variants">@forelse($product->variants as $variant)<div class="detail-variant"><b>{{ $variant->color ?: 'No Color' }} / {{ $variant->size ?: 'No Size' }}</b><span>SKU: {{ $variant->sku }}</span><span>Stock: <strong>{{ number_format((int)($variant->inventory?->quantity ?? 0)) }}</strong></span><span>${{ number_format((float)$variant->selling_price,2) }}</span></div>@empty<div class="empty-variants">No variants in database.</div>@endforelse</div></section></div>
            <div class="modal-footer"><button type="button" class="cancel-button" onclick="closeProductModal()">Close</button><button type="button" class="cancel-button danger-outline" onclick="openProductModal('delete',{{ $product->id }})">Delete</button><button type="button" class="save-button" onclick="closeProductModal();openProductModal('edit',{{ $product->id }})">Edit Product</button></div>
        </div>

        @php $editVariants = $product->variants->map(function($v){ return ['id'=>$v->id,'sku'=>$v->sku,'barcode'=>$v->barcode,'size'=>$v->size,'color'=>$v->color,'cost_price'=>$v->cost_price,'selling_price'=>$v->selling_price,'discount_type'=>$v->discount_type ?? 'percentage','discount_value'=>$v->discount_value ?? 0,'stock'=>(int)($v->inventory?->quantity ?? 0),'low_stock_threshold'=>(int)($v->inventory?->low_stock_threshold ?? 0),'status'=>(bool)$v->status]; })->values(); @endphp
        <div class="product-modal" id="product-edit-modal-{{ $product->id }}">
            <div class="modal-header"><div><span>PRODUCT CATALOG</span><h2>Edit Product</h2><p>Update product information and every Color + Size stock row.</p></div><button type="button" onclick="closeProductModal()">×</button></div>
            <form method="POST" action="{{ route('admin.products.update',$product->id) }}" enctype="multipart/form-data" onsubmit="return prepareEditVariants({{ $product->id }})">
                @csrf @method('PUT')
                <div class="modal-body"><section><h3>1. Basic Information</h3><div class="form-grid"><label class="full">Product Name *<input name="name" value="{{ $product->name }}" required></label><label>Category *<select name="category_id" required>@foreach($categories as $category)<option value="{{ $category->id }}" @selected($product->category_id==$category->id)>{{ $category->name }}</option>@endforeach</select></label><label>Gender<select name="gender"><option value="">Select gender</option>@foreach(['Men','Women','Unisex','Kids'] as $gender)<option value="{{ $gender }}" @selected($product->gender===$gender)>{{ $gender }}</option>@endforeach</select></label><label>Status<select name="status"><option value="1" @selected($product->status)>Active</option><option value="0" @selected(!$product->status)>Inactive</option></select></label><label>Replace Image<input type="file" name="image" accept="image/jpeg,image/png,image/webp"></label><label class="full">Description<textarea name="description" rows="3">{{ $product->description }}</textarea></label></div></section>
                <section><h3>2. Variants & Stock</h3><div class="edit-variant-table-wrap"><table class="variant-table"><thead><tr><th>Color</th><th>Size</th><th>SKU</th><th>Cost</th><th>Selling</th><th>Discount</th><th>Stock</th><th>Low Stock</th><th>Status</th><th></th></tr></thead><tbody id="edit-rows-{{ $product->id }}"></tbody></table></div><button type="button" class="add-edit-row" onclick="addEditVariant({{ $product->id }})">＋ Add Variant</button></section></div>
                <input type="hidden" name="variants_json" id="variants-json-{{ $product->id }}"><div class="modal-footer"><button type="button" class="cancel-button" onclick="closeProductModal()">Cancel</button><button type="submit" class="save-button">Save Changes</button></div>
            </form>
        </div>
        <script>window.editVariantData=window.editVariantData||{};window.editVariantData[{{ $product->id }}]={{ $editVariants->toJson() }};</script>
    @endforeach

    <div class="product-modal product-delete-modal" id="product-delete-modal"><div class="delete-icon">!</div><h2>Delete Product?</h2><p>Are you sure you want to delete <strong id="delete-product-name">this product</strong>?</p><p class="delete-warning">Products already used in sales or purchases cannot be deleted.</p><form id="delete-product-form" method="POST" action="">@csrf @method('DELETE')<div class="modal-footer"><button type="button" class="cancel-button" onclick="closeProductModal()">Cancel</button><button type="submit" class="delete-confirm-button">Delete Product</button></div></form></div>
</div>
@endsection

@push('styles')
<style>
.products-page {
    width:100%;
    color:#24201E;
}.products-header {
    display:flex;
    justify-content:space-between;
    align-items:flex-end;
    margin-bottom:22px;
    gap:20px;
}.products-eyebrow {
    color:#A66A44;
    font-size:11px;
    font-weight:800;
    letter-spacing:1.4px;
}.products-header h1 {
    margin:5px 0 0;
    color:#2C1E17;
    font-size:30px;
    font-weight:800;
}.products-header p {
    margin:6px 0 0;
    color:#8B8580;
    font-size:13px;
}.products-add-button,.save-button,.generate-button {
    border:0;
    border-radius:10px;
    background:#6F4E37;
    color:#fff;
    font-weight:800;
    cursor:pointer;
}.products-add-button {
    padding:13px 18px;
}.products-stats {
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:16px;
    margin-bottom:20px;
}.product-stat-card {
    display:flex;
    align-items:center;
    gap:13px;
    padding:18px;
    border:1px solid rgba(44,30,23,.08);
    border-radius:16px;
    background:#fff;
}.product-stat-icon {
    width:42px;
    height:42px;
    display:grid;
    place-items:center;
    border-radius:12px;
    background:#F5EDE3;
    color:#6F4E37;
    font-weight:900;
}.product-stat-icon.active {
    background:rgba(100,221,23,.10);
    color:#64DD17;
}.product-stat-icon.inactive {
    background:#f3f1ef;
}.product-stat-icon.variant {
    background:#faf1e8;
}.product-stat-card span {
    display:block;
    color:#8B8580;
    font-size:11px;
}.product-stat-card strong {
    display:block;
    margin-top:4px;
    color:#2C1E17;
    font-size:21px;
}.products-toolbar,.products-card {
    border:1px solid rgba(44,30,23,.08);
    border-radius:16px;
    background:#fff;
}.products-toolbar {
    padding:16px;
    margin-bottom:20px;
}.products-toolbar-form {
    display:grid;
    grid-template-columns:minmax(250px,1.8fr) repeat(3,minmax(130px,.7fr)) auto auto;
    gap:10px;
    align-items:center;
}.products-search-wrap {
    height:42px;
    display:flex;
    align-items:center;
    gap:9px;
    padding:0 12px;
    border:1px solid rgba(44,30,23,.12);
    border-radius:10px;
    background:#FAF8F5;
}.products-search-wrap span {
    color:#6F4E37;
}.products-search-wrap input {
    border:0;
    outline:0;
    background:transparent;
    width:100%;
    font:inherit;
    font-size:13px;
}.products-toolbar select {
    height:42px;
    padding:0 10px;
    border:1px solid rgba(44,30,23,.12);
    border-radius:10px;
    background:#FAF8F5;
    font-size:13px;
}.products-search-button {
    height:42px;
    padding:0 16px;
    border:0;
    border-radius:10px;
    background:#6F4E37;
    color:#fff;
    font-weight:800;
}.products-reset-button {
    display:grid;
    place-items:center;
    height:40px;
    padding:0 14px;
    border:1px solid rgba(44,30,23,.12);
    border-radius:10px;
    color:#6F4E37;
    text-decoration:none;
    font-size:13px;
}.products-card-header {
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:18px 20px;
    border-bottom:1px solid rgba(44,30,23,.07);
}.products-card-header h2 {
    margin:0;
    font-size:19px;
}.products-card-header p {
    margin:4px 0 0;
    color:#8B8580;
    font-size:12px;
}.product-view-toggle {
    display:flex;
    border:1px solid rgba(44,30,23,.1);
    border-radius:10px;
    padding:3px;
}.product-view-toggle-button {
    border:0;
    background:transparent;
    border-radius:7px;
    padding:7px 10px;
    color:#6F4E37;
    cursor:pointer;
}.product-view-toggle-button.active {
    background:#6F4E37;
    color:#fff;
}.products-table-wrapper {
    overflow:auto;
}.products-table {
    width:100%;
    border-collapse:collapse;
}.products-table th {
    padding:12px 16px;
    background:#F5EDE3;
    color:#6F4E37;
    text-align:left;
    font-size:11px;
}.products-table td {
    padding:13px 16px;
    border-bottom:1px solid rgba(44,30,23,.07);
    font-size:12px;
}.product-cell {
    display:flex;
    align-items:center;
    gap:11px;
}.product-image,.product-grid-image {
    display:grid;
    place-items:center;
    background:#F5EDE3;
    color:#6F4E37;
    overflow:hidden;
}.product-image {
    width:44px;
    height:44px;
    border-radius:11px;
    font-weight:800;
}.product-image img,.product-grid-image img {
    width:100%;
    height:100%;
    object-fit:cover;
}.product-info strong {
    display:block;
    font-size:13px;
}.product-info span {
    display:block;
    margin-top:3px;
    color:#8B8580;
    font-size:10px;
}.category-badge,.variant-count,.status-badge,.stock-value {
    display:inline-flex;
    align-items:center;
    border-radius:8px;
    padding:6px 9px;
    font-size:10px;
    font-weight:800;
}.category-badge,.variant-count {
    background:#F5EDE3;
    color:#6F4E37;
}.product-price {
    color:#6F4E37;
}.stock-value {
    background:rgba(100,221,23,.10);
    color:#4E7D49;
}.stock-zero {
    background:rgba(255,0,0,.08);
    color:#FF0000;
}.status-badge {
    gap:6px;
}.status-badge span {
    width:7px;
    height:7px;
    border-radius:50%;
    background:currentColor;
}.status-active {
    background:rgba(100,221,23,.10);
    color:#64DD17;
}.status-inactive {
    background:rgba(255,0,0,.10);
    color:#FF0000;
}.action-buttons {
    display:flex;
    gap:7px;
    align-items:center;
}.action-buttons button {
    padding:7px 10px;
    border:1px solid #eadbd0;
    border-radius:8px;
    background:#fff;
    color:#6F4E37;
    font-size:11px;
    cursor:pointer;
}.action-buttons .delete-button {
    font-weight:700;
    color:#FF0000;
    border-color:rgba(255,0,0,.25);
}.action-buttons form {
    margin:0;
}.products-grid {
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:14px;
    padding:18px;
}.product-grid-card {
    border:1px solid rgba(44,30,23,.09);
    border-radius:14px;
    padding:14px;
}.product-grid-image {
    height:180px;
    border-radius:11px;
    margin-bottom:12px;
}.product-grid-card h3 {
    margin:0;
    font-size:15px;
}.product-grid-card>span {
    display:block;
    margin-top:4px;
    color:#8B8580;
    font-size:11px;
}.grid-meta {
    display:flex;
    justify-content:space-between;
    gap:8px;
    margin:14px 0;
    font-size:11px;
}.grid-actions {
    display:flex;
    gap:7px;
}.grid-actions button {
    flex:1;
    padding:8px;
    border:1px solid #eadbd0;
    border-radius:8px;
    background:#fff;
    color:#6F4E37;
    cursor:pointer;
}.grid-actions .delete-button {
    font-weight:700;
    color:#FF0000;
    border-color:rgba(255,0,0,.25);
}.products-pagination {
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:14px 18px;
    color:#8B8580;
    font-size:11px;
}.products-pagination>div {
    display:flex;
    align-items:center;
    gap:6px;
}.page-btn,.page-current {
    width:28px;
    height:28px;
    display:grid;
    place-items:center;
    border:1px solid #eadbd0;
    border-radius:8px;
    text-decoration:none;
    color:#6F4E37;
}.page-current {
    background:#6F4E37;
    color:#fff;
}.page-btn.disabled {
    opacity:.4;
}.products-empty {
    text-align:center;
    padding:60px 20px;
}.products-empty-icon {
    width:52px;
    height:52px;
    margin:auto;
    display:grid;
    place-items:center;
    border-radius:15px;
    background:#F5EDE3;
    color:#6F4E37;
    font-weight:900;
}.products-empty h3 {
    margin:12px 0 5px;
}.products-empty p {
    color:#8B8580;
    font-size:13px;
    margin-bottom:18px;
}.product-modal-backdrop {
    position:fixed;
    inset:0;
    z-index:9999;
    display:none;
    align-items:center;
    justify-content:center;
    padding:20px;
    background:rgba(36,32,30,.62);
    backdrop-filter:blur(4px);
}.product-modal-backdrop.show {
    display:flex;
}.product-modal {
    display:none;
    width:min(1050px,100%);
    max-height:calc(100vh - 40px);
    overflow:auto;
    background:#fff;
    border-radius:18px;
    box-shadow:0 24px 70px rgba(44,30,23,.25);
}.product-modal.show {
    display:block;
}.modal-header {
    position:sticky;
    top:0;
    z-index:3;
    display:flex;
    justify-content:space-between;
    gap:20px;
    padding:20px 22px;
    border-bottom:1px solid rgba(44,30,23,.08);
    background:#fff;
}.modal-header span {
    color:#A66A44;
    font-size:10px;
    font-weight:800;
    letter-spacing:1px;
}.modal-header h2 {
    margin:4px 0 0;
    color:#2C1E17;
    font-size:21px;
}.modal-header p {
    margin:5px 0 0;
    color:#8B8580;
    font-size:12px;
}.modal-header>button {
    width:34px;
    height:34px;
    border:1px solid #eadbd0;
    border-radius:9px;
    background:#FAF8F5;
    color:#6F4E37;
    font-size:22px;
    cursor:pointer;
}.modal-body {
    padding:18px 22px;
}.modal-body section {
    padding:17px;
    margin-bottom:14px;
    border:1px solid rgba(44,30,23,.08);
    border-radius:13px;
}.modal-body h3 {
    margin:0 0 14px;
    font-size:15px;
    color:#2C1E17;
}.form-grid {
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:13px;
}.form-grid label {
    display:block;
    color:#6F4E37;
    font-size:11px;
    font-weight:800;
}.form-grid label.full {
    grid-column:1/-1;
}.form-grid input,.form-grid select,.form-grid textarea {
    width:100%;
    box-sizing:border-box;
    margin-top:6px;
    padding:10px;
    border:1px solid #e6d8ce;
    border-radius:9px;
    background:#FAF8F5;
    outline:0;
    font:inherit;
    font-size:12px;
    color:#24201E;
}.form-grid textarea {
    resize:vertical;
}.form-grid input:focus,.form-grid select:focus,.form-grid textarea:focus {
    border-color:#A66A44;
    background:#fff;
}.hint,.section-note {
    color:#8B8580;
    font-size:11px;
    margin:8px 0 0;
}.variant-builder-grid {
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:14px;
}.builder-box {
    border:1px solid #eadbd0;
    border-radius:11px;
    padding:13px;
    background:#FAF8F5;
}.builder-title {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:10px;
    font-size:12px;
}.builder-title button,.add-edit-row {
    border:1px solid #eadbd0;
    background:#fff;
    color:#6F4E37;
    border-radius:8px;
    padding:7px 9px;
    font-size:10px;
    font-weight:800;
    cursor:pointer;
}.builder-item {
    display:grid;
    grid-template-columns:1fr auto;
    gap:7px;
    margin-bottom:7px;
}.builder-item input {
    width:100%;
    box-sizing:border-box;
    padding:9px;
    border:1px solid #e6d8ce;
    border-radius:8px;
    background:#fff;
    font-size:12px;
}.remove-builder {
    border:0;
    background:transparent;
    color:#FF0000;
    cursor:pointer;
}.generated-head {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin:16px 0 9px;
}.generated-head h4 {
    margin:0;
    font-size:13px;
}.generated-head span {
    color:#8B8580;
    font-size:10px;
}.generate-button {
    padding:9px 12px;
    font-size:10px;
}.variant-table-wrap,.edit-variant-table-wrap {
    overflow:auto;
    border:1px solid #eadbd0;
    border-radius:10px;
}.variant-table {
    width:100%;
    min-width:980px;
    border-collapse:collapse;
}.variant-table th {
    padding:9px;
    background:#F5EDE3;
    color:#6F4E37;
    text-align:left;
    font-size:9px;
}.variant-table td {
    padding:7px;
    border-top:1px solid #eee3db;
}.variant-table input,.variant-table select {
    width:100%;
    box-sizing:border-box;
    padding:7px;
    border:1px solid #e6d8ce;
    border-radius:7px;
    background:#fff;
    font-size:10px;
}.empty-variants {
    text-align:center;
    padding:25px!important;
    color:#8B8580;
    font-size:11px;
}.variant-remove {
    border:0;
    background:transparent;
    color:#FF0000;
    font-size:15px;
    cursor:pointer;
}.modal-footer {
    position:sticky;
    bottom:0;
    z-index:3;
    display:flex;
    justify-content:flex-end;
    gap:9px;
    padding:14px 22px;
    border-top:1px solid rgba(44,30,23,.08);
    background:#fff;
}.cancel-button,.save-button {
    min-height:40px;
    padding:0 16px;
    border-radius:9px;
    font-size:12px;
}.cancel-button {
    border:1px solid #eadbd0;
    background:#fff;
    color:#6F4E37;
    cursor:pointer;
}.save-button {
    border:0;
}.detail-grid {
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:12px;
}.detail-grid div {
    padding:12px;
    border-radius:10px;
    background:#FAF8F5;
}.detail-grid b,.detail-grid span {
    display:block;
}.detail-grid b {
    color:#8B8580;
    font-size:10px;
}.detail-grid span {
    margin-top:4px;
    font-size:12px;
}.detail-variants {
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:9px;
}.detail-variant {
    padding:11px;
    border:1px solid #eadbd0;
    border-radius:10px;
}.detail-variant b,.detail-variant span {
    display:block;
    font-size:11px;
}.detail-variant span {
    margin-top:4px;
    color:#8B8580;
}.delete-icon {
    width:48px;
    height:48px;
    margin:28px auto 10px;
    display:grid;
    place-items:center;
    border-radius:50%;
    background:rgba(255,0,0,.08);
    color:#FF0000;
    font-size:22px;
    font-weight:900;
}.product-delete-modal {
    text-align:center;
    width:min(430px,100%);
}.product-delete-modal h2 {
    margin:0;
}.product-delete-modal p {
    color:#8B8580;
    font-size:12px;
    padding:0 20px 10px;
}.product-delete-modal .modal-footer {
    position:static;
}.delete-warning {
    font-size:11px!important;
    color:#8B8580;
}.danger-outline {
    color:#FF0000!important;
    border-color:rgba(200,92,74,.25)!important;
}.delete-confirm-button {
    font-weight:700;
    min-height:40px;
    padding:0 16px;
    border:0;
    border-radius:9px;
    background:#FF0000;
    color:#fff;
    font-size:12px;
    font-weight:800;
    cursor:pointer;
}.products-grid-wrapper[hidden] {
    display:none;
}
@media(max-width:900px){.products-stats {
    grid-template-columns:repeat(2,1fr);
}.products-toolbar-form {
    grid-template-columns:1fr 1fr;
}.products-search-wrap {
    grid-column:1/-1;
}.variant-builder-grid {
    grid-template-columns:1fr;
}}
@media(max-width:650px){.products-header {
    align-items:stretch;
    flex-direction:column;
}.products-stats {
    grid-template-columns:1fr;
}.products-toolbar-form,.form-grid,.detail-grid,.detail-variants {
    grid-template-columns:1fr;
}.form-grid label.full {
    grid-column:auto;
}.products-add-button {
    width:100%;
}.modal-body {
    padding:12px;
}.modal-header,.modal-footer {
    padding:15px;
}.products-table th,.products-table td {
    white-space:nowrap;
}}
</style>
@endpush

@push('scripts')
<script>
let colors = [];
let sizes = [];
let variantRows = [];

window.editVariantData = window.editVariantData || {};

function openProductModal(type, id = null) {
    const backdrop = document.getElementById('product-modal-backdrop');

    document.querySelectorAll('.product-modal').forEach(function (modal) {
        modal.classList.remove('show');
    });

    let modal;

    if (type === 'add') {
        modal = document.getElementById('product-add-modal');
    } else {
        modal = document.getElementById(
            'product-' + type + '-modal-' + id
        );
    }

    if (type === 'delete') {
        modal = document.getElementById('product-delete-modal');
    }

    if (!modal) {
        return;
    }

    if (type === 'delete') {
        const product = document.querySelector(
            '[data-product-name="product-' + id + '"]'
        );

        const name = product?.dataset?.name || 'this product';

        document.getElementById('delete-product-name').textContent = name;

        document.getElementById('delete-product-form').action =
            `{{ url('/admin/products') }}/${id}`;
    }

    backdrop.classList.add('show');
    modal.classList.add('show');

    document.body.style.overflow = 'hidden';

    if (type === 'edit') {
        renderEditVariants(id);
    }
}

function closeProductModal() {
    document
        .getElementById('product-modal-backdrop')
        ?.classList.remove('show');

    document.querySelectorAll('.product-modal').forEach(function (modal) {
        modal.classList.remove('show');
    });

    document.body.style.overflow = '';
}

function backdropClose(event) {
    if (event.target.id === 'product-modal-backdrop') {
        closeProductModal();
    }
}

function setProductView(view) {
    const listView = document.getElementById('products-list-view');
    const gridView = document.getElementById('products-grid-view');

    listView.hidden = view !== 'list';
    gridView.hidden = view !== 'grid';

    document
        .getElementById('product-list-view-button')
        .classList.toggle('active', view === 'list');

    document
        .getElementById('product-grid-view-button')
        .classList.toggle('active', view === 'grid');
}

function addColor(value = '') {
    colors.push(value);
    renderBuilders();
}

function addSize(value = '') {
    sizes.push(value);
    renderBuilders();
}

function removeColor(index) {
    colors.splice(index, 1);
    renderBuilders();
}

function removeSize(index) {
    sizes.splice(index, 1);
    renderBuilders();
}

function renderBuilders() {
    document.getElementById('colors-list').innerHTML = colors
        .map(function (value, index) {
            return `
                <div class="builder-item">
                    <input
                        value="${esc(value)}"
                        placeholder="e.g. Black"
                        oninput="colors[${index}]=this.value"
                    >

                    <button
                        type="button"
                        class="remove-builder"
                        onclick="removeColor(${index})"
                    >
                        ×
                    </button>
                </div>
            `;
        })
        .join('');

    document.getElementById('sizes-list').innerHTML = sizes
        .map(function (value, index) {
            return `
                <div class="builder-item">
                    <input
                        value="${esc(value)}"
                        placeholder="e.g. M"
                        oninput="sizes[${index}]=this.value"
                    >

                    <button
                        type="button"
                        class="remove-builder"
                        onclick="removeSize(${index})"
                    >
                        ×
                    </button>
                </div>
            `;
        })
        .join('');
}

function generateVariants() {
    colors = colors
        .map(function (value) {
            return value.trim();
        })
        .filter(Boolean);

    sizes = sizes
        .map(function (value) {
            return value.trim();
        })
        .filter(Boolean);

    const unique = function (items) {
        return [...new Set(
            items.map(function (value) {
                return value.toLowerCase();
            })
        )].map(function (key) {
            return items.find(function (value) {
                return value.toLowerCase() === key;
            });
        });
    };

    colors = unique(colors);
    sizes = unique(sizes);

    if (!colors.length || !sizes.length) {
        alert('Please add at least one Color and one Size.');
        return;
    }

    const name = document
        .getElementById('add-name')
        .value
        .trim();

    const baseSku = slugSku(name || 'PRODUCT');

    const cost =
        document.getElementById('base-cost').value || 0;

    const selling =
        document.getElementById('base-selling').value || 0;

    const discountType =
        document.getElementById('base-discount-type').value;

    const discountValue =
        document.getElementById('base-discount').value || 0;

    variantRows = [];

    colors.forEach(function (color) {
        sizes.forEach(function (size) {
            variantRows.push({
                color: color,
                size: size,
                sku: `${baseSku}-${slugSku(color)}-${slugSku(size)}`,
                cost_price: cost,
                selling_price: selling,
                discount_type: discountType,
                discount_value: discountValue,
                stock: 0,
                low_stock_threshold: 0,
                status: 1
            });
        });
    });

    renderVariantRows();
}

function renderVariantRows() {
    const body = document.getElementById('variant-rows');

    document.getElementById('variant-count-text').textContent =
        `${variantRows.length} variants`;

    if (!variantRows.length) {
        body.innerHTML = `
            <tr>
                <td colspan="10" class="empty-variants">
                    No variants generated.
                </td>
            </tr>
        `;

        return;
    }

    body.innerHTML = variantRows
        .map(function (variant, index) {
            return `
                <tr>
                    <td>
                        <input
                            name="variants[${index}][color]"
                            value="${esc(variant.color)}"
                            required
                        >
                    </td>

                    <td>
                        <input
                            name="variants[${index}][size]"
                            value="${esc(variant.size)}"
                            required
                        >
                    </td>

                    <td>
                        <input
                            name="variants[${index}][sku]"
                            value="${esc(variant.sku)}"
                            required
                        >
                    </td>

                    <td>
                        <input
                            type="number"
                            name="variants[${index}][cost_price]"
                            value="${variant.cost_price}"
                            min="0"
                            step="0.01"
                            required
                        >
                    </td>

                    <td>
                        <input
                            type="number"
                            name="variants[${index}][selling_price]"
                            value="${variant.selling_price}"
                            min="0"
                            step="0.01"
                            required
                        >
                    </td>

                    <td>
                        <select name="variants[${index}][discount_type]">
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

                        <input
                            type="number"
                            name="variants[${index}][discount_value]"
                            value="${variant.discount_value}"
                            min="0"
                            step="0.01"
                        >
                    </td>

                    <td>
                        <input
                            type="number"
                            name="variants[${index}][stock]"
                            value="${variant.stock}"
                            min="0"
                            step="1"
                            required
                        >
                    </td>

                    <td>
                        <input
                            type="number"
                            name="variants[${index}][low_stock_threshold]"
                            value="${variant.low_stock_threshold}"
                            min="0"
                            step="1"
                            required
                        >
                    </td>

                    <td>
                        <select name="variants[${index}][status]">
                            <option value="1" selected>
                                Active
                            </option>

                            <option value="0">
                                Inactive
                            </option>
                        </select>
                    </td>

                    <td>
                        <button
                            type="button"
                            class="variant-remove"
                            onclick="removeVariant(${index})"
                        >
                            ×
                        </button>
                    </td>
                </tr>
            `;
        })
        .join('');
}

function removeVariant(index) {
    variantRows.splice(index, 1);
    renderVariantRows();
}

function addEditVariant(id) {
    window.editVariantData[id].push({
        id: null,
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
    });

    renderEditVariants(id);
}

function removeEditVariant(id, index) {
    window.editVariantData[id].splice(index, 1);
    renderEditVariants(id);
}

function renderEditVariants(id) {
    const data = window.editVariantData[id] || [];
    const body = document.getElementById('edit-rows-' + id);

    if (!body) {
        return;
    }

    body.innerHTML = data
        .map(function (variant, index) {
            return `
                <tr>
                    <td>
                        <input
                            data-k="color"
                            value="${esc(variant.color || '')}"
                            required
                        >
                    </td>

                    <td>
                        <input
                            data-k="size"
                            value="${esc(variant.size || '')}"
                            required
                        >
                    </td>

                    <td>
                        <input
                            data-k="sku"
                            value="${esc(variant.sku || '')}"
                            required
                        >
                    </td>

                    <td>
                        <input
                            type="number"
                            data-k="cost_price"
                            value="${variant.cost_price || 0}"
                            min="0"
                            step="0.01"
                            required
                        >
                    </td>

                    <td>
                        <input
                            type="number"
                            data-k="selling_price"
                            value="${variant.selling_price || 0}"
                            min="0"
                            step="0.01"
                            required
                        >
                    </td>

                    <td>
                        <select data-k="discount_type">
                            <option
                                value="percentage"
                                ${(variant.discount_type || 'percentage') === 'percentage' ? 'selected' : ''}
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

                        <input
                            type="number"
                            data-k="discount_value"
                            value="${variant.discount_value || 0}"
                            min="0"
                            step="0.01"
                        >
                    </td>

                    <td>
                        <input
                            type="number"
                            data-k="stock"
                            value="${variant.stock || 0}"
                            min="0"
                            step="1"
                            required
                        >
                    </td>

                    <td>
                        <input
                            type="number"
                            data-k="low_stock_threshold"
                            value="${variant.low_stock_threshold || 0}"
                            min="0"
                            step="1"
                            required
                        >
                    </td>

                    <td>
                        <select data-k="status">
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
                    </td>

                    <td>
                        <button
                            type="button"
                            class="variant-remove"
                            onclick="removeEditVariant(${id}, ${index})"
                        >
                            ×
                        </button>
                    </td>
                </tr>
            `;
        })
        .join('');
}

function prepareEditVariants(id) {
    const body = document.getElementById('edit-rows-' + id);
    const data = window.editVariantData[id] || [];

    body.querySelectorAll('tr').forEach(function (row, index) {
        row.querySelectorAll('[data-k]').forEach(function (element) {
            const key = element.dataset.k;
            let value = element.value;

            if (
                [
                    'cost_price',
                    'selling_price',
                    'discount_value',
                    'stock',
                    'low_stock_threshold'
                ].includes(key)
            ) {
                value = Number(value || 0);
            }

            if (key === 'status') {
                value = value === '1';
            }

            data[index][key] = value;
        });
    });

    if (!data.length) {
        alert('A product must have at least one variant.');
        return false;
    }

    document.getElementById('variants-json-' + id).value =
        JSON.stringify(data);

    return addEditHiddenFields(
        body.closest('form'),
        data
    );
}

function addEditHiddenFields(form, data) {
    form
        .querySelectorAll('.generated-edit-field')
        .forEach(function (element) {
            element.remove();
        });

    data.forEach(function (variant, index) {
        Object.entries(variant).forEach(function ([key, value]) {
            const input = document.createElement('input');

            input.type = 'hidden';
            input.className = 'generated-edit-field';
            input.name = `variants[${index}][${key}]`;

            input.value =
                typeof value === 'boolean'
                    ? (value ? '1' : '0')
                    : (value ?? '');

            form.appendChild(input);
        });
    });

    return true;
}

function slugSku(value) {
    return String(value)
        .toUpperCase()
        .replace(/[^A-Z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '')
        .slice(0, 20) || 'ITEM';
}

function esc(value) {
    return String(value ?? '').replace(
        /[&<>'"]/g,
        function (character) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                "'": '&#039;',
                '"': '&quot;'
            }[character];
        }
    );
}

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        closeProductModal();
    }
});
</script>
@endpush
