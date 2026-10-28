
@extends('admin.layouts.app')

@section('title', 'Create Collection')

@section('content')
<div class="collection-page">
    <div class="collection-header">
        <div>
            <a href="{{ route('admin.collections.index') }}" class="back-link">
                ← Back to Collections
            </a>
            <h1>Create Collection</h1>
            <p>Create a collection and select products to display together.</p>
        </div>
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

    <form action="{{ route('admin.collections.store') }}"
          method="POST"
          enctype="multipart/form-data">
        @csrf

        <div class="collection-layout">
            <section class="collection-card">
                <h2>Collection Information</h2>

                <div class="form-field">
                    <label for="name">Collection Name *</label>
                    <input id="name" name="name" type="text"
                           value="{{ old('name') }}"
                           placeholder="e.g. Wear a Trend"
                           maxlength="255" required>
                </div>

                <div class="form-field">
                    <label for="slug">Slug (optional)</label>
                    <input id="slug" name="slug" type="text"
                           value="{{ old('slug') }}"
                           placeholder="wear-a-trend">
                    <small>Leave blank to generate from the collection name.</small>
                </div>

                <div class="form-field">
                    <label for="description">Description</label>
                    <textarea id="description" name="description"
                              rows="4" maxlength="5000"
                              placeholder="Describe this collection...">{{ old('description') }}</textarea>
                </div>

                <div class="form-field">
                    <label for="image">Cover Image</label>
                    <input id="image" name="image" type="file"
                           accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                    <small>JPG, PNG or WebP. Maximum 5 MB.</small>
                    <img id="image-preview" alt="Cover preview"
                         style="display:none;max-width:220px;max-height:160px;object-fit:cover;border-radius:12px;margin-top:12px;">
                </div>
            </section>

            <section class="collection-card">
                <h2>Display Settings</h2>

                <div class="form-field">
                    <label for="sort_order">Display Order</label>
                    <input id="sort_order" name="sort_order" type="number"
                           min="0" value="{{ old('sort_order', 0) }}">
                    <small>Lower numbers appear first.</small>
                </div>

                <label class="status-option">
                    <input type="checkbox" name="status" value="1"
                           {{ old('status') ? 'checked' : '' }}>
                    <span>
                        <strong>Active Collection</strong>
                        <small>Enable this collection for display.</small>
                    </span>
                </label>
            </section>
        </div>

        <section class="collection-card products-card">
            <div class="products-heading">
                <div>
                    <h2>Select Products</h2>
                    <p>Choose the products that belong to this collection.</p>
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
                               {{ in_array((string) $product->id, array_map('strval', old('product_ids', [])), true) ? 'checked' : '' }}>

                        @if ($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}"
                                 alt="{{ $product->name }}"
                                 loading="lazy">
                        @else
                            <div class="product-placeholder">No image</div>
                        @endif

                        <span class="product-name">{{ $product->name }}</span>
                    </label>
                @empty
                    <p class="empty-products">
                        No active products are available. Add or activate products first.
                    </p>
                @endforelse
            </div>
        </section>

        <div class="form-actions">
            <a href="{{ route('admin.collections.index') }}" class="btn-secondary">
                Cancel
            </a>
            <button type="submit" class="btn-primary">
                Save Collection
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
.form-field input:focus,.form-field textarea:focus,#product-search:focus{outline:2px solid #c99a6b;border-color:#6f4e37}
.form-field small,.status-option small{display:block;color:#81766e;font-size:12px;margin-top:7px}
.status-option{display:flex;align-items:flex-start;gap:10px;padding:14px;background:#faf8f5;border-radius:10px;cursor:pointer}
.status-option input{margin-top:4px;accent-color:#6f4e37}
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
        const selected = document.querySelectorAll(
            'input[name="product_ids[]"]:checked'
        ).length;
        count.textContent = selected + ' selected';
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

        if (!file) {
            preview.removeAttribute('src');
            preview.style.display = 'none';
            return;
        }

        if (!file.type.startsWith('image/')) {
            this.value = '';
            preview.style.display = 'none';
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
