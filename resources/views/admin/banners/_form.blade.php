
{{-- resources/views/admin/banners/_form.blade.php --}}

@if ($errors->any())
    <div class="banner-alert error" role="alert">
        <strong>Please correct the following errors:</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="banner-form-grid">

    {{-- Banner Title: Optional --}}
    <div class="banner-field">
        <label for="title">
            Banner Title <span class="optional">(optional)</span>
        </label>

        <input
            id="title"
            name="title"
            type="text"
            maxlength="255"
            value="{{ old('title', $banner?->title ?? '') }}"
            placeholder="Enter a banner title (optional)"
        >

        <small>You can leave this field empty.</small>
    </div>

    {{-- Subtitle: Optional --}}
    <div class="banner-field">
        <label for="subtitle">
            Subtitle <span class="optional">(optional)</span>
        </label>

        <input
            id="subtitle"
            name="subtitle"
            type="text"
            maxlength="255"
            value="{{ old('subtitle', $banner?->subtitle ?? '') }}"
            placeholder="Enter a promotional message (optional)"
        >

        <small>You can leave this field empty.</small>
    </div>

    {{-- Banner Image --}}
    <div class="banner-field full">
        <label for="image">
            Banner Image
            @if (!$banner)
                <span class="required">*</span>
            @else
                <span class="optional">(optional)</span>
            @endif
        </label>

        <input
            id="image"
            name="image"
            type="file"
            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
            @required(!$banner)
            aria-describedby="banner-image-help"
        >

        <small id="banner-image-help">
            Accepted formats: JPG, PNG and WebP. Maximum file size: 5 MB.
        </small>

        <div
            id="banner-image-preview"
            class="banner-upload-preview"
            @if (!$banner?->image) hidden @endif
        >
            <img
                id="banner-preview-img"
                src="{{ $banner?->image
                    ? \Illuminate\Support\Facades\Storage::disk('public')->url($banner->image)
                    : '' }}"
                alt="Banner image preview"
            >

            <span class="banner-preview-caption">
                {{ $banner?->image ? 'Current banner image' : 'Selected image preview' }}
            </span>
        </div>
    </div>

    {{-- Destination Type --}}
    <div class="banner-field">
        <label for="link_type">Destination Type</label>

        @php
            $selectedLinkType = old(
                'link_type',
                $banner?->link_type ?? 'none'
            );

            $allowedLinkTypes = [
                'none',
                'product',
                'category',
                'collection',
                'discount',
            ];

            if (!in_array($selectedLinkType, $allowedLinkTypes, true)) {
                $selectedLinkType = 'none';
            }
        @endphp

        <select
            id="link_type"
            name="link_type"
            aria-describedby="banner-destination-help"
        >
            <option value="none"
                @selected($selectedLinkType === 'none')>
                No destination
            </option>

            <option value="product"
                @selected($selectedLinkType === 'product')>
                Product
            </option>

            <option value="category"
                @selected($selectedLinkType === 'category')>
                Category
            </option>

            <option value="collection"
                @selected($selectedLinkType === 'collection')>
                Collection
            </option>

            <option value="discount"
                @selected($selectedLinkType === 'discount')>
                Discount / Promotion
            </option>
        </select>

        <small id="banner-destination-help">
            Choose the page that opens when a customer taps this banner.
        </small>
    </div>

    {{-- Destination ID --}}
    <div
        class="banner-field"
        id="banner-link-id-field"
        @if (!in_array($selectedLinkType, ['product', 'category', 'collection'], true))
            hidden
        @endif
    >
        <label for="link_id">
            Destination ID <span class="required">*</span>
        </label>

        <input
            id="link_id"
            name="link_id"
            type="number"
            min="1"
            step="1"
            value="{{ old('link_id', $banner?->link_id) }}"
            placeholder="Enter the existing record ID"
        >

        <small id="banner-link-id-help">
            Enter the ID of the selected destination.
        </small>
    </div>

    {{-- Display Order --}}
    <div class="banner-field">
        <label for="sort_order">Display Order</label>

        <input
            id="sort_order"
            name="sort_order"
            type="number"
            min="0"
            step="1"
            value="{{ old('sort_order', $banner?->sort_order ?? 0) }}"
        >

        <small>Lower numbers appear first.</small>
    </div>

    {{-- Visibility --}}
    <div class="banner-field">
        <label>Visibility</label>

        <label class="banner-checkbox" for="status">
            <input
                id="status"
                type="checkbox"
                name="status"
                value="1"
                @checked((bool) old('status', $banner?->status ?? false))
            >

            <span>Show this banner on the homepage</span>
        </label>

        <small>Inactive banners should not be displayed to customers.</small>
    </div>

</div>

{{-- Form Actions --}}
<div class="banner-form-actions">
    @if (!request()->routeIs('admin.banners.index'))
        <a
            href="{{ route('admin.banners.index') }}"
            class="banner-btn light"
        >
            Cancel
        </a>

        <button type="submit" class="banner-btn">
            {{ $banner ? 'Save Changes' : 'Create Banner' }}
        </button>
    @endif
</div>

@push('styles')
<style>
    .banner-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 22px;
    }

    .banner-field {
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .banner-field.full {
        grid-column: 1 / -1;
    }

    .banner-field > label {
        color: #302720;
        font-size: 13px;
        font-weight: 700;
    }

    .banner-field .required {
        color: #c24135;
    }

    .banner-field .optional {
        color: #8b8580;
        font-size: 12px;
        font-weight: 400;
    }

    .banner-field input:not([type="checkbox"]),
    .banner-field select {
        box-sizing: border-box;
        width: 100%;
        min-height: 46px;
        padding: 11px 13px;
        border: 1px solid #e5dcd3;
        border-radius: 10px;
        background: #fff;
        color: #302720;
        font: inherit;
        font-size: 13px;
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    .banner-field input[type="file"] {
        padding: 9px;
        background: #fcfaf8;
    }

    .banner-field input:focus,
    .banner-field select:focus {
        outline: none;
        border-color: #6f4e37;
        box-shadow: 0 0 0 3px rgba(111, 78, 55, .12);
    }

    .banner-field small {
        color: #827970;
        font-size: 12px;
        line-height: 1.6;
    }

    .banner-field [hidden],
    #banner-image-preview[hidden] {
        display: none !important;
    }

    .banner-checkbox {
        display: flex;
        align-items: center;
        gap: 10px;
        min-height: 44px;
        padding: 10px 12px;
        border: 1px solid #e5dcd3;
        border-radius: 10px;
        background: #fcfaf8;
        cursor: pointer;
        font-weight: 500 !important;
    }

    .banner-checkbox input {
        width: 17px;
        height: 17px;
        margin: 0;
        accent-color: #6f4e37;
        flex-shrink: 0;
    }

    .banner-upload-preview {
        margin-top: 6px;
        padding: 12px;
        border: 1px solid #e9e0d8;
        border-radius: 12px;
        background: #fcfaf8;
    }

    .banner-upload-preview img {
        display: block;
        width: 100%;
        max-height: 280px;
        object-fit: contain;
        object-position: center;
        border-radius: 8px;
    }

    .banner-preview-caption {
        display: block;
        margin-top: 9px;
        color: #827970;
        font-size: 12px;
    }

    .banner-form-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 10px;
        margin-top: 26px;
        padding-top: 20px;
        border-top: 1px solid #eee6df;
    }

    .banner-alert.error {
        margin-bottom: 20px;
        padding: 14px 17px;
        border: 1px solid #f2d0cc;
        border-radius: 10px;
        background: #fff5f4;
        color: #a52c26;
        font-size: 13px;
    }

    .banner-alert.error ul {
        margin: 8px 0 0;
        padding-left: 20px;
    }

    @media (max-width: 650px) {
        .banner-form-grid {
            grid-template-columns: minmax(0, 1fr);
            gap: 18px;
        }

        .banner-field.full {
            grid-column: auto;
        }

        .banner-form-actions {
            flex-wrap: wrap;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .banner-field input,
        .banner-field select {
            transition: none;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const titleInput = document.getElementById('title');
    const form = titleInput ? titleInput.closest('form') : null;

    if (!form) return;

    const imageInput = form.querySelector('[name="image"]');
    const preview = form.querySelector('#banner-image-preview');
    const previewImg = form.querySelector('#banner-preview-img');
    const previewCaption = preview
        ? preview.querySelector('.banner-preview-caption')
        : null;

    const linkType = form.querySelector('[name="link_type"]');
    const linkId = form.querySelector('[name="link_id"]');
    const linkIdField = form.querySelector('#banner-link-id-field');
    const linkIdHelp = form.querySelector('#banner-link-id-help');

    const typesRequiringId = ['product', 'category', 'collection'];

    function updateDestinationField() {
        const type = linkType ? linkType.value : 'none';
        const needsId = typesRequiringId.includes(type);

        if (linkIdField) {
            linkIdField.hidden = !needsId;
        }

        if (linkId) {
            linkId.required = needsId;

            if (!needsId) {
                linkId.value = '';
            }
        }

        if (linkIdHelp) {
            const helpText = {
                product: 'Enter the ID of an existing product.',
                category: 'Enter the ID of an existing category.',
                collection: 'Enter the ID of an existing collection.',
                discount: 'No ID is needed. The app should show products with real discounts.',
                none: 'This banner will not navigate to another page.'
            };

            linkIdHelp.textContent =
                helpText[type] || 'Choose a destination type.';
        }
    }

    if (linkType) {
        linkType.addEventListener('change', updateDestinationField);
        updateDestinationField();
    }

    if (imageInput && preview && previewImg) {
        imageInput.addEventListener('change', function () {
            const file = this.files && this.files[0];

            if (!file) return;

            const allowedTypes = [
                'image/jpeg',
                'image/png',
                'image/webp'
            ];

            if (!allowedTypes.includes(file.type)) {
                window.alert('Please choose a JPG, PNG or WebP image.');
                this.value = '';
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                window.alert('The image must be 5 MB or smaller.');
                this.value = '';
                return;
            }

            if (previewImg.dataset.objectUrl) {
                URL.revokeObjectURL(previewImg.dataset.objectUrl);
            }

            const objectUrl = URL.createObjectURL(file);

            previewImg.src = objectUrl;
            previewImg.dataset.objectUrl = objectUrl;
            preview.hidden = false;

            if (previewCaption) {
                previewCaption.textContent = 'Selected image preview';
            }
        });
    }

    form.addEventListener('submit', function (event) {
        const type = linkType ? linkType.value : 'none';
        const needsId = typesRequiringId.includes(type);

        if (
            needsId &&
            linkId &&
            (
                !linkId.value ||
                !Number.isInteger(Number(linkId.value)) ||
                Number(linkId.value) < 1
            )
        ) {
            event.preventDefault();
            window.alert('Please enter a valid destination ID.');
            linkId.focus();
        }
    });
});
</script>
@endpush
