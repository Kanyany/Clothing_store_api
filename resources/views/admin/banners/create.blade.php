
@extends('admin.layouts.app')

@section('title', 'Create Banner')

@push('styles')
<style>
.banner-editor {
    max-width: 900px;
    margin: 0 auto;
    color: #24201e;
}
.banner-editor h1 {
    margin: 0 0 8px;
    font-size: 26px;
}
.banner-editor .intro {
    color: #8b8580;
    margin: 0 0 24px;
    font-size: 14px;
}
.banner-editor-card {
    background: #fff;
    border: 1px solid #e6d9cb;
    border-radius: 16px;
    padding: 24px;
}
.banner-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
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
    font-size: 13px;
    font-weight: 700;
}
.banner-field input:not([type="checkbox"]),
.banner-field select {
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
.banner-field input:focus,
.banner-field select:focus {
    outline: 2px solid #dbc5b2;
    border-color: #6f4e37;
}
.banner-field small {
    color: #8b8580;
    font-size: 12px;
    line-height: 1.5;
}
.banner-checkbox {
    display: flex;
    gap: 10px;
    align-items: center;
    font-weight: 500 !important;
}
.banner-checkbox input {
    accent-color: #6f4e37;
    width: 17px;
    height: 17px;
}
.banner-upload-preview {
    margin-top: 8px;
}
.banner-upload-preview img {
    display: block;
    width: 100%;
    max-height: 280px;
    object-fit: contain;
    object-position: left;
    background: #faf6f1;
    border: 1px solid #e6d9cb;
    border-radius: 10px;
}
.banner-form-actions {
    display: flex;
    justify-content: flex-end;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 26px;
    padding-top: 20px;
    border-top: 1px solid #f0e8e0;
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
.banner-btn:disabled {
    opacity: .65;
    cursor: wait;
}

/* Popup overlay */
.banner-alert-overlay {
    position: fixed;
    inset: 0;
    z-index: 999999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: rgba(30, 24, 20, .55);
    backdrop-filter: blur(4px);
}
.banner-alert-overlay.show {
    display: flex;
}
.banner-alert-modal {
    width: min(430px, 100%);
    padding: 30px;
    border: 1px solid #e6d9cb;
    border-radius: 20px;
    background: #fff;
    box-shadow: 0 24px 80px rgba(0, 0, 0, .22);
    text-align: center;
    animation: bannerPopupIn .2s ease-out;
}
.banner-alert-icon {
    width: 66px;
    height: 66px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 18px;
    border-radius: 50%;
    background: #fff3d9;
    color: #946200;
    font-size: 30px;
    font-weight: 700;
}
.banner-alert-icon.error {
    background: #fff0ef;
    color: #b42318;
}
.banner-alert-icon.success {
    background: #e8f7ed;
    color: #237541;
}
.banner-alert-modal h2 {
    margin: 0 0 10px;
    font-size: 22px;
    color: #29231e;
}
.banner-alert-modal p {
    margin: 0 0 24px;
    color: #81756b;
    font-size: 14px;
    line-height: 1.7;
    overflow-wrap: anywhere;
}
.banner-alert-actions {
    display: flex;
    justify-content: center;
    gap: 10px;
    flex-wrap: wrap;
}
.banner-alert-actions button {
    min-width: 120px;
}
@keyframes bannerPopupIn {
    from {
        opacity: 0;
        transform: translateY(12px) scale(.97);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}
@media(max-width: 650px) {
    .banner-form-grid {
        grid-template-columns: 1fr;
    }
    .banner-field.full {
        grid-column: auto;
    }
    .banner-editor-card {
        padding: 16px;
    }
    .banner-alert-modal {
        padding: 24px 18px;
    }
}
@media(prefers-reduced-motion: reduce) {
    .banner-alert-modal {
        animation: none;
    }
}
</style>
@endpush

@section('content')
<div class="banner-editor">
    <h1>Create Banner</h1>
    <p class="intro">
        Add an image and configure how it appears on the homepage.
    </p>

    @if(session('success'))
        <div role="status" style="margin-bottom:16px;color:#237541">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div role="alert" style="margin-bottom:16px;color:#b42318">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div role="alert" style="margin-bottom:16px;color:#b42318">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="banner-editor-card">
        <form
            id="bannerCreateForm"
            method="POST"
            action="{{ route('admin.banners.store') }}"
            enctype="multipart/form-data"
            novalidate
        >
            @csrf

            @include('admin.banners._form', ['banner' => null])
        </form>
    </div>
</div>

{{-- Confirmation popup --}}
<div
    id="bannerAlertOverlay"
    class="banner-alert-overlay"
    role="dialog"
    aria-modal="true"
    aria-labelledby="bannerAlertTitle"
    aria-describedby="bannerAlertMessage"
>
    <div class="banner-alert-modal">
        <div id="bannerAlertIcon" class="banner-alert-icon">!</div>

        <h2 id="bannerAlertTitle">Confirm Create Banner</h2>

        <p id="bannerAlertMessage">
            Are you sure you want to create this banner?
        </p>

        <div id="bannerAlertActions" class="banner-alert-actions">
            <button type="button" id="bannerAlertCancel"
                    class="banner-btn light">
                Cancel
            </button>

            <button type="button" id="bannerAlertConfirm"
                    class="banner-btn">
                Yes, Create
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    function initializeBannerForm() {
        const form = document.getElementById('bannerCreateForm');
        const overlay = document.getElementById('bannerAlertOverlay');
        const alertTitle = document.getElementById('bannerAlertTitle');
        const alertMessage = document.getElementById('bannerAlertMessage');
        const alertIcon = document.getElementById('bannerAlertIcon');
        const alertActions = document.getElementById('bannerAlertActions');

        if (!form || !overlay || !alertTitle ||
            !alertMessage || !alertIcon || !alertActions) {
            console.error('Banner confirmation popup could not initialize.');
            return;
        }

        if (form.dataset.alertInitialized === '1') return;
        form.dataset.alertInitialized = '1';

        let submitting = false;
        let confirmAction = null;

        function closeAlert() {
            overlay.classList.remove('show');
            confirmAction = null;
        }

        function showAlert(options) {
            alertTitle.textContent = options.title;
            alertMessage.textContent = options.message;

            alertIcon.className = 'banner-alert-icon ' + (options.type || 'warning');
            alertIcon.textContent =
                options.type === 'success' ? '✓' :
                options.type === 'error' ? '×' : '!';

            alertActions.replaceChildren();
            confirmAction = options.onConfirm || null;

            if (options.cancelText) {
                const cancel = document.createElement('button');
                cancel.type = 'button';
                cancel.className = 'banner-btn light';
                cancel.textContent = options.cancelText;
                cancel.addEventListener('click', closeAlert);
                alertActions.appendChild(cancel);
            }

            const confirm = document.createElement('button');
            confirm.type = 'button';
            confirm.className = 'banner-btn';
            confirm.textContent = options.confirmText || 'OK';

            confirm.addEventListener('click', function () {
                const action = confirmAction;
                closeAlert();

                if (typeof action === 'function') {
                    action();
                }
            });

            alertActions.appendChild(confirm);
            overlay.classList.add('show');
            confirm.focus();
        }

        function validateForm() {
            const titleInput = form.querySelector('[name="title"]');
            const imageInput = form.querySelector('[name="image"]');
            const linkType = form.querySelector('[name="link_type"]');
            const linkId = form.querySelector('[name="link_id"]');

            if (!titleInput || !titleInput.value.trim()) {
                showAlert({
                    title: 'Title Required',
                    message: 'Please enter a title for this banner.',
                    type: 'error',
                    confirmText: 'OK',
                    onConfirm: function () {
                        titleInput && titleInput.focus();
                    }
                });
                return false;
            }

            if (!imageInput || !imageInput.files || imageInput.files.length === 0) {
                showAlert({
                    title: 'Image Required',
                    message: 'Please select a banner image before continuing.',
                    type: 'error',
                    confirmText: 'Choose Image',
                    onConfirm: function () {
                        imageInput && imageInput.focus();
                    }
                });
                return false;
            }

            const file = imageInput.files[0];
            const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

            if (!allowedTypes.includes(file.type)) {
                showAlert({
                    title: 'Invalid Image',
                    message: 'Please select a JPG, PNG or WebP image.',
                    type: 'error'
                });
                return false;
            }

            if (file.size > 5 * 1024 * 1024) {
                showAlert({
                    title: 'Image Too Large',
                    message: 'The image must be 5 MB or smaller.',
                    type: 'error'
                });
                return false;
            }

            if (linkType && linkType.value !== 'none' &&
                linkId && (!linkId.value || Number(linkId.value) < 1)) {
                showAlert({
                    title: 'Destination ID Required',
                    message: 'Enter a valid existing record ID, or choose No destination.',
                    type: 'error',
                    confirmText: 'OK',
                    onConfirm: function () {
                        linkId.focus();
                    }
                });
                return false;
            }

            return true;
        }

        form.addEventListener('submit', function (event) {
            if (submitting) return;

            event.preventDefault();

            if (!validateForm()) return;

            showAlert({
                title: 'Create This Banner?',
                message: 'Please confirm that the title and image are correct. This will save the banner to your database.',
                type: 'warning',
                cancelText: 'Review',
                confirmText: 'Yes, Create',
                onConfirm: function () {
                    if (submitting) return;
                    submitting = true;

                    const submitButton = form.querySelector('[type="submit"]');
                    if (submitButton) {
                        submitButton.disabled = true;
                        submitButton.textContent = 'Creating...';
                    }

                    // Submit to Laravel without triggering the confirmation again.
                    HTMLFormElement.prototype.submit.call(form);
                }
            });
        });

        overlay.addEventListener('click', function (event) {
            if (event.target === overlay) closeAlert();
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && overlay.classList.contains('show')) {
                closeAlert();
            }
        });

        // Display Laravel validation feedback in the popup after redirect.
        const serverError = @json($errors->first());
        const sessionError = @json(session('error'));
        const sessionSuccess = @json(session('success'));

        if (serverError) {
            showAlert({
                title: 'Please Check Your Information',
                message: serverError,
                type: 'error'
            });
        } else if (sessionError) {
            showAlert({
                title: 'Unable to Create Banner',
                message: sessionError,
                type: 'error'
            });
        } else if (sessionSuccess) {
            showAlert({
                title: 'Banner Created Successfully',
                message: sessionSuccess,
                type: 'success'
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeBannerForm);
    } else {
        initializeBannerForm();
    }
})();
</script>
@endpush
