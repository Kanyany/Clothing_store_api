@extends('admin.layouts.app')

@section('title', 'Categories')
@section('page-title', 'Categories')

@section('content')
<style>
:root {
    --cat-blue:#2563eb;
    --cat-blue-dark:#1d4ed8;
    --cat-red:#dc2626;
    --cat-green:#16a34a;
    --cat-gray:#6b7280;
    --cat-border:#e5e7eb;
    --cat-bg:#f8fafc;
    --cat-text:#111827;
}
.categories-page {
    padding:24px;
    max-width:1400px;
    margin:0 auto;
    color:var(--cat-text);
}
.categories-header {
    display:flex;
    align-items:flex-end;
    justify-content:space-between;
    gap:20px;
    margin-bottom:22px;
}
.categories-header h1 {
    margin:0;
    font-size:28px;
    font-weight:800;
    letter-spacing:-.02em;
}
.categories-header p {
    margin:6px 0 0;
    color:var(--cat-gray);
}
.cat-eyebrow {
    font-size:11px;
    font-weight:800;
    letter-spacing:.12em;
    color:var(--cat-blue);
    margin-bottom:5px;
}
.cat-primary {
    border:0;
    background:var(--cat-blue);
    color:#fff;
    border-radius:9px;
    padding:11px 16px;
    font-weight:700;
    cursor:pointer;
}
.cat-primary:hover {
    background:var(--cat-blue-dark);
}
.cat-alert {
    padding:12px 15px;
    border-radius:9px;
    margin-bottom:16px;
    font-weight:700;
    border:1px solid;
}
.cat-alert.success {
    background:#ecfdf5;
    border-color:#86efac;
    color:#166534;
}
.cat-alert.error {
    background:#fef2f2;
    border-color:#fca5a5;
    color:#b91c1c;
}

.js-auto-alert {
    transition: opacity .25s ease, transform .25s ease;
}

.js-auto-alert.is-hiding {
    opacity: 0;
    transform: translateY(-4px);
}

.btn-save:disabled,
.cat-tool-btn:disabled {
    opacity: .65;
    cursor: wait;
}
.cat-stats {
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:12px;
    margin-bottom:18px;
}
.cat-stat {
    background:#fff;
    border:1px solid var(--cat-border);
    border-radius:12px;
    padding:15px;
}
.cat-stat span {
    display:block;
    color:var(--cat-gray);
    font-size:12px;
    font-weight:600;
}
.cat-stat strong {
    display:block;
    font-size:23px;
    margin-top:4px;
}
.cat-toolbar {
    display:flex;
    gap:10px;
    align-items:center;
    background:#fff;
    border:1px solid var(--cat-border);
    border-radius:12px;
    padding:12px;
    margin-bottom:20px;
}
.cat-search {
    flex:1;
    min-width:180px;
    border:1px solid #d1d5db;
    border-radius:8px;
    padding:10px 12px;
    font-size:14px;
    outline:none;
}
.cat-search:focus,.cat-select:focus {
    border-color:var(--cat-blue);
    box-shadow:0 0 0 3px #dbeafe;
}
.cat-select {
    border:1px solid #d1d5db;
    border-radius:8px;
    padding:10px 12px;
    background:#fff;
}
.cat-tool-btn {
    border:1px solid #d1d5db;
    background:#fff;
    border-radius:8px;
    padding:10px 14px;
    font-weight:700;
    cursor:pointer;
}
.cat-tool-btn.blue {
    background:var(--cat-blue);
    border-color:var(--cat-blue);
    color:#fff;
}
.cat-tool-btn:hover {
    border-color:var(--cat-blue);
}
.cat-section-title {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin:0 0 12px;
}
.cat-section-title h2 {
    font-size:17px;
    margin:0;
}
.cat-section-title span {
    font-size:12px;
    color:var(--cat-gray);
}
.root-grid {
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:16px;
}
.root-card {
    background:#fff;
    border:1px solid var(--cat-border);
    border-radius:14px;
    overflow:hidden;
    transition:.15s;
}
.root-card:hover {
    border-color:#bfdbfe;
    box-shadow:0 8px 24px rgba(15,23,42,.07);
}
.root-image {
    height:150px;
    background:#f3f4f6;
    display:flex;
    align-items:center;
    justify-content:center;
    overflow:hidden;
}
.root-image {
    aspect-ratio: 16 / 9;
}
.root-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.root-placeholder {
    font-size:42px;
    font-weight:800;
    color:#94a3b8;
}
.root-body {
    padding:15px;
}
.root-top {
    display:flex;
    justify-content:space-between;
    gap:10px;
    align-items:flex-start;
}
.root-name {
    font-size:18px;
    font-weight:800;
}
.root-meta {
    font-size:12px;
    color:var(--cat-gray);
    margin-top:4px;
}
.status {
    font-size:12px;
    font-weight:800;
    display:inline-flex;
    align-items:center;
    gap:5px;
}
.status i {
    width:7px;
    height:7px;
    border-radius:50%;
    display:block;
}
.status.active {
    color:var(--cat-green);
}
.status.active i {
    background:var(--cat-green);
}
.status.inactive {
    color:var(--cat-red);
}
.status.inactive i {
    background:var(--cat-red);
}
.root-actions {
    display:flex;
    gap:7px;
    margin-top:14px;
}
.cat-btn {
    border:1px solid #d1d5db;
    background:#fff;
    border-radius:7px;
    padding:7px 10px;
    font-size:12px;
    font-weight:700;
    cursor:pointer;
}
.cat-btn.blue {
    color:var(--cat-blue);
    border-color:#93c5fd;
}
.cat-btn.red {
    color:var(--cat-red);
    border-color:#fca5a5;
}
.cat-btn.green {
    color:var(--cat-green);
    border-color:#86efac;
}
.cat-btn:disabled {
    opacity:.4;
    cursor:not-allowed;
}
.children-panel {
    display:none;
    margin-top:18px;
    background:#fff;
    border:1px solid var(--cat-border);
    border-radius:14px;
    padding:18px;
}
.children-panel.show {
    display:block;
}
.children-header {
    display:flex;
    align-items:center;
    justify-content:space-between;
    border-bottom:1px solid var(--cat-border);
    padding-bottom:13px;
    margin-bottom:13px;
}
.children-header h3 {
    margin:0;
    font-size:18px;
}
.back-btn {
    border:0;
    background:#eff6ff;
    color:var(--cat-blue);
    font-weight:800;
    border-radius:7px;
    padding:8px 11px;
    cursor:pointer;
}
.child-list {
    display:grid;
    gap:10px;
}
.child-item {
    border:1px solid var(--cat-border);
    border-radius:10px;
    padding:12px;
    background:#fff;
}
.child-row {
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:15px;
}
.child-main {
    display:flex;
    align-items:center;
    gap:10px;
    min-width:0;
}
.child-thumb {
    width:44px;
    height:44px;
    border-radius:8px;
    background:#f1f5f9;
    object-fit:cover;
    flex:0 0 44px;
}
.child-placeholder {
    width:44px;
    height:44px;
    border-radius:8px;
    background:#f1f5f9;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:800;
    color:#94a3b8;
}
.child-name {
    font-weight:800;
}
.child-sub {
    font-size:12px;
    color:var(--cat-gray);
    margin-top:3px;
}
.grandchildren {
    margin:10px 0 0 54px;
    padding-left:14px;
    border-left:2px solid #e5e7eb;
    display:grid;
    gap:7px;
}
.grandchild {
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
    padding:8px 10px;
    background:#f8fafc;
    border-radius:7px;
}
.grandchild-name {
    font-weight:700;
    font-size:13px;
}
.empty {
    background:#fff;
    border:1px dashed #cbd5e1;
    border-radius:12px;
    padding:35px;
    text-align:center;
    color:var(--cat-gray);
}
.modal-backdrop {
    position:fixed;
    inset:0;
    background:rgba(15,23,42,.55);
    display:none;
    align-items:center;
    justify-content:center;
    padding:20px;
    z-index:9999;
}
.modal-backdrop.show {
    display:flex;
}
.modal {
    background:#fff;
    width:min(620px,100%);
    max-height:90vh;
    overflow:auto;
    border-radius:14px;
    box-shadow:0 25px 60px rgba(0,0,0,.22);
}
.modal-head {
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    padding:19px 20px;
    border-bottom:1px solid var(--cat-border);
}
.modal-head h3 {
    margin:0;
    font-size:19px;
}
.modal-head p {
    margin:4px 0 0;
    color:var(--cat-gray);
    font-size:12px;
}
.modal-close {
    border:0;
    background:#f3f4f6;
    border-radius:7px;
    font-size:20px;
    width:34px;
    height:34px;
    cursor:pointer;
}
.modal-body {
    padding:20px;
}
.form-grid {
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:15px;
}
.field {
    display:flex;
    flex-direction:column;
    gap:6px;
}
.field.full {
    grid-column:1/-1;
}
.field label {
    font-size:12px;
    font-weight:800;
}
.field input,.field select,.field textarea {
    width:100%;
    box-sizing:border-box;
    border:1px solid #d1d5db;
    border-radius:8px;
    padding:10px 11px;
    font:inherit;
    outline:none;
}
.field input:focus,.field select:focus,.field textarea:focus {
    border-color:var(--cat-blue);
    box-shadow:0 0 0 3px #dbeafe;
}
.field textarea {
    min-height:90px;
    resize:vertical;
}
.image-preview {
    display:none;
    margin-top:8px;
    border:1px solid var(--cat-border);
    border-radius:9px;
    padding:8px;
    background:#f8fafc;
}
.image-preview.show {
    display:block;
}
.image-preview img {
    width:100%;
    height:150px;
    object-fit:contain;
    border-radius:6px;
    background:#fff;
}
.image-meta {
    font-size:11px;
    color:var(--cat-gray);
    margin-top:6px;
}
.error-text {
    color:var(--cat-red);
    font-size:11px;
    font-weight:700;
}
.modal-foot {
    display:flex;
    justify-content:flex-end;
    gap:9px;
    padding:15px 20px;
    border-top:1px solid var(--cat-border);
}
.btn-cancel {
    border:1px solid #d1d5db;
    background:#fff;
    border-radius:8px;
    padding:10px 15px;
    font-weight:700;
    cursor:pointer;
}
.btn-save {
    border:0;
    background:var(--cat-blue);
    color:#fff;
    border-radius:8px;
    padding:10px 16px;
    font-weight:800;
    cursor:pointer;
}
.confirm-icon {
    width:44px;
    height:44px;
    border-radius:50%;
    background:#fee2e2;
    color:var(--cat-red);
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:900;
    font-size:20px;
    margin-bottom:12px;
}
@media(max-width:900px) {
    .root-grid {
        grid-template-columns:repeat(2,minmax(0,1fr));
    }
}
@media(max-width:650px) {
    .categories-page {
        padding:15px;
    }
    .categories-header {
        align-items:stretch;
        flex-direction:column;
    }
    .cat-stats {
        grid-template-columns:1fr;
    }
    .cat-toolbar {
        flex-wrap:wrap;
    }
    .cat-search {
        flex-basis:100%;
    }
    .root-grid {
        grid-template-columns:1fr;
    }
    .form-grid {
        grid-template-columns:1fr;
    }
    .field.full {
        grid-column:auto;
    }
    .child-row {
        align-items:flex-start;
        flex-direction:column;
    }
    .root-actions {
        flex-wrap:wrap;
    }
}
/* =========================================================
CATEGORY FINAL THEME
Brown + Clear Typography + Better Images
========================================================= */
:root {
    --cat-primary: #6F4E37;
    --cat-primary-dark: #5A3F2D;
    --cat-text: #111827;
    --cat-muted: #64748B;
    --cat-green: #16A34A;
    --cat-red: #DC2626;
    --cat-border: #E5E7EB;
    --cat-bg: #F8FAFC;
}
/* =========================================================
GLOBAL TEXT
========================================================= */
.categories-page {
    color: #111827;
    font-family: "Segoe UI", Arial, Helvetica, sans-serif;
    -webkit-font-smoothing: antialiased;
    text-rendering: optimizeLegibility;
}
.categories-page h1,
.categories-page h2,
.categories-page h3,
.categories-page strong,
.categories-page b {
    color: #111827;
}
/* =========================================================
HEADER
========================================================= */
.categories-header h1 {
    color: #111827 !important;
    font-size: 30px;
    font-weight: 800;
}
.categories-header p {
    color: #64748B !important;
    font-size: 15px;
}
.cat-eyebrow {
    color: #6F4E37 !important;
}
/* =========================================================
PRIMARY BUTTONS
========================================================= */
.add-category-btn,
.cat-primary,
.cat-tool-btn.blue,
.btn-save {
    background: #6F4E37 !important;
    border-color: #6F4E37 !important;
    color: #FFFFFF !important;
}
.add-category-btn:hover,
.cat-primary:hover,
.cat-tool-btn.blue:hover,
.btn-save:hover {
    background: #5A3F2D !important;
    border-color: #5A3F2D !important;
}
/* =========================================================
SEARCH
========================================================= */
.cat-search,
.cat-select {
    color: #111827 !important;
    font-size: 15px;
}
.cat-search::placeholder {
    color: #64748B;
}
.cat-search:focus,
.cat-select:focus {
    border-color: #6F4E37 !important;
    box-shadow: 0 0 0 3px rgba(111, 78, 55, 0.14) !important;
    outline: none;
}
/* =========================================================
SECTION TITLE
========================================================= */
.cat-section-title h2 {
    color: #111827 !important;
    font-size: 20px;
    font-weight: 800;
}
.cat-section-title span {
    color: #64748B !important;
    font-size: 14px;
}
/* =========================================================
STAT CARDS
========================================================= */
.cat-stat span {
    color: #64748B !important;
    font-size: 13px;
}
.cat-stat strong {
    color: #111827 !important;
    font-size: 27px;
    font-weight: 800;
}
/* =========================================================
DEPARTMENT IMAGE
16:9 PROFESSIONAL IMAGE SIZE
========================================================= */
.root-image {
    width: 100%;
    height: 260px;
    overflow: hidden;
    background: #F3F4F6;
    display: flex;
    align-items: center;
    justify-content: center;
}
.root-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    display: block;
}
.root-placeholder {
    font-size: 48px !important;
    font-weight: 800;
    color: #94A3B8 !important;
}
/* =========================================================
DEPARTMENT CARD
========================================================= */
.root-card {
    background: #FFFFFF;
    border: 1px solid #E5E7EB;
    border-radius: 14px;
    overflow: hidden;
}
.root-card:hover {
    border-color: #CBB8A9 !important;
    box-shadow: 0 10px 28px rgba(17, 24, 39, 0.08);
}
/* =========================================================
DEPARTMENT NAME
========================================================= */
.root-name {
    color: #111827 !important;
    font-size: 21px !important;
    font-weight: 800 !important;
}
.root-meta {
    color: #64748B !important;
    font-size: 14px !important;
}
/* =========================================================
ACTIVE / INACTIVE
========================================================= */
.status {
    font-size: 14px !important;
    font-weight: 800 !important;
}
.status.active {
    color: #16A34A !important;
}
.status.active i {
    background: #16A34A !important;
}
.status.inactive {
    color: #DC2626 !important;
}
.status.inactive i {
    background: #DC2626 !important;
}
/* =========================================================
VIEW CATEGORY
========================================================= */
.cat-btn.blue,
.view-category-btn {
    color: #6F4E37 !important;
    border-color: #6F4E37 !important;
    background: #FFFFFF !important;
    font-size: 14px !important;
    font-weight: 800 !important;
}
.cat-btn.blue:hover,
.view-category-btn:hover {
    color: #FFFFFF !important;
    background: #6F4E37 !important;
    border-color: #6F4E37 !important;
}
/* =========================================================
EDIT
========================================================= */
.cat-btn:not(.blue):not(.red):not(.green) {
    color: #111827 !important;
    font-size: 14px !important;
    font-weight: 800 !important;
}
/* =========================================================
DELETE
========================================================= */
.cat-btn.red,
.delete-btn {
    color: #DC2626 !important;
    border-color: #DC2626 !important;
    background: #FFFFFF !important;
    font-size: 14px !important;
    font-weight: 800 !important;
}
.cat-btn.red:hover:not(:disabled),
.delete-btn:hover:not(:disabled) {
    color: #FFFFFF !important;
    background: #DC2626 !important;
    border-color: #DC2626 !important;
}
/* =========================================================
CHILDREN / SUBCATEGORIES
========================================================= */
.child-name,
.grandchild-name {
    color: #111827 !important;
    font-size: 15px !important;
    font-weight: 800 !important;
}
.child-sub {
    color: #64748B !important;
    font-size: 13px !important;
}
.grandchild {
    background: #F8FAFC;
}
.grandchildren {
    border-left-color: #D6C5B8 !important;
}
/* =========================================================
BACK BUTTON
========================================================= */
.back-btn {
    color: #6F4E37 !important;
    background: #F3ECE7 !important;
}
.back-btn:hover {
    color: #FFFFFF !important;
    background: #6F4E37 !important;
}
/* =========================================================
MODAL
========================================================= */
.modal-head h3 {
    color: #111827 !important;
    font-size: 22px !important;
    font-weight: 800 !important;
}
.modal-head p {
    color: #64748B !important;
    font-size: 14px !important;
}
.field label {
    color: #111827 !important;
    font-size: 14px !important;
    font-weight: 800 !important;
}
.field input,
.field select,
.field textarea {
    color: #111827 !important;
    font-size: 15px !important;
}
.field input:focus,
.field select:focus,
.field textarea:focus {
    border-color: #6F4E37 !important;
    box-shadow: 0 0 0 3px rgba(111, 78, 55, 0.14) !important;
}
/* =========================================================
SAVE BUTTON
========================================================= */
.btn-save {
    background: #6F4E37 !important;
    border-color: #6F4E37 !important;
    color: #FFFFFF !important;
    font-size: 15px !important;
    font-weight: 800 !important;
}
.btn-save:hover {
    background: #5A3F2D !important;
}
/* =========================================================
CANCEL
========================================================= */
.btn-cancel {
    color: #111827 !important;
    background: #FFFFFF !important;
    font-size: 14px !important;
    font-weight: 800 !important;
}
/* =========================================================
ERROR
========================================================= */
.error-text,
.cat-alert.error {
    color: #DC2626 !important;
}
/* =========================================================
SUCCESS
========================================================= */
.cat-alert.success {
    color: #166534 !important;
}
/* =========================================================
DELETE CONFIRM
========================================================= */
#deleteConfirmBtn {
    background: #DC2626 !important;
    border-color: #DC2626 !important;
    color: #FFFFFF !important;
}
#deleteConfirmBtn:hover {
    background: #B91C1C !important;
}
/* =========================================================
MOBILE
========================================================= */
@media (max-width: 650px) {
    .root-image {
        aspect-ratio: 16 / 9;
    }
    .root-name {
        font-size: 19px !important;
    }
    .root-meta {
        font-size: 13px !important;
    }
    .cat-btn {
        font-size: 14px !important;
    }
}
</style>

<div class="categories-page">
    <div class="categories-header">
        <div>
            <div class="cat-eyebrow">PRODUCT ORGANIZATION</div>
            <h1>Categories</h1>
            <p>Keep your store structure simple: Department → Category → Subcategory.</p>
        </div>
        <button class="cat-primary" type="button" onclick="openCategoryModal('add')">＋ Add Category</button>
    </div>

    @if(session('success'))<div class="cat-alert success js-auto-alert">✓ {{ session('success') }}</div>@endif
    @if(session('error'))<div class="cat-alert error js-auto-alert">✕ {{ session('error') }}</div>@endif
    @if($errors->any())<div class="cat-alert error js-auto-alert">✕ {{ $errors->first() }}</div>@endif

    <div class="cat-stats">
        <div class="cat-stat"><span>Total Categories</span><strong>{{ number_format($stats['total']) }}</strong></div>
        <div class="cat-stat"><span>Active</span><strong>{{ number_format($stats['active']) }}</strong></div>
        <div class="cat-stat"><span>Departments</span><strong>{{ number_format($stats['root']) }}</strong></div>
    </div>

    <form class="cat-toolbar" method="GET" action="{{ route('admin.categories.index') }}">
        <input class="cat-search" name="search" value="{{ request('search') }}" placeholder="Search category...">
        <select class="cat-select" name="status">
            <option value="">All status</option>
            <option value="1" @selected(request('status') === '1')>Active</option>
            <option value="0" @selected(request('status') === '0')>Inactive</option>
        </select>
        <button class="cat-tool-btn blue" type="submit">Search</button>
        @if(request()->hasAny(['search','status']))<a class="cat-tool-btn" href="{{ route('admin.categories.index') }}">Clear</a>@endif
    </form>

    <div class="cat-section-title">
        <h2>Departments</h2>
        <span>{{ count($categoryTree) }} shown</span>
    </div>

    @if(count($categoryTree))
        <div class="root-grid" id="rootGrid">
            @foreach($categoryTree as $root)
                <article class="root-card" data-root-id="{{ $root['id'] }}">
                    <div class="root-image">
                        @if($root['image'])
                            <img src="{{ Storage::url($root['image']) }}" alt="{{ $root['name'] }}">
                        @else
                            <div class="root-placeholder">{{ strtoupper(substr($root['name'],0,1)) }}</div>
                        @endif
                    </div>
                    <div class="root-body">
                        <div class="root-top">
                            <div><div class="root-name">{{ $root['name'] }}</div><div class="root-meta">{{ $root['products_count'] }} products · {{ count($root['children']) }} categories</div></div>
                            <span class="status {{ $root['status'] ? 'active' : 'inactive' }}"><i></i>{{ $root['status'] ? 'Active' : 'Inactive' }}</span>
                        </div>
                        <div class="root-actions">
                            <button type="button" class="cat-btn blue" onclick="showChildren({{ $root['id'] }})">View Categories</button>
                            <button type="button" class="cat-btn" onclick="openCategoryModal('edit', {{ $root['id'] }})">Edit</button>
                            <button type="button" class="cat-btn red" {{ ($root['products_count'] || count($root['children'])) ? 'disabled' : '' }} onclick="openDeleteModal({{ $root['id'] }}, @js($root['name']), {{ $root['products_count'] || count($root['children']) ? 'false' : 'true' }})">Delete</button>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <div class="empty"><strong>No departments found</strong><br>Try clearing your search or create a new category.</div>
    @endif

    <div class="children-panel" id="childrenPanel">
        <div class="children-header"><div><h3 id="childrenTitle">Categories</h3><div class="root-meta" id="childrenMeta"></div></div><button class="back-btn" type="button" onclick="hideChildren()">← Departments</button></div>
        <div class="child-list" id="childrenList"></div>
    </div>
</div>

{{-- ADD / EDIT --}}
<div class="modal-backdrop" id="categoryModal" onclick="if(event.target===this)closeCategoryModal()">
    <div class="modal">
        <form id="categoryForm" method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" id="categoryMethod" value="POST">
            <input type="hidden" name="form_type" id="categoryFormType" value="add">
            <input type="hidden" name="edit_category_id" id="editCategoryId" value="">
            <div class="modal-head"><div><h3 id="modalTitle">Add Category</h3><p id="modalSubtitle">Create a department, category or subcategory.</p></div><button type="button" class="modal-close" onclick="closeCategoryModal()">×</button></div>
            <div class="modal-body">
                <div class="form-grid">
                    <div class="field full"><label>Category Name *</label><input name="name" id="categoryName" required placeholder="e.g. Dresses"></div>
                    <div class="field"><label>Parent Category</label><select name="parent_id" id="categoryParent"><option value="">No parent — Department</option></select><span class="root-meta">Leave empty for a top-level department.</span></div>
                    <div class="field"><label>Status</label><select name="status" id="categoryStatus"><option value="1">Active</option><option value="0">Inactive</option></select></div>
                    <div class="field full"><label>Description</label><textarea name="description" id="categoryDescription" placeholder="Optional description..."></textarea></div>
                    <div class="field full"><label>Category Image</label><input type="file" name="image" id="categoryImage" accept="image/jpeg,image/png,image/webp"><div class="image-preview" id="categoryImagePreview"><img id="categoryPreviewImg" alt="Category preview"><div class="image-meta" id="categoryImageMeta"></div></div><span class="root-meta">JPG, JPEG, PNG or WEBP · up to 5MB · any dimensions</span></div>
                </div>
            </div>
            <div class="modal-foot"><button type="button" class="btn-cancel" onclick="closeCategoryModal()">Cancel</button><button class="btn-save" type="submit" id="modalSave">Save Category</button></div>
        </form>
    </div>
</div>

{{-- DELETE CONFIRM --}}
<div class="modal-backdrop" id="deleteModal" onclick="if(event.target===this)closeDeleteModal()">
    <div class="modal" style="max-width:430px"><form id="deleteForm" method="POST">@csrf @method('DELETE')<div class="modal-body"><div class="confirm-icon">!</div><h3 style="margin:0 0 7px">Delete category?</h3><p style="margin:0;color:#6b7280;font-size:13px">You are deleting <strong id="deleteName"></strong>. This cannot be undone.</p><p id="deleteWarning" style="display:none;color:#b91c1c;font-size:12px;font-weight:700"></p></div><div class="modal-foot"><button type="button" class="btn-cancel" onclick="closeDeleteModal()">Cancel</button><button type="submit" class="btn-save" style="background:var(--cat-red)" id="deleteConfirmBtn">Delete Category</button></div></form></div>
</div>

<script>
const categoryTree = @json($categoryTree);
const parentOptions = @json($parentOptions);
const baseCategoryUrl = @json(url('/admin/categories'));

function escapeHtml(value){return String(value??'').replace(/[&<>'"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[c]));}
function findNode(id,nodes=categoryTree){for(const n of nodes){if(Number(n.id)===Number(id))return n;const found=findNode(id,n.children||[]);if(found)return found;}return null;}
function allNodes(nodes=categoryTree,out=[]){for(const n of nodes){out.push(n);allNodes(n.children||[],out)}return out;}
function imageUrl(path){return path ? '/storage/'+String(path).replace(/^\//,'') : ''}
function showChildren(id){const root=findNode(id);if(!root)return;document.getElementById('rootGrid').style.display='none';const panel=document.getElementById('childrenPanel');panel.classList.add('show');document.getElementById('childrenTitle').textContent=root.name;document.getElementById('childrenMeta').textContent=`${root.children.length} categories · ${root.products_count} products`;const list=document.getElementById('childrenList');list.innerHTML='';if(!root.children.length){list.innerHTML='<div class="empty">No categories under this department yet.</div>';return;}root.children.forEach(child=>{const item=document.createElement('div');item.className='child-item';const thumb=child.image?`<img class="child-thumb" src="${escapeHtml(imageUrl(child.image))}" alt="" onerror="this.style.display='none'">`:`<div class="child-placeholder">${escapeHtml((child.name||'?').charAt(0).toUpperCase())}</div>`;const grands=(child.children||[]).map(g=>`<div class="grandchild"><span class="grandchild-name">${escapeHtml(g.name)} <span style="color:#6b7280;font-weight:500">· ${g.products_count} products</span></span><span><button class="cat-btn" type="button" onclick="openCategoryModal('edit',${g.id})">Edit</button><button class="cat-btn red" type="button" ${g.products_count||g.children?.length?'disabled':''} onclick="openDeleteModal(${g.id},'${escapeHtml(g.name)}',${g.products_count||g.children?.length?'false':'true'})">Delete</button></span></div>`).join('');item.innerHTML=`<div class="child-row"><div class="child-main">${thumb}<div><div class="child-name">${escapeHtml(child.name)}</div><div class="child-sub">${child.products_count} products · ${child.children.length} subcategories</div></div></div><div><span class="status ${child.status?'active':'inactive'}"><i></i>${child.status?'Active':'Inactive'}</span> <button class="cat-btn blue" type="button" onclick="openCategoryModal('edit',${child.id})">Edit</button> <button class="cat-btn red" type="button" ${child.products_count||child.children.length?'disabled':''} onclick="openDeleteModal(${child.id},'${escapeHtml(child.name)}',${child.products_count||child.children.length?'false':'true'})">Delete</button></div></div>${grands?`<div class="grandchildren">${grands}</div>`:''}`;list.appendChild(item);});}
function hideChildren(){document.getElementById('childrenPanel').classList.remove('show');document.getElementById('rootGrid').style.display='grid';}
function populateParents(excludeId=null){const select=document.getElementById('categoryParent');select.innerHTML='<option value="">No parent — Department</option>';allNodes().filter(n=>Number(n.id)!==Number(excludeId)).forEach(n=>{const depth=n.parent_id?1:0;const prefix=n.parent_id?'↳ ':' ';const opt=document.createElement('option');opt.value=n.id;opt.textContent=prefix+n.name;select.appendChild(opt);});}
function openCategoryModal(type,id=null){const modal=document.getElementById('categoryModal'),form=document.getElementById('categoryForm');const editing=type==='edit';document.getElementById('categoryFormType').value=editing?'edit':'add';document.getElementById('categoryMethod').value=editing?'PUT':'POST';document.getElementById('editCategoryId').value=editing?id:'';form.action=editing?`${baseCategoryUrl}/${id}`:`${baseCategoryUrl}`;document.getElementById('modalTitle').textContent=editing?'Edit Category':'Add Category';document.getElementById('modalSubtitle').textContent=editing?'Update category information.':'Create a department, category or subcategory.';populateParents(editing?id:null);const node=editing?findNode(id):null;document.getElementById('categoryName').value=node?.name||'';document.getElementById('categoryDescription').value=node?.description||'';document.getElementById('categoryStatus').value=node?.status?'1':'0';document.getElementById('categoryParent').value=node?.parent_id||'';document.getElementById('categoryImage').value='';const preview=document.getElementById('categoryImagePreview');if(node?.image){preview.classList.add('show');document.getElementById('categoryPreviewImg').src=imageUrl(node.image);document.getElementById('categoryImageMeta').textContent='Current image';}else{preview.classList.remove('show');document.getElementById('categoryPreviewImg').src='';document.getElementById('categoryImageMeta').textContent='';}modal.classList.add('show');document.body.style.overflow='hidden';setTimeout(()=>document.getElementById('categoryName').focus(),50);}
function closeCategoryModal(){document.getElementById('categoryModal').classList.remove('show');document.body.style.overflow='';}
function openDeleteModal(id,name,allowed){const warning=document.getElementById('deleteWarning'),btn=document.getElementById('deleteConfirmBtn');document.getElementById('deleteName').textContent=name;document.getElementById('deleteForm').action=`${baseCategoryUrl}/${id}`;warning.style.display=allowed?'none':'block';warning.textContent=allowed?'':'This category has products or child categories and cannot be deleted.';btn.disabled=!allowed;document.getElementById('deleteModal').classList.add('show');document.body.style.overflow='hidden';}
function closeDeleteModal(){document.getElementById('deleteModal').classList.remove('show');document.body.style.overflow='';}
document.getElementById('categoryImage').addEventListener('change',function(){const file=this.files?.[0],preview=document.getElementById('categoryImagePreview');if(!file){preview.classList.remove('show');return;}if(file.size>5*1024*1024){this.value='';preview.classList.remove('show');alert('Image is larger than 5MB. Please choose a smaller image.');return;}const reader=new FileReader();reader.onload=e=>{document.getElementById('categoryPreviewImg').src=e.target.result;document.getElementById('categoryImageMeta').textContent=`${file.name} · ${(file.size/1024/1024).toFixed(2)} MB`;preview.classList.add('show')};reader.readAsDataURL(file);});
document.addEventListener('keydown',e=>{if(e.key==='Escape'){closeCategoryModal();closeDeleteModal();}});

// Every success/error alert stays visible for 5 seconds, then closes smoothly.
document.querySelectorAll('.js-auto-alert').forEach(alertEl=>{
    setTimeout(()=>{
        alertEl.classList.add('is-hiding');
        setTimeout(()=>alertEl.remove(),300);
    },5000);
});

// Prevent accidental double-submit while a category is being saved/deleted.
document.getElementById('categoryForm')?.addEventListener('submit',()=>{
    const button=document.getElementById('modalSave');
    if(button){button.disabled=true;button.textContent='Saving...';}
});
document.getElementById('deleteForm')?.addEventListener('submit',()=>{
    const button=document.getElementById('deleteConfirmBtn');
    if(button && !button.disabled){button.disabled=true;button.textContent='Deleting...';}
});
@if($errors->any())
document.addEventListener('DOMContentLoaded',()=>openCategoryModal('{{ old('form_type')==='edit'?'edit':'add' }}',{{ old('edit_category_id') ?: 'null' }}));
@endif
</script>
@endsection
