
@extends('admin.layouts.app')

@section('title', 'Edit Banner')

@push('styles')
<style>
.banner-editor { max-width: 900px; margin: 0 auto; color: #24201e; }
.banner-editor h1 { margin: 0 0 8px; font-size: 26px; }
.banner-editor .intro { color: #8b8580; margin-bottom: 24px; font-size: 14px; }
.banner-editor-card { background: #fff; border: 1px solid #e6d9cb; border-radius: 16px; padding: 24px; }
.banner-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.banner-field { min-width: 0; display: flex; flex-direction: column; gap: 8px; }
.banner-field.full { grid-column: 1 / -1; }
.banner-field > label { font-size: 13px; font-weight: 700; }
.banner-field input:not([type=checkbox]), .banner-field select { width: 100%; min-height: 44px; padding: 10px 12px; border: 1px solid #e1d4c8; border-radius: 9px; background: #fff; color: #24201e; font: inherit; font-size: 13px; }
.banner-field input:focus, .banner-field select:focus { outline: 2px solid #dbc5b2; border-color: #6f4e37; }
.banner-field small { color: #8b8580; font-size: 12px; line-height: 1.5; }
.banner-checkbox { display: flex; gap: 10px; align-items: center; font-weight: 500 !important; }
.banner-checkbox input { accent-color: #6f4e37; width: 17px; height: 17px; }
.banner-upload-preview { margin-top: 8px; }
.banner-upload-preview img { display: block; width: 100%; max-height: 280px; object-fit: contain; object-position: left; background: #faf6f1; border: 1px solid #e6d9cb; border-radius: 10px; }
.banner-form-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 26px; padding-top: 20px; border-top: 1px solid #f0e8e0; }
.banner-btn { display: inline-flex; justify-content: center; align-items: center; padding: 11px 16px; border: 1px solid #6f4e37; border-radius: 9px; background: #6f4e37; color: #fff; text-decoration: none; font-size: 13px; font-weight: 700; cursor: pointer; }
.banner-btn:hover { background: #523925; color: #fff; }
.banner-btn.light { background: #fff; color: #6f4e37; }
.banner-alert.error { padding: 12px 15px; border-radius: 9px; margin-bottom: 18px; background: #fff0ef; color: #a52c26; }
@media(max-width:650px) { .banner-form-grid { grid-template-columns: 1fr; } .banner-field.full { grid-column: auto; } .banner-editor-card { padding: 16px; } }
</style>
@endpush

@section('content')
<div class="banner-editor">
    <h1>Edit Banner</h1>
    <p class="intro">Update banner details, image, destination and visibility.</p>

    <div class="banner-editor-card">
        <form method="POST"
              action="{{ route('admin.banners.update', $banner) }}"
              enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('admin.banners._form', ['banner' => $banner])
        </form>
    </div>
</div>
@endsection
