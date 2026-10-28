
@extends('admin.layouts.app')

@section('title', 'Banner Management')

@push('styles')
<style>
.banner-page {
    color: #24201e;
    width: 100%;
}

.banner-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 24px;
}

.banner-heading h1 {
    font-size: 27px;
    margin: 0 0 8px;
}

.banner-heading p {
    color: #8b8580;
    margin: 0;
    font-size: 14px;
}

.banner-btn {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    gap: 7px;
    padding: 11px 16px;
    border: 1px solid #6f4e37;
    border-radius: 9px;
    background: #6f4e37;
    color: #fff;
    text-decoration: none;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
}

.banner-btn:hover {
    background: #523925;
    color: #fff;
}

.banner-btn.light {
    background: #fff;
    color: #6f4e37;
}

.banner-btn.danger {
    background: #fff;
    color: #b42318;
    border-color: #f0c6c2;
}

.banner-btn:disabled {
    opacity: .65;
    cursor: wait;
}

.banner-alert {
    padding: 13px 16px;
    border-radius: 10px;
    margin-bottom: 18px;
    font-size: 14px;
}

.banner-alert.success {
    background: #edf8f0;
    color: #23663a;
}

.banner-alert.error {
    background: #fff0ef;
    color: #a52c26;
}

.banner-card {
    background: #fff;
    border: 1px solid #e6d9cb;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 18px rgba(44,30,23,.04);
}

.banner-table-wrap {
    width: 100%;
    overflow-x: auto;
}

.banner-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 760px;
}

.banner-table th {
    padding: 15px 17px;
    text-align: left;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .6px;
    color: #8b7565;
    background: #faf6f1;
    border-bottom: 1px solid #e6d9cb;
}

.banner-table td {
    padding: 16px 17px;
    border-bottom: 1px solid #f0e8e0;
    font-size: 13px;
    vertical-align: middle;
}

.banner-table tr:last-child td {
    border-bottom: 0;
}

.banner-table tbody tr:hover {
    background: #fdfbf8;
}

.banner-preview {
    width: 140px;
    height: 76px;
    display: block;
    object-fit: cover;
    border-radius: 9px;
    border: 1px solid #e6d9cb;
    background: #faf6f1;
}

.banner-title {
    font-weight: 700;
    margin-bottom: 5px;
}

.banner-muted {
    color: #8b8580;
    font-size: 12px;
}

.banner-status {
    display: inline-block;
    border-radius: 30px;
    padding: 6px 10px;
    font-size: 11px;
    font-weight: 700;
}

.banner-status.on {
    color: #23663a;
    background: #e7f6eb;
}

.banner-status.off {
    color: #75685e;
    background: #f1ece7;
}

.banner-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
    justify-content: flex-end;
}

.banner-actions .banner-btn {
    padding: 8px 10px;
    font-size: 12px;
}

.banner-empty {
    padding: 52px 20px;
    text-align: center;
}

.banner-empty h3 {
    margin: 0 0 8px;
    font-size: 18px;
}

.banner-empty p {
    margin: 0 0 18px;
    color: #8b8580;
    font-size: 13px;
}

.banner-pagination {
    padding: 16px;
    border-top: 1px solid #f0e8e0;
}

/* Add Banner modal - styled like Add Product */
.banner-modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 9990;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: rgba(28, 31, 39, .65);
    backdrop-filter: blur(4px);
}

.banner-modal-overlay.show {
    display: flex;
}

.banner-modal {
    display: flex;
    flex-direction: column;
    width: min(1000px, 100%);
    max-height: calc(100vh - 40px);
    background: #fff;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 25px 80px rgba(0,0,0,.24);
    animation: bannerModalIn .2s ease-out;
}

.banner-modal-header {
    flex-shrink: 0;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
    padding: 22px 24px;
    border-bottom: 1px solid #eadfd5;
}

.banner-modal-kicker {
    margin: 0 0 6px;
    color: #8b5e3c;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1.2px;
    text-transform: uppercase;
}

.banner-modal-header h2 {
    margin: 0 0 5px;
    font-size: 25px;
    color: #20232a;
}

.banner-modal-header p {
    margin: 0;
    font-size: 13px;
    color: #747986;
}

.banner-modal-close {
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    border: 1px solid #e7ded6;
    border-radius: 10px;
    background: #fff;
    color: #24201e;
    font-size: 23px;
    cursor: pointer;
}

.banner-modal-close:hover {
    background: #faf6f1;
}

.banner-modal-body {
    min-height: 0;
    overflow-y: auto;
    padding: 22px 24px;
}

.banner-modal-body .banner-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.banner-modal-body .banner-field {
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.banner-modal-body .banner-field.full {
    grid-column: 1 / -1;
}

.banner-modal-body .banner-field > label {
    font-size: 13px;
    font-weight: 700;
}

.banner-modal-body .banner-field input:not([type="checkbox"]),
.banner-modal-body .banner-field select {
    box-sizing: border-box;
    width: 100%;
    min-height: 44px;
    padding: 10px 12px;
    border: 1px solid #e1d4c8;
    border-radius: 9px;
    background: #fff;
    color: #24201e;
    font: inherit;
    font-size: 13px;
}

.banner-modal-body .banner-field input:focus,
.banner-modal-body .banner-field select:focus {
    outline: 2px solid #dbc5b2;
    border-color: #6f4e37;
}

.banner-modal-body .banner-field small {
    color: #8b8580;
    font-size: 12px;
    line-height: 1.5;
}

.banner-modal-body .banner-checkbox {
    display: flex;
    gap: 10px;
    align-items: center;
    font-weight: 500 !important;
}

.banner-modal-body .banner-checkbox input {
    accent-color: #6f4e37;
    width: 17px;
    height: 17px;
}

.banner-modal-body .banner-upload-preview {
    margin-top: 8px;
}

.banner-modal-body .banner-upload-preview img {
    display: block;
    width: 100%;
    max-height: 260px;
    object-fit: contain;
    object-position: left;
    background: #faf6f1;
    border: 1px solid #e6d9cb;
    border-radius: 10px;
}

.banner-modal-body .banner-field input[type="file"] {
    padding: 10px;
}

.banner-modal-body .banner-form-actions {
    display: none;
}

.banner-modal-footer {
    flex-shrink: 0;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    border-top: 1px solid #eadfd5;
    background: #fff;
}

.banner-confirm-overlay {
    position: fixed;
    inset: 0;
    z-index: 10000;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: rgba(28,31,39,.58);
    backdrop-filter: blur(3px);
}

.banner-confirm-overlay.show {
    display: flex;
}

.banner-confirm-box {
    width: min(420px, 100%);
    padding: 28px;
    border-radius: 18px;
    background: #fff;
    text-align: center;
    box-shadow: 0 20px 65px rgba(0,0,0,.2);
}

.banner-confirm-icon {
    width: 60px;
    height: 60px;
    margin: 0 auto 16px;
    display: grid;
    place-items: center;
    border-radius: 50%;
    background: #fff3d9;
    color: #946200;
    font-size: 27px;
    font-weight: 800;
}

.banner-confirm-box h3 {
    margin: 0 0 10px;
    font-size: 21px;
}

.banner-confirm-box p {
    margin: 0 0 22px;
    color: #81756b;
    font-size: 14px;
    line-height: 1.6;
}

.banner-confirm-actions {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 10px;
}

@keyframes bannerModalIn {
    from { opacity: 0; transform: translateY(10px) scale(.99); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

@media(max-width: 700px) {
    .banner-heading h1 { font-size: 23px; }
    .banner-preview { width: 112px; height: 65px; }
    .banner-modal-overlay { padding: 10px; }
    .banner-modal { max-height: calc(100vh - 20px); border-radius: 14px; }
    .banner-modal-header { padding: 17px; }
    .banner-modal-header h2 { font-size: 21px; }
    .banner-modal-body { padding: 17px; }
    .banner-modal-body .banner-form-grid { grid-template-columns: 1fr; }
    .banner-modal-body .banner-field.full { grid-column: auto; }
    .banner-modal-footer { padding: 13px 17px; }
}

@media(prefers-reduced-motion: reduce) {
    .banner-modal { animation: none; }
}
</style>
@endpush

@section('content')
<div class="banner-page">
    <div class="banner-heading">
        <div>
            <h1>Banner Management</h1>
            <p>Manage homepage banners, images and display order.</p>
        </div>

        <button type="button" class="banner-btn" id="openBannerModal">
            + Add Banner
        </button>
    </div>

    @if(session('success'))
        <div class="banner-alert success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="banner-alert error">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="banner-alert error">
            <strong>Please correct the following:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="banner-card">
        @if($banners->count())
            <div class="banner-table-wrap">
                <table class="banner-table">
                    <thead>
                    <tr>
                        <th>Image</th>
                        <th>Banner Details</th>
                        <th>Destination</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($banners as $banner)
                        <tr>
                            <td>
                                <img class="banner-preview"
                                     src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($banner->image) }}"
                                     alt="{{ $banner->title }}" loading="lazy">
                            </td>
                            <td>
                                <div class="banner-title">{{ $banner->title }}</div>
                                <div class="banner-muted">{{ $banner->subtitle ?: 'No subtitle' }}</div>
                            </td>
                            <td>
                                {{ $banner->link_type && $banner->link_type !== 'none' ? ucfirst($banner->link_type) : 'None' }}
                                @if($banner->link_id)
                                    <div class="banner-muted">ID: {{ $banner->link_id }}</div>
                                @endif
                            </td>
                            <td>{{ $banner->sort_order }}</td>
                            <td>
                                <span class="banner-status {{ $banner->status ? 'on' : 'off' }}">
                                    {{ $banner->status ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="banner-actions">
                                    <form method="POST" action="{{ route('admin.banners.toggle-status', $banner) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="banner-btn light" type="submit">
                                            {{ $banner->status ? 'Disable' : 'Enable' }}
                                        </button>
                                    </form>

                                    <a class="banner-btn light" href="{{ route('admin.banners.edit', $banner) }}">
                                        Edit
                                    </a>

                                    <form method="POST"
                                          action="{{ route('admin.banners.destroy', $banner) }}"
                                          onsubmit="return confirm('Delete this banner permanently?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="banner-btn danger" type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            @if($banners->hasPages())
                <div class="banner-pagination">{{ $banners->links() }}</div>
            @endif
        @else
            <div class="banner-empty">
                <h3>No banners yet</h3>
                <p>Add your first banner to manage the homepage slider.</p>
                <button type="button" class="banner-btn" id="openBannerModalEmpty">
                    + Create First Banner
                </button>
            </div>
        @endif
    </div>
</div>

{{-- Add Banner modal --}}
<div class="banner-modal-overlay" id="bannerModalOverlay"
     role="dialog" aria-modal="true" aria-labelledby="bannerModalTitle">
    <div class="banner-modal">
        <div class="banner-modal-header">
            <div>
                <div class="banner-modal-kicker">Banner Setup</div>
                <h2 id="bannerModalTitle">Add Banner</h2>
                <p>Upload a banner and configure how it appears on your homepage.</p>
            </div>
            <button type="button" class="banner-modal-close" id="closeBannerModal"
                    aria-label="Close modal">&times;</button>
        </div>

        <form id="bannerCreateForm" method="POST"
              action="{{ route('admin.banners.store') }}"
              enctype="multipart/form-data" novalidate>
            @csrf

            <div class="banner-modal-body">
                @include('admin.banners._form', ['banner' => null])
            </div>

            <div class="banner-modal-footer">
                <button type="button" class="banner-btn light" id="cancelBannerModal">
                    Cancel
                </button>
                <button type="submit" class="banner-btn" id="submitBannerButton">
                    Create Banner
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Confirmation popup --}}
<div class="banner-confirm-overlay" id="bannerConfirmOverlay"
     role="dialog" aria-modal="true" aria-labelledby="bannerConfirmTitle">
    <div class="banner-confirm-box">
        <div class="banner-confirm-icon" id="bannerConfirmIcon">!</div>
        <h3 id="bannerConfirmTitle">Create This Banner?</h3>
        <p id="bannerConfirmMessage">Are you sure you want to create this banner?</p>
        <div class="banner-confirm-actions">
            <button type="button" class="banner-btn light" id="cancelBannerConfirm">
                Review
            </button>
            <button type="button" class="banner-btn" id="confirmBannerCreate">
                Yes, Create
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('bannerModalOverlay');
    const form = document.getElementById('bannerCreateForm');
    const confirmOverlay = document.getElementById('bannerConfirmOverlay');
    const confirmButton = document.getElementById('confirmBannerCreate');
    const confirmCancel = document.getElementById('cancelBannerConfirm');
    const submitButton = document.getElementById('submitBannerButton');

    if (!modal || !form || !confirmOverlay) {
        console.error('Banner modal could not initialize.');
        return;
    }

    function openModal() {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        const titleInput = form.querySelector('[name="title"]');
        if (titleInput) titleInput.focus();
    }

    function closeModal() {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }

    function closeConfirmation() {
        confirmOverlay.classList.remove('show');
    }

    document.getElementById('openBannerModal')?.addEventListener('click', openModal);
    document.getElementById('openBannerModalEmpty')?.addEventListener('click', openModal);
    document.getElementById('closeBannerModal')?.addEventListener('click', closeModal);
    document.getElementById('cancelBannerModal')?.addEventListener('click', closeModal);

    modal.addEventListener('click', function (event) {
        if (event.target === modal) closeModal();
    });

    confirmCancel.addEventListener('click', closeConfirmation);

    confirmOverlay.addEventListener('click', function (event) {
        if (event.target === confirmOverlay) closeConfirmation();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            if (confirmOverlay.classList.contains('show')) {
                closeConfirmation();
            } else if (modal.classList.contains('show')) {
                closeModal();
            }
        }
    });

    // Image preview and validation
    const imageInput = form.querySelector('[name="image"]');
    const imagePreview = document.getElementById('banner-image-preview');
    const previewImg = document.getElementById('banner-preview-img');
    const linkType = form.querySelector('[name="link_type"]');
    const linkId = form.querySelector('[name="link_id"]');
    const linkIdField = document.getElementById('banner-link-id-field');

    if (imageInput) {
        imageInput.addEventListener('change', function () {
            const file = this.files && this.files[0];
            if (!file) return;

            const allowed = ['image/jpeg', 'image/png', 'image/webp'];

            if (!allowed.includes(file.type)) {
                window.alert('Please choose a JPG, PNG or WebP image.');
                this.value = '';
                if (imagePreview) imagePreview.style.display = 'none';
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                window.alert('The image must be 5 MB or smaller.');
                this.value = '';
                if (imagePreview) imagePreview.style.display = 'none';
                return;
            }

            if (previewImg && imagePreview) {
                if (previewImg.dataset.objectUrl) {
                    URL.revokeObjectURL(previewImg.dataset.objectUrl);
                }
                const objectUrl = URL.createObjectURL(file);
                previewImg.src = objectUrl;
                previewImg.dataset.objectUrl = objectUrl;
                imagePreview.style.display = 'block';
            }
        });
    }

    function updateDestinationField() {
        const type = linkType ? linkType.value : 'none';
        const needsId = ['product', 'category', 'collection'].includes(type);

        if (linkIdField) {
            linkIdField.style.display = needsId ? '' : 'none';
        }

        if (linkId) {
            linkId.required = needsId;
            if (!needsId) linkId.value = '';
        }

        const linkIdHelp = form.querySelector('#banner-link-id-help');
        if (linkIdHelp) {
            const helpText = {
                product: 'Enter the ID of an existing product.',
                category: 'Enter the ID of an existing category.',
                collection: 'Enter the ID of an existing collection.',
                discount: 'No ID is required. The app should display products with real discounts.',
                none: 'This banner will not navigate to another page.'
            };
            linkIdHelp.textContent = helpText[type] || 'Choose a destination type.';
        }
    }

    linkType?.addEventListener('change', updateDestinationField);
    updateDestinationField();

    function showValidationError(title, message, focusElement) {
        document.getElementById('bannerConfirmTitle').textContent = title;
        document.getElementById('bannerConfirmMessage').textContent = message;
        document.getElementById('bannerConfirmIcon').textContent = '!';
        confirmCancel.textContent = 'Close';
        confirmButton.style.display = 'none';
        confirmOverlay.classList.add('show');

        confirmCancel.onclick = function () {
            closeConfirmation();
            confirmButton.style.display = '';
            confirmCancel.textContent = 'Review';
            if (focusElement) focusElement.focus();
        };
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        const titleInput = form.querySelector('[name="title"]');
        const file = imageInput && imageInput.files ? imageInput.files[0] : null;

        if (!titleInput || !titleInput.value.trim()) {
            showValidationError(
                'Title Required',
                'Please enter a title for this banner.',
                titleInput
            );
            return;
        }

        if (!file) {
            showValidationError(
                'Image Required',
                'Please select a banner image before continuing.',
                imageInput
            );
            return;
        }

        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
            showValidationError(
                'Invalid Image',
                'Please choose a JPG, PNG or WebP image.',
                imageInput
            );
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            showValidationError(
                'Image Too Large',
                'The image must be 5 MB or smaller.',
                imageInput
            );
            return;
        }

        const needsDestinationId = ['product', 'category', 'collection'].includes(
            linkType ? linkType.value : 'none'
        );

        if (needsDestinationId && linkId &&
            (!linkId.value || !Number.isInteger(Number(linkId.value)) || Number(linkId.value) < 1)) {
            showValidationError(
                'Destination ID Required',
                'Enter a valid destination ID for the selected destination type.',
                linkId
            );
            return;
        }

        document.getElementById('bannerConfirmTitle').textContent = 'Create This Banner?';
        document.getElementById('bannerConfirmMessage').textContent =
            'Please confirm that the title and image are correct. The banner will be saved to your database.';
        document.getElementById('bannerConfirmIcon').textContent = '?';
        confirmCancel.textContent = 'Review';
        confirmButton.style.display = '';
        confirmOverlay.classList.add('show');

        confirmCancel.onclick = closeConfirmation;
        confirmButton.onclick = function () {
            closeConfirmation();
            submitButton.disabled = true;
            submitButton.textContent = 'Creating...';

            // Submit the multipart form to the existing Laravel store route.
            HTMLFormElement.prototype.submit.call(form);
        };
    });

    // Reopen the modal if Laravel redirects back with validation errors.
    @if($errors->any())
        openModal();
    @endif

    // Show a success alert after redirect, when Laravel provides a flash message.
    @if(session('success'))
        document.getElementById('bannerConfirmTitle').textContent = 'Success';
        document.getElementById('bannerConfirmMessage').textContent = @json(session('success'));
        document.getElementById('bannerConfirmIcon').textContent = '✓';
        confirmCancel.textContent = 'Close';
        confirmButton.style.display = 'none';
        confirmOverlay.classList.add('show');
        confirmCancel.onclick = function () {
            closeConfirmation();
            confirmButton.style.display = '';
            confirmCancel.textContent = 'Review';
        };
    @endif

    @if(session('error'))
        document.getElementById('bannerConfirmTitle').textContent = 'Unable to Create Banner';
        document.getElementById('bannerConfirmMessage').textContent = @json(session('error'));
        document.getElementById('bannerConfirmIcon').textContent = '!';
        confirmCancel.textContent = 'Close';
        confirmButton.style.display = 'none';
        confirmOverlay.classList.add('show');
        confirmCancel.onclick = function () {
            closeConfirmation();
            confirmButton.style.display = '';
            confirmCancel.textContent = 'Review';
        };
    @endif
});
</script>
@endpush
