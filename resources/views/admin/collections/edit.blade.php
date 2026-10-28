
@extends('admin.layouts.app')

@section('title', 'Edit Collection')

@section('content')
<div class="collection-page">
    <div class="collection-header">
        <a href="{{ route('admin.collections.index') }}" class="back-link">
            ← Back to Collections
        </a>
        <h1>Edit Collection</h1>
        <p>Update collection details and selected products.</p>
    </div>

    @if ($errors->any())
        <div class="alert-error">
            <strong>Please check the following:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.collections.update', $collection) }}"
          method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="collection-layout">
            <section class="collection-card">
                <h2>Collection Information</h2>

                <div class="form-field">
                    <label for="name">Collection Name *</label>
                    <input id="name" name="name" type="text"
                           value="{{ old('name', $collection->name) }}"
                           maxlength="255" required>
                </div>

                <div class="form-field">
                    <label for="slug">Slug</label>
                    <input id="slug" name="slug" type="text"
                           value="{{ old('slug', $collection->slug) }}">
                </div>

                <div class="form-field">
                    <label for="description">Description</label>
                    <textarea id="description" name="description"
                              rows="4" maxlength="5000">{{ old('description', $collection->description) }}</textarea>
                </div>

                <div class="form-field">
                    <label for="image">Replace Cover Image</label>
                    <input id="image" name="image" type="file"
                           accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">

                    <img id="image-preview"
                         src="{{ $collection->image ? asset('storage/' . $collection->image) : '' }}"
                         alt="Collection cover preview"
                         style="{{ $collection->image ? '' : 'display:none;' }}max-width:220px;max-height:160px;object-fit:cover;border-radius:12px;margin-top:12px;">

                    @if ($collection->image)
                        <label class="remove-image-option">
                            <input type="checkbox" name="remove_image" value="1">
                            Remove current image
                        </label>
                    @endif

                    <small>JPG, PNG or WebP. Maximum 5 MB.</small>
                </div>
            </section>

            <section class="collection-card">
                <h2>Display Settings</h2>

                <div class="form-field">
                    <label for="sort_order">Display Order</label>
                    <input id="sort_order" name="sort_order" type="number"
                           min="0"
                           value="{{ old('sort_order', $collection->sort_order) }}">
                </div>

                <label class="status-option">
                    <input type="checkbox" name="status" value="1"
                           {{ old('status', $collection->status) ? 'checked' : '' }}>
                    <span>
                        <strong>Active Collection</strong>
                        <small>Enable this collection for display.</small>
                    </span>
                </label>
            </section>
        </div>

        <section class="collection-card">
            <div class="products-heading">
                <div>
                    <h2>Select Products</h2>
                    <p>Check products to include them in this collection.</p>
                </div>
                <span id="selected-count" class="selected-count">0 selected</span>
            </div>

            <input id="product-search" type="search"
                   placeholder="Search products by name..."
                   aria-label="Search products">

            <div class="product-selection">
                @forelse ($products as $product)
                    <label class="product-option"
                           data-name="{{ strtolower($product->name) }}">
                        <input type="checkbox"
                               name="product_ids[]"
                               value="{{ $product->id }}"
                               {{ in_array((string) $product->id, array_map('strval', old('product_ids', $selectedProductIds)), true) ? 'checked' : '' }}>

                        @if ($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}"
                                 alt="{{ $product->name }}" loading="lazy">
                        @else
                            <div class="product-placeholder">No image</div>
                        @endif

                        <span class="product-name">{{ $product->name }}</span>
                    </label>
                @empty
                    <p class="empty-products">
                        No active products are available.
                    </p>
                @endforelse
            </div>
        </section>

        <div class="form-actions">
            <a href="{{ route('admin.collections.index') }}" class="btn-secondary">
                Cancel
            </a>
            <button type="submit" class="btn-primary">
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
.collection-page{color:#29221e}
.collection-header{margin-bottom:24px}
.collection-header h1{font-size:27px;margin:12px 0 6px}
.collection-header p,.products-heading p{color:#81766e;margin:0}
.back-link{color:#6f4e37;text-decoration:none;font-size:14px}
.collection-layout{display:grid;grid-template-columns:2fr 1fr;gap:20px}
.collection-card{background:#fff;border:1px solid #eee6df;border-radius:16px;padding:24px;margin-bottom:20px}
.collection-card h2{font-size:18px;margin:0 0 22px}
.form-field{margin-bottom:20px}
.form-field label{display:block;font-size:14px;font-weight:600;margin-bottom:8px}
.form-field input:not([type=file]),.form-field textarea,#product-search{width:100%;padding:12px;border:1px solid #ded6cf;border-radius:9px;font:inherit;box-sizing:border-box}
.form-field small,.status-option small{display:block;color:#81766e;font-size:12px;margin-top:7px}
.status-option,.remove-image-option{display:flex;align-items:flex-start;gap:10px;padding:14px;background:#faf8f5;border-radius:10px;cursor:pointer;margin-top:12px}
.status-option input,.remove-image-option input{margin-top:4px;accent-color:#6f4e37}
.status-option strong{font-size:14px}
.products-heading{display:flex;justify-content:space-between;gap:12px;align-items:center;margin-bottom:18px}
.products-heading h2{margin-bottom:6px}
.selected-count{background:#f5ede3;color:#6f4e37;padding:7px 10px;border-radius:20px;font-size:12px;white-space:nowrap}
.product-selection{display:grid;grid-template-columns:repeat(auto-fill,minmax(165px,1fr));gap:12px;margin-top:18px}
.product-option{display:flex;flex-direction:column;gap:8px;padding:10px;border:1px solid #e8e0d9;border-radius:12px;cursor:pointer;min-width:0}
.product-option:has(input:checked){border-color:#6f4e37;background:#faf5ef}
.product-option input{accent-color:#6f4e37}
.product-option img,.product-placeholder{width:100%;height:125px;object-fit:cover;border-radius:8px}
.product-placeholder{display:flex;align-items:center;justify-content:center;background:#f5f1ed;color:#81766e;font-size:12px}
.product-name{font-size:13px;font-weight:600;overflow-wrap:anywhere}
.empty-products{color:#81766e}
.form-actions{display:flex;justify-content:flex-end;gap:10px;margin-bottom:30px}
.btn-primary,.btn-secondary{display:inline-block;border:0;border-radius:9px;padding:12px 18px;text-decoration:none;font-size:14px;cursor:pointer}
.btn-primary{background:#6f4e37;color:white}
.btn-secondary{background:#eee8e2;color:#39291f}
.alert-error{background:#fff0ef;border:1px solid #edc5c1;color:#9d3028;padding:14px 18px;border-radius:10px;margin-bottom:20px}
@media(max-width:800px){.collection-layout{grid-template-columns:1fr}.collection-card{padding:18px}.product-selection{grid-template-columns:repeat(2,minmax(0,1fr))}}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const search = document.getElementById('product-search');
    const options = Array.from(document.querySelectorAll('.product-option'));
    const count = document.getElementById('selected-count');
    const imageInput = document.getElementById('image');
    const preview = document.getElementById('image-preview');

    function updateCount() {
        count.textContent =
            document.querySelectorAll('input[name="product_ids[]"]:checked').length
            + ' selected';
    }

    document.querySelectorAll('input[name="product_ids[]"]').forEach(input => {
        input.addEventListener('change', updateCount);
    });

    search.addEventListener('input', function () {
        const term = this.value.trim().toLowerCase();
        options.forEach(option => {
            option.hidden = !option.dataset.name.includes(term);
        });
    });

    imageInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;

        if (!file.type.startsWith('image/')) {
            this.value = '';
            alert('Please select an image file.');
            return;
        }

        preview.src = URL.createObjectURL(file);
        preview.style.display = 'block';
    });

    updateCount();
});
</script>
@endpush
