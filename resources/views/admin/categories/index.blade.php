@extends('admin.layouts.app')

@section('title', 'Categories')
@section('page-title', 'Categories')

@section('content')

<style>

/* =========================================================
   PAGE
========================================================= */

.categories-page {
    padding: 24px;
}


/* =========================================================
   HEADER
========================================================= */

.categories-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 24px;
}

.categories-title h1 {
    margin: 0;
    color: #24201E;
    font-size: 28px;
    font-weight: 800;
}

.categories-title p {
    margin: 6px 0 0;
    color: #8B8580;
    font-size: 14px;
}


/* =========================================================
   ADD BUTTON
========================================================= */

.add-category-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;

    height: 44px;
    padding: 0 18px;

    border: none;
    border-radius: 10px;

    background: #6F4E37;
    color: #FFFFFF;

    font-family: inherit;
    font-size: 14px;
    font-weight: 700;

    cursor: pointer;
}

.add-category-btn:hover {
    background: #5C402E;
}


/* =========================================================
   ALERT
========================================================= */

.category-alert {
    padding: 13px 16px;
    margin-bottom: 18px;

    border-radius: 10px;

    font-size: 14px;
}

.category-success {
    background: #EEF5EC;
    color: #64DD17;
}

.category-error {
    background: #FCEFEB;
    color: #ff0000;
}


/* =========================================================
   STATS
========================================================= */

.category-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 18px;
}

.category-stat {
    background: #FFFFFF;
    border: 1px solid rgba(44, 30, 23, .08);
    border-radius: 14px;
    padding: 19px 20px;
}

.category-stat-label {
    color: #8B8580;
    font-size: 13px;
    margin-bottom: 8px;
}

.category-stat-value {
    color: #24201E;
    font-size: 27px;
    font-weight: 800;
}


/* =========================================================
   FILTER
========================================================= */

.category-filter {
    background: #FFFFFF;
    border: 1px solid rgba(44, 30, 23, .08);
    border-radius: 14px;
    padding: 16px;
    margin-bottom: 18px;
}

.category-filter-form {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr auto;
    gap: 10px;
}

.category-input,
.category-select {
    width: 100%;
    height: 44px;
    box-sizing: border-box;

    padding: 0 13px;

    border: 1px solid #E4DDD6;
    border-radius: 9px;

    background: #FAF8F5;
    color: #24201E;

    font-family: inherit;
    font-size: 13px;

    outline: none;
}

.category-input:focus,
.category-select:focus {
    border-color: #A66A44;
    background: #FFFFFF;
}

.filter-buttons {
    display: flex;
    gap: 8px;
}

.search-btn,
.clear-btn {
    height: 44px;
    padding: 0 17px;

    border-radius: 9px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    font-family: inherit;
    font-size: 13px;
    font-weight: 700;

    cursor: pointer;
    text-decoration: none;
    white-space: nowrap;
}

.search-btn {
    border: none;
    background: #6F4E37;
    color: #FFFFFF;
}

.clear-btn {
    border: 1px solid #E4DDD6;
    background: #FFFFFF;
    color: #6F4E37;
}


/* =========================================================
   TABLE
========================================================= */

.category-table-card {
    background: #FFFFFF;
    border: 1px solid rgba(44, 30, 23, .08);
    border-radius: 14px;
    overflow: hidden;
}

.category-table-wrapper {
    overflow-x: auto;
}

.category-table {
    width: 100%;
    min-width: 900px;
    border-collapse: collapse;
}

.category-table thead th {
    padding: 15px 18px;

    background: #FAF8F5;

    border-bottom: 1px solid #EDE6DF;

    color: #8B8580;

    font-size: 12px;
    font-weight: 800;

    text-align: left;
    white-space: nowrap;
}

.category-table tbody td {
    padding: 15px 18px;

    border-bottom: 1px solid #F0EBE6;

    color: #24201E;

    font-size: 13px;

    vertical-align: middle;
}

.category-table tbody tr:last-child td {
    border-bottom: none;
}

.category-table tbody tr:hover {
    background: #FCFAF8;
}


/* =========================================================
   CATEGORY
========================================================= */

.category-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.category-image,
.category-image-placeholder {
    width: 46px;
    height: 46px;

    flex-shrink: 0;

    border-radius: 10px;
}

.category-image {
    object-fit: cover;
    background: #F5EDE3;
}

.category-image-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;

    background: #F5EDE3;
    color: #6F4E37;

    font-size: 19px;
}

.category-name {
    color: #24201E;
    font-size: 14px;
    font-weight: 800;
}

.category-id {
    margin-top: 3px;
    color: #8B8580;
    font-size: 11px;
}


/* =========================================================
   PARENT
========================================================= */

.parent-badge,
.root-badge {
    display: inline-flex;

    padding: 6px 10px;

    border-radius: 8px;

    font-size: 12px;
    font-weight: 700;
}

.parent-badge {
    background: #F5EDE3;
    color: #6F4E37;
}

.root-badge {
    background: #FAF8F5;
    color: #8B8580;
}


/* =========================================================
   DESCRIPTION
========================================================= */

.description-text {
    max-width: 260px;

    overflow: hidden;

    color: #8B8580;

    white-space: nowrap;
    text-overflow: ellipsis;

    font-size: 13px;
}


/* =========================================================
   PRODUCT COUNT
========================================================= */

.product-count {
    color: #6F4E37;
    font-weight: 800;
}


/* =========================================================
   STATUS
========================================================= */

.status-form {
    margin: 0;
}

.status-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;

    padding: 7px 11px;

    border: none;
    border-radius: 8px;

    font-family: inherit;
    font-size: 12px;
    font-weight: 700;

    cursor: pointer;
}

.status-btn.active {
    background: #EEF5EC;
    color: #64DD17;
}

.status-btn.inactive {
    background: #FCEFEB;
    color: #ff0000;
}

.status-dot {
    width: 7px;
    height: 7px;

    border-radius: 50%;

    background: currentColor;
}


/* =========================================================
   ACTIONS
========================================================= */

.actions {
    display: flex;
    align-items: center;
    gap: 7px;
}

.action-btn {
    width: 36px;
    height: 36px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    border: 1px solid #E4DDD6;
    border-radius: 8px;

    background: #FFFFFF;

    box-sizing: border-box;

    cursor: pointer;
}

.edit-btn {
    color: #6F4E37;
}

.delete-btn {
    color: #ff0000;
}

.delete-btn:disabled {
    opacity: .25;
    cursor: not-allowed;
}


/* =========================================================
   EMPTY
========================================================= */

.empty-state {
    padding: 70px 20px;
    text-align: center;
}

.empty-icon {
    margin-bottom: 12px;
    color: #6F4E37;
    font-size: 36px;
}

.empty-state h3 {
    margin: 0 0 6px;
    color: #24201E;
    font-size: 17px;
}

.empty-state p {
    margin: 0;
    color: #8B8580;
    font-size: 13px;
}


/* =========================================================
   MODAL BACKDROP
========================================================= */

.category-modal-backdrop {
    position: fixed;
    inset: 0;

    z-index: 9999;

    display: none;

    align-items: center;
    justify-content: center;

    padding: 24px;

    background: rgba(36, 32, 30, .58);

    backdrop-filter: blur(4px);
}

.category-modal-backdrop.show {
    display: flex;
}


/* =========================================================
   MODAL
========================================================= */

.category-modal {
    width: min(100%, 760px);

    max-height: calc(100vh - 48px);

    overflow-y: auto;

    background: #FFFFFF;

    border-radius: 16px;

    box-shadow:
        0 25px 80px rgba(36, 32, 30, .30);
}


/* =========================================================
   MODAL HEADER
========================================================= */

.category-modal-header {
    position: sticky;
    top: 0;

    z-index: 5;

    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 20px;

    padding: 22px 24px;

    background: #FFFFFF;

    border-bottom: 1px solid #EEE8E2;
}

.category-modal-title {
    color: #24201E;

    font-size: 22px;
    line-height: 1.3;

    font-weight: 800;
}

.category-modal-subtitle {
    margin-top: 5px;

    color: #8B8580;

    font-size: 13px;
}

.category-modal-close {
    width: 40px;
    height: 40px;

    flex-shrink: 0;

    border: none;
    border-radius: 9px;

    background: #F5EDE3;
    color: #6F4E37;

    font-size: 25px;
    line-height: 1;

    cursor: pointer;
}

.category-modal-close:hover {
    background: #EDE1D4;
}


/* =========================================================
   MODAL BODY
========================================================= */

.category-modal-body {
    padding: 24px;
}

.category-modal-grid {
    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 19px;
}

.category-modal-field {
    display: flex;
    flex-direction: column;

    gap: 8px;

    min-width: 0;
}

.category-modal-field.full {
    grid-column: 1 / -1;
}


/* =========================================================
   MODAL LABEL
========================================================= */

.category-modal-field label {
    color: #24201E;

    font-size: 14px;
    font-weight: 800;
}


/* =========================================================
   MODAL INPUT
========================================================= */

.category-modal-field input,
.category-modal-field select,
.category-modal-field textarea {
    width: 100%;

    box-sizing: border-box;

    border: 1px solid #DED5CD;
    border-radius: 9px;

    background: #FFFFFF;
    color: #24201E;

    outline: none;

    font-family: inherit;
    font-size: 14px;
}

.category-modal-field input,
.category-modal-field select {
    height: 46px;
    padding: 0 13px;
}

.category-modal-field textarea {
    min-height: 120px;

    padding: 12px;

    resize: vertical;

    line-height: 1.5;
}

.category-modal-field input::placeholder,
.category-modal-field textarea::placeholder {
    color: #A39A93;
}

.category-modal-field input:focus,
.category-modal-field select:focus,
.category-modal-field textarea:focus {
    border-color: #A66A44;

    box-shadow:
        0 0 0 3px rgba(
            166,
            106,
            68,
            .09
        );
}


/* =========================================================
   FILE
========================================================= */

.category-modal-field input[type="file"] {
    height: auto;
    padding: 10px;

    background: #FAF8F5;

    cursor: pointer;
}


/* =========================================================
   HELP
========================================================= */

.category-modal-help {
    color: #8B8580;

    font-size: 12px;

    line-height: 1.5;
}


/* =========================================================
   CURRENT IMAGE
========================================================= */

.category-edit-image {
    width: 90px;
    height: 90px;

    object-fit: cover;

    border-radius: 10px;

    border: 1px solid #E4DDD6;

    background: #F5EDE3;
}

.category-no-image {
    width: 90px;
    height: 90px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 10px;

    border: 1px solid #E4DDD6;

    background: #F5EDE3;

    color: #8B8580;

    font-size: 12px;
}


/* =========================================================
   MODAL ERROR
========================================================= */

.category-modal-error {
    color: #ff0000;

    font-size: 12px;

    line-height: 1.5;
}


/* =========================================================
   MODAL FOOTER
========================================================= */

.category-modal-footer {
    position: sticky;
    bottom: 0;

    z-index: 5;

    display: flex;
    justify-content: flex-end;

    gap: 10px;

    padding: 16px 24px;

    background: #FFFFFF;

    border-top: 1px solid #EEE8E2;
}

.category-modal-cancel,
.category-modal-save {
    height: 44px;

    padding: 0 19px;

    border-radius: 9px;

    font-family: inherit;
    font-size: 13px;
    font-weight: 800;

    cursor: pointer;
}

.category-modal-cancel {
    border: 1px solid #DED5CD;

    background: #FFFFFF;
    color: #6F4E37;
}

.category-modal-cancel:hover {
    background: #FAF8F5;
}

.category-modal-save {
    border: none;

    background: #6F4E37;
    color: #FFFFFF;
}

.category-modal-save:hover {
    background: #5C402E;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1000px) {

    .category-filter-form {
        grid-template-columns: 1fr 1fr;
    }

    .category-stats {
        grid-template-columns: 1fr;
    }
}


@media (max-width: 700px) {

    .categories-page {
        padding: 16px;
    }

    .categories-header {
        flex-direction: column;
        align-items: stretch;
    }

    .add-category-btn {
        width: 100%;
    }

    .category-filter-form {
        grid-template-columns: 1fr;
    }

    .filter-buttons {
        width: 100%;
    }

    .search-btn,
    .clear-btn {
        flex: 1;
    }

    .category-modal-backdrop {
        padding: 10px;
    }

    .category-modal {
        max-height: calc(100vh - 20px);
        border-radius: 13px;
    }

    .category-modal-header {
        padding: 18px;
    }

    .category-modal-body {
        padding: 18px;
    }

    .category-modal-footer {
        padding: 14px 18px;
    }

    .category-modal-grid {
        grid-template-columns: 1fr;
    }

    .category-modal-field.full {
        grid-column: auto;
    }

    .category-modal-cancel,
    .category-modal-save {
        flex: 1;
    }
}

</style>


<div class="categories-page">


    {{-- =====================================================
         HEADER
    ====================================================== --}}

    <div class="categories-header">

        <div class="categories-title">

            <h1>
                Categories
            </h1>

            <p>
                Manage your product categories
            </p>

        </div>


        {{-- ADD CATEGORY --}}

        <button
            type="button"
            class="add-category-btn"
            onclick="openCategoryAddModal()"
        >
            <span>＋</span>
            <span>Add Category</span>
        </button>

    </div>


    {{-- =====================================================
         ALERTS
    ====================================================== --}}

    @if(session('success'))

        <div class="category-alert category-success">
            {{ session('success') }}
        </div>

    @endif


    @if(session('error'))

        <div class="category-alert category-error">
            {{ session('error') }}
        </div>

    @endif


    {{-- =====================================================
         STATISTICS
    ====================================================== --}}

    <div class="category-stats">

        <div class="category-stat">

            <div class="category-stat-label">
                Total Categories
            </div>

            <div class="category-stat-value">
                {{ $stats['total'] }}
            </div>

        </div>


        <div class="category-stat">

            <div class="category-stat-label">
                Active
            </div>

            <div class="category-stat-value">
                {{ $stats['active'] }}
            </div>

        </div>


        <div class="category-stat">

            <div class="category-stat-label">
                Inactive
            </div>

            <div class="category-stat-value">
                {{ $stats['inactive'] }}
            </div>

        </div>

    </div>


    {{-- =====================================================
         SEARCH / FILTER
    ====================================================== --}}

    <div class="category-filter">

        <form
            action="{{ route('admin.categories.index') }}"
            method="GET"
            class="category-filter-form"
        >

            <input
                type="text"
                name="search"
                class="category-input"
                placeholder="Search category..."
                value="{{ request('search') }}"
            >


            <select
                name="status"
                class="category-select"
            >

                <option value="">
                    All Status
                </option>

                <option
                    value="1"
                    @selected(request('status') === '1')
                >
                    Active
                </option>

                <option
                    value="0"
                    @selected(request('status') === '0')
                >
                    Inactive
                </option>

            </select>


            <select
                name="parent_id"
                class="category-select"
            >

                <option value="">
                    All Parent Categories
                </option>

                <option
                    value="root"
                    @selected(
                        request('parent_id') === 'root'
                    )
                >
                    Root Categories
                </option>

                @foreach($parentCategories as $parent)

                    <option
                        value="{{ $parent->id }}"
                        @selected(
                            (string) request('parent_id')
                            ===
                            (string) $parent->id
                        )
                    >
                        {{ $parent->name }}
                    </option>

                @endforeach

            </select>


            <div class="filter-buttons">

                <button
                    type="submit"
                    class="search-btn"
                >
                    Search
                </button>

                <a
                    href="{{ route('admin.categories.index') }}"
                    class="clear-btn"
                >
                    Clear
                </a>

            </div>

        </form>

    </div>


    {{-- =====================================================
         TABLE
    ====================================================== --}}

    <div class="category-table-card">

        @if($categories->count())

            <div class="category-table-wrapper">

                <table class="category-table">

                    <thead>

                        <tr>

                            <th>
                                Category
                            </th>

                            <th>
                                Parent Category
                            </th>

                            <th>
                                Description
                            </th>

                            <th>
                                Products
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @foreach($categories as $category)

                            <tr>

                                {{-- CATEGORY --}}

                                <td>

                                    <div class="category-info">

                                        @if($category->image)

                                            <img
                                                src="{{ Storage::url($category->image) }}"
                                                alt="{{ $category->name }}"
                                                class="category-image"
                                            >

                                        @else

                                            <div class="category-image-placeholder">
                                                ◇
                                            </div>

                                        @endif


                                        <div>

                                            <div class="category-name">
                                                {{ $category->name }}
                                            </div>

                                            <div class="category-id">
                                                ID: {{ $category->id }}
                                            </div>

                                        </div>

                                    </div>

                                </td>


                                {{-- PARENT --}}

                                <td>

                                    @if($category->parent)

                                        <span class="parent-badge">
                                            {{ $category->parent->name }}
                                        </span>

                                    @else

                                        <span class="root-badge">
                                            Root Category
                                        </span>

                                    @endif

                                </td>


                                {{-- DESCRIPTION --}}

                                <td>

                                    <div class="description-text">

                                        {{ $category->description ?: '—' }}

                                    </div>

                                </td>


                                {{-- PRODUCTS --}}

                                <td>

                                    <span class="product-count">
                                        {{ $category->products_count }}
                                    </span>

                                </td>


                                {{-- STATUS --}}

                                <td>

                                    <form
                                        action="{{ route(
                                            'admin.categories.toggle-status',
                                            $category
                                        ) }}"
                                        method="POST"
                                        class="status-form"
                                    >

                                        @csrf

                                        @if($category->status)

                                            <button
                                                type="submit"
                                                class="status-btn active"
                                                title="Click to deactivate"
                                            >

                                                <span class="status-dot"></span>

                                                Active

                                            </button>

                                        @else

                                            <button
                                                type="submit"
                                                class="status-btn inactive"
                                                title="Click to activate"
                                            >

                                                <span class="status-dot"></span>

                                                Inactive

                                            </button>

                                        @endif

                                    </form>

                                </td>


                                {{-- ACTIONS --}}

                                <td>

                                    <div class="actions">


                                        {{-- EDIT --}}

                                        <button
                                            type="button"
                                            class="action-btn edit-btn"
                                            title="Edit Category"
                                            onclick="openCategoryEditModal(
                                                {{ $category->id }}
                                            )"
                                        >
                                            ✏️
                                        </button>


                                        {{-- DELETE --}}

                                        @if(
                                            $category->products_count === 0
                                            &&
                                            !$category->children()->exists()
                                        )

                                            <form
                                                action="{{ route(
                                                    'admin.categories.destroy',
                                                    $category
                                                ) }}"
                                                method="POST"
                                                onsubmit="return openCategoryDeleteModal(this, '{{ addslashes($category->name) }}');"
                                            >

                                                @csrf

                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="action-btn delete-btn"
                                                    title="Delete Category"
                                                >
                                                    🗑️
                                                </button>

                                            </form>

                                        @else

                                            <button
                                                type="button"
                                                class="action-btn delete-btn"
                                                disabled
                                                title="Cannot delete category with products or children"
                                            >
                                                🗑️
                                            </button>

                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @else

            <div class="empty-state">

                <div class="empty-icon">
                    ◇
                </div>

                <h3>
                    No Categories Found
                </h3>

                <p>
                    There are no categories in the database.
                </p>

            </div>

        @endif

    </div>

</div>


{{-- =========================================================
     ADD CATEGORY MODAL
========================================================= --}}

<div
    class="category-modal-backdrop"
    id="categoryAddModal"
    onclick="closeAddModalBackdrop(event)"
>

    <div
        class="category-modal"
        onclick="event.stopPropagation()"
    >

        <form
            action="{{ route('admin.categories.store') }}"
            method="POST"
            enctype="multipart/form-data"
        >

            @csrf

            <input
                type="hidden"
                name="form_type"
                value="add"
            >


            <div class="category-modal-header">

                <div>

                    <div class="category-modal-title">
                        Add Category
                    </div>

                    <div class="category-modal-subtitle">
                        Create a new product category
                    </div>

                </div>


                <button
                    type="button"
                    class="category-modal-close"
                    onclick="closeCategoryAddModal()"
                >
                    ×
                </button>

            </div>


            <div class="category-modal-body">

                <div class="category-modal-grid">


                    {{-- NAME --}}

                    <div class="category-modal-field">

                        <label>
                            Category Name *
                        </label>

                        <input
                            type="text"
                            name="name"
                            value="{{
                                old('form_type') === 'add'
                                    ? old('name')
                                    : ''
                            }}"
                            placeholder="Enter category name"
                            required
                        >

                        @if(old('form_type') === 'add')

                            @error('name')

                                <span class="category-modal-error">
                                    {{ $message }}
                                </span>

                            @enderror

                        @endif

                    </div>


                    {{-- PARENT --}}

                    <div class="category-modal-field">

                        <label>
                            Parent Category
                        </label>

                        <select name="parent_id">

                            <option value="">
                                No Parent (Root Category)
                            </option>

                            @foreach($categoryOptions as $option)

                                <option
                                    value="{{ $option['id'] }}"
                                    @selected(
                                        old('form_type') === 'add'
                                        &&
                                        (string) old('parent_id')
                                        ===
                                        (string) $option['id']
                                    )
                                >
                                    {{ $option['name'] }}
                                </option>

                            @endforeach

                        </select>

                        @if(old('form_type') === 'add')

                            @error('parent_id')

                                <span class="category-modal-error">
                                    {{ $message }}
                                </span>

                            @enderror

                        @endif

                    </div>


                    {{-- DESCRIPTION --}}

                    <div class="category-modal-field full">

                        <label>
                            Description
                        </label>

                        <textarea
                            name="description"
                            placeholder="Enter category description..."
                        >{{
                            old('form_type') === 'add'
                                ? old('description')
                                : ''
                        }}</textarea>

                    </div>


                    {{-- IMAGE --}}

                    <div class="category-modal-field">

                        <label>
                            Category Image
                        </label>

                        <input
                            type="file"
                            name="image"
                            accept=".jpg,.jpeg,.png,.webp"
                        >

                        <span class="category-modal-help">
                            JPG, JPEG, PNG or WEBP — Max 2MB
                        </span>

                        @if(old('form_type') === 'add')

                            @error('image')

                                <span class="category-modal-error">
                                    {{ $message }}
                                </span>

                            @enderror

                        @endif

                    </div>


                    {{-- STATUS --}}

                    <div class="category-modal-field">

                        <label>
                            Status *
                        </label>

                        <select
                            name="status"
                            required
                        >

                            <option
                                value="1"
                                @selected(
                                    old('form_type') !== 'add'
                                    ||
                                    old('status', '1') == '1'
                                )
                            >
                                Active
                            </option>

                            <option
                                value="0"
                                @selected(
                                    old('form_type') === 'add'
                                    &&
                                    old('status') == '0'
                                )
                            >
                                Inactive
                            </option>

                        </select>

                    </div>

                </div>

            </div>


            <div class="category-modal-footer">

                <button
                    type="button"
                    class="category-modal-cancel"
                    onclick="closeCategoryAddModal()"
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    class="category-modal-save"
                >
                    Save Category
                </button>

            </div>

        </form>

    </div>

</div>


{{-- =========================================================
     EDIT CATEGORY MODALS
========================================================= --}}

@foreach($categories as $category)

    @php

        $isEditError =
            old('form_type') === 'edit'
            &&
            (string) old('edit_category_id')
            ===
            (string) $category->id;

        $selectedParent =
            $isEditError
                ? old('parent_id')
                : $category->parent_id;

        $selectedStatus =
            $isEditError
                ? old(
                    'status',
                    $category->status ? '1' : '0'
                )
                : (
                    $category->status
                        ? '1'
                        : '0'
                );

    @endphp


    <div
        class="category-modal-backdrop"
        id="categoryEditModal-{{ $category->id }}"
        onclick="closeEditModalBackdrop(
            event,
            {{ $category->id }}
        )"
    >

        <div
            class="category-modal"
            onclick="event.stopPropagation()"
        >

            <form
                action="{{ route(
                    'admin.categories.update',
                    $category
                ) }}"
                method="POST"
                enctype="multipart/form-data"
            >

                @csrf

                @method('PUT')

                <input
                    type="hidden"
                    name="form_type"
                    value="edit"
                >

                <input
                    type="hidden"
                    name="edit_category_id"
                    value="{{ $category->id }}"
                >


                {{-- HEADER --}}

                <div class="category-modal-header">

                    <div>

                        <div class="category-modal-title">
                            Edit Category
                        </div>

                        <div class="category-modal-subtitle">
                            Update category information
                        </div>

                    </div>


                    <button
                        type="button"
                        class="category-modal-close"
                        onclick="closeCategoryEditModal(
                            {{ $category->id }}
                        )"
                    >
                        ×
                    </button>

                </div>


                {{-- BODY --}}

                <div class="category-modal-body">

                    <div class="category-modal-grid">


                        {{-- NAME --}}

                        <div class="category-modal-field">

                            <label>
                                Category Name *
                            </label>

                            <input
                                type="text"
                                name="name"
                                value="{{ old(
                                    'form_type'
                                ) === 'edit'
                                &&
                                (string) old(
                                    'edit_category_id'
                                ) === (string) $category->id
                                    ? old(
                                        'name',
                                        $category->name
                                    )
                                    : $category->name
                                }}"
                                placeholder="Enter category name"
                                required
                            >

                            @if($isEditError)

                                @error('name')

                                    <span class="category-modal-error">
                                        {{ $message }}
                                    </span>

                                @enderror

                            @endif

                        </div>


                        {{-- PARENT --}}

                        <div class="category-modal-field">

                            <label>
                                Parent Category
                            </label>

                            <select name="parent_id">

                                <option
                                    value=""
                                    @selected(
                                        $selectedParent === null
                                        ||
                                        $selectedParent === ''
                                    )
                                >
                                    No Parent (Root Category)
                                </option>

                                @foreach(
                                    $editCategoryOptions[
                                        $category->id
                                    ] ?? []
                                    as $option
                                )

                                    <option
                                        value="{{ $option['id'] }}"
                                        @selected(
                                            (string) $selectedParent
                                            ===
                                            (string) $option['id']
                                        )
                                    >
                                        {{ $option['name'] }}
                                    </option>

                                @endforeach

                            </select>

                            @if($isEditError)

                                @error('parent_id')

                                    <span class="category-modal-error">
                                        {{ $message }}
                                    </span>

                                @enderror

                            @endif

                        </div>


                        {{-- DESCRIPTION --}}

                        <div class="category-modal-field full">

                            <label>
                                Description
                            </label>

                            <textarea
                                name="description"
                                placeholder="Enter category description..."
                            >{{ old(
                                'form_type'
                            ) === 'edit'
                            &&
                            (string) old(
                                'edit_category_id'
                            ) === (string) $category->id
                                ? old(
                                    'description',
                                    $category->description
                                )
                                : $category->description
                            }}</textarea>

                            @if($isEditError)

                                @error('description')

                                    <span class="category-modal-error">
                                        {{ $message }}
                                    </span>

                                @enderror

                            @endif

                        </div>


                        {{-- IMAGE --}}

                        <div class="category-modal-field">

                            <label>
                                Category Image
                            </label>


                            @if($category->image)

                                <img
                                    src="{{ Storage::url(
                                        $category->image
                                    ) }}"
                                    alt="{{ $category->name }}"
                                    class="category-edit-image"
                                >

                                <span class="category-modal-help">
                                    Current image
                                </span>

                            @else

                                <div class="category-no-image">
                                    No image
                                </div>

                            @endif


                            <input
                                type="file"
                                name="image"
                                accept=".jpg,.jpeg,.png,.webp"
                            >

                            <span class="category-modal-help">
                                Upload a new image only if you want
                                to replace the current image.
                                Max 2MB.
                            </span>


                            @if($isEditError)

                                @error('image')

                                    <span class="category-modal-error">
                                        {{ $message }}
                                    </span>

                                @enderror

                            @endif

                        </div>


                        {{-- STATUS --}}

                        <div class="category-modal-field">

                            <label>
                                Status *
                            </label>

                            <select
                                name="status"
                                required
                            >

                                <option
                                    value="1"
                                    @selected(
                                        (string) $selectedStatus === '1'
                                    )
                                >
                                    Active
                                </option>

                                <option
                                    value="0"
                                    @selected(
                                        (string) $selectedStatus === '0'
                                    )
                                >
                                    Inactive
                                </option>

                            </select>

                        </div>

                    </div>

                </div>


                {{-- FOOTER --}}

                <div class="category-modal-footer">

                    <button
                        type="button"
                        class="category-modal-cancel"
                        onclick="closeCategoryEditModal(
                            {{ $category->id }}
                        )"
                    >
                        Cancel
                    </button>


                    <button
                        type="submit"
                        class="category-modal-save"
                    >
                        Update Category
                    </button>

                </div>

            </form>

        </div>

    </div>

@endforeach



{{-- =========================================================
     DELETE CATEGORY CONFIRMATION MODAL
========================================================= --}}

<div
    class="category-delete-backdrop"
    id="categoryDeleteModal"
    onclick="closeCategoryDeleteBackdrop(event)"
>
    <div
        class="category-delete-modal"
        onclick="event.stopPropagation()"
        role="dialog"
        aria-modal="true"
        aria-labelledby="categoryDeleteTitle"
    >
        <div class="category-delete-icon">
            !
        </div>

        <div
            class="category-delete-title"
            id="categoryDeleteTitle"
        >
            Delete Category?
        </div>

        <p class="category-delete-text">
            Are you sure you want to delete
            <strong id="categoryDeleteName"></strong>?
            This action cannot be undone.
        </p>

        <div class="category-delete-footer">

            <button
                type="button"
                class="category-delete-cancel"
                onclick="closeCategoryDeleteModal()"
            >
                Cancel
            </button>

            <button
                type="button"
                class="category-delete-confirm"
                onclick="confirmCategoryDelete()"
            >
                Delete Category
            </button>

        </div>
    </div>
</div>

<style>
/* =========================================================
   DELETE CONFIRMATION MODAL
========================================================= */

.category-delete-backdrop {
    position: fixed;
    inset: 0;
    z-index: 10000;

    display: none;
    align-items: center;
    justify-content: center;

    padding: 20px;

    background: rgba(36, 32, 30, .60);
    backdrop-filter: blur(4px);
}

.category-delete-backdrop.show {
    display: flex;
}

.category-delete-modal {
    width: min(430px, 100%);
    padding: 28px;

    border: 1px solid rgba(44, 30, 23, .08);
    border-radius: 18px;

    background: #FFFFFF;

    box-shadow:
        0 25px 80px rgba(36, 32, 30, .30);

    text-align: center;
}

.category-delete-icon {
    width: 54px;
    height: 54px;

    margin: 0 auto 16px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background: #FCEFEB;
    color: #ff0000;

    font-size: 25px;
    font-weight: 900;
}

.category-delete-title {
    color: #24201E;

    font-size: 22px;
    font-weight: 800;
}

.category-delete-text {
    margin: 10px 0 24px;

    color: #8B8580;

    font-size: 14px;
    line-height: 1.6;
}

.category-delete-text strong {
    color: #24201E;
    font-weight: 800;
}

.category-delete-footer {
    display: flex;
    justify-content: center;
    gap: 10px;
}

.category-delete-cancel,
.category-delete-confirm {
    min-width: 130px;
    height: 44px;

    padding: 0 18px;

    border-radius: 9px;

    font-family: inherit;
    font-size: 13px;
    font-weight: 800;

    cursor: pointer;
}

.category-delete-cancel {
    border: 1px solid #DED5CD;
    background: #FFFFFF;
    color: #6F4E37;
}

.category-delete-cancel:hover {
    background: #FAF8F5;
}

.category-delete-confirm {
    border: none;
    background: #ff0000;
    color: #FFFFFF;
}

.category-delete-confirm:hover {
    background: #A94738;
}

@media (max-width: 500px) {
    .category-delete-modal {
        padding: 22px;
    }

    .category-delete-footer {
        flex-direction: column-reverse;
    }

    .category-delete-cancel,
    .category-delete-confirm {
        width: 100%;
    }
}
</style>

<script>

let categoryDeleteForm = null;

function openCategoryDeleteModal(form, categoryName) {
    categoryDeleteForm = form;

    const modal = document.getElementById('categoryDeleteModal');
    const name = document.getElementById('categoryDeleteName');

    if (!modal || !name) {
        return true;
    }

    name.textContent = categoryName || 'this category';

    modal.classList.add('show');
    document.body.style.overflow = 'hidden';

    return false;
}

function confirmCategoryDelete() {
    if (!categoryDeleteForm) {
        return;
    }

    categoryDeleteForm.submit();
}

function closeCategoryDeleteModal() {
    const modal = document.getElementById('categoryDeleteModal');

    if (!modal) {
        return;
    }

    modal.classList.remove('show');
    document.body.style.overflow = '';

    categoryDeleteForm = null;
}

function closeCategoryDeleteBackdrop(event) {
    if (event.target.id === 'categoryDeleteModal') {
        closeCategoryDeleteModal();
    }
}

/* =========================================================
   ADD MODAL
========================================================= */

function openCategoryAddModal() {

    const modal =
        document.getElementById(
            'categoryAddModal'
        );

    if (!modal) {
        return;
    }

    modal.classList.add('show');

    document.body.style.overflow =
        'hidden';
}


function closeCategoryAddModal() {

    const modal =
        document.getElementById(
            'categoryAddModal'
        );

    if (!modal) {
        return;
    }

    modal.classList.remove('show');

    document.body.style.overflow =
        '';
}


function closeAddModalBackdrop(event) {

    if (
        event.target.id ===
        'categoryAddModal'
    ) {
        closeCategoryAddModal();
    }
}


/* =========================================================
   EDIT MODAL
========================================================= */

function openCategoryEditModal(categoryId) {

    const modal =
        document.getElementById(
            'categoryEditModal-' +
            categoryId
        );

    if (!modal) {
        return;
    }

    modal.classList.add('show');

    document.body.style.overflow =
        'hidden';
}


function closeCategoryEditModal(categoryId) {

    const modal =
        document.getElementById(
            'categoryEditModal-' +
            categoryId
        );

    if (!modal) {
        return;
    }

    modal.classList.remove('show');

    document.body.style.overflow =
        '';
}


function closeEditModalBackdrop(
    event,
    categoryId
) {

    if (
        event.target.id ===
        'categoryEditModal-' +
        categoryId
    ) {
        closeCategoryEditModal(
            categoryId
        );
    }
}


/* =========================================================
   ESC KEY
========================================================= */

document.addEventListener(
    'keydown',
    function(event) {

        if (event.key !== 'Escape') {
            return;
        }

        document
            .querySelectorAll(
                '.category-modal-backdrop.show'
            )
            .forEach(function(modal) {

                modal.classList.remove(
                    'show'
                );

            });

        document.body.style.overflow =
            '';

    }
);


/* =========================================================
   DELETE MODAL ESC KEY
========================================================= */

document.addEventListener(
    'keydown',
    function(event) {
        if (event.key === 'Escape') {
            closeCategoryDeleteModal();
        }
    }
);



/* =========================================================
   AUTO OPEN AFTER VALIDATION ERROR
========================================================= */

document.addEventListener(
    'DOMContentLoaded',
    function() {

        const formType =
            @json(old('form_type'));

        const editCategoryId =
            @json(old('edit_category_id'));


        if (formType === 'add') {

            openCategoryAddModal();

            return;
        }


        if (
            formType === 'edit'
            &&
            editCategoryId
        ) {

            openCategoryEditModal(
                editCategoryId
            );

        }

    }
);

</script>

@endsection