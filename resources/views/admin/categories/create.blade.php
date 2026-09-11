@extends('admin.layouts.app')

@section('title', 'Add Category')
@section('page-title', 'Add Category')

@section('content')

<style>
    .category-form-page {
        padding: 24px;
    }

    .category-form-header {
        margin-bottom: 24px;
    }

    .category-form-header h1 {
        margin: 0;
        color: #24201E;
        font-size: 28px;
        font-weight: 700;
    }

    .category-form-header p {
        margin: 7px 0 0;
        color: #8B8580;
        font-size: 14px;
    }

    .category-form-card {
        max-width: 850px;
        background: #FFFFFF;
        border: 1px solid rgba(44, 30, 23, .08);
        border-radius: 14px;
        padding: 24px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        color: #24201E;
        font-size: 13px;
        font-weight: 600;
    }

    .required {
        color: #ff0000;
    }

    .form-input,
    .form-select,
    .form-textarea {
        width: 100%;
        box-sizing: border-box;
        border: 1px solid #E4DDD6;
        border-radius: 9px;
        background: #FAF8F5;
        color: #24201E;
        font-family: inherit;
        font-size: 14px;
        outline: none;
    }

    .form-input,
    .form-select {
        height: 44px;
        padding: 0 13px;
    }

    .form-textarea {
        min-height: 120px;
        padding: 12px 13px;
        resize: vertical;
    }

    .form-input:focus,
    .form-select:focus,
    .form-textarea:focus {
        border-color: #A66A44;
        background: #FFFFFF;
    }

    .form-error {
        margin-top: 6px;
        color: #ff0000;
        font-size: 12px;
    }

    .form-help {
        margin-top: 6px;
        color: #8B8580;
        font-size: 12px;
    }

    .image-input {
        padding: 9px;
    }

    .status-options {
        display: flex;
        gap: 12px;
    }

    .status-option {
        flex: 1;
        position: relative;
    }

    .status-option input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .status-option label {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 44px;
        border: 1px solid #E4DDD6;
        border-radius: 9px;
        background: #FAF8F5;
        color: #6F4E37;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
    }

    .status-option input:checked + label {
        border-color: #6F4E37;
        background: #F5EDE3;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 28px;
        padding-top: 20px;
        border-top: 1px solid #F0EBE6;
    }

    .cancel-btn,
    .save-btn {
        min-height: 44px;
        padding: 0 18px;
        border-radius: 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        font-family: inherit;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
    }

    .cancel-btn {
        border: 1px solid #E4DDD6;
        background: #FFFFFF;
        color: #6F4E37;
    }

    .save-btn {
        border: none;
        background: #6F4E37;
        color: #FFFFFF;
    }

    .save-btn:hover {
        background: #5C402E;
    }

    @media (max-width: 650px) {

        .category-form-page {
            padding: 16px;
        }

        .category-form-card {
            padding: 18px;
        }

        .status-options {
            flex-direction: column;
        }

        .form-actions {
            flex-direction: column-reverse;
        }

        .cancel-btn,
        .save-btn {
            width: 100%;
        }
    }
</style>


<div class="category-form-page">

    <div class="category-form-header">

        <h1>
            Add Category
        </h1>

        <p>
            Create a new product category.
        </p>

    </div>


    {{-- VALIDATION ERRORS --}}

    @if($errors->any())

        <div
            style="
                margin-bottom: 20px;
                padding: 13px 16px;
                border-radius: 10px;
                background: #FCEFEB;
                color: #ff0000;
                font-size: 13px;
            "
        >

            {{ $errors->first() }}

        </div>

    @endif


    <div class="category-form-card">

        <form
            action="{{ route('admin.categories.store') }}"
            method="POST"
            enctype="multipart/form-data"
        >

            @csrf


            {{-- CATEGORY NAME --}}

            <div class="form-group">

                <label
                    for="name"
                    class="form-label"
                >
                    Category Name
                    <span class="required">*</span>
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-input"
                    value="{{ old('name') }}"
                    placeholder="Example: Clothing"
                    required
                >

                @error('name')

                    <div class="form-error">
                        {{ $message }}
                    </div>

                @enderror

            </div>


            {{-- PARENT CATEGORY --}}

            <div class="form-group">

                <label
                    for="parent_id"
                    class="form-label"
                >
                    Parent Category
                </label>

                <select
                    id="parent_id"
                    name="parent_id"
                    class="form-select"
                >

                    <option value="">
                        No Parent — Root Category
                    </option>

                    @foreach($categoryOptions as $option)

                        <option
                            value="{{ $option['id'] }}"
                            @selected(
                                (string) old('parent_id')
                                ===
                                (string) $option['id']
                            )
                        >
                            {{ $option['name'] }}
                        </option>

                    @endforeach

                </select>

                <div class="form-help">
                    Leave empty if this is a main/root category.
                </div>

                @error('parent_id')

                    <div class="form-error">
                        {{ $message }}
                    </div>

                @enderror

            </div>


            {{-- DESCRIPTION --}}

            <div class="form-group">

                <label
                    for="description"
                    class="form-label"
                >
                    Description
                </label>

                <textarea
                    id="description"
                    name="description"
                    class="form-textarea"
                    placeholder="Describe this category..."
                >{{ old('description') }}</textarea>

                @error('description')

                    <div class="form-error">
                        {{ $message }}
                    </div>

                @enderror

            </div>


            {{-- IMAGE --}}

            <div class="form-group">

                <label
                    for="image"
                    class="form-label"
                >
                    Category Image
                </label>

                <input
                    type="file"
                    id="image"
                    name="image"
                    class="form-input image-input"
                    accept=".jpg,.jpeg,.png,.webp"
                >

                <div class="form-help">
                    JPG, JPEG, PNG or WEBP. Maximum 2MB.
                </div>

                @error('image')

                    <div class="form-error">
                        {{ $message }}
                    </div>

                @enderror

            </div>


            {{-- STATUS --}}

            <div class="form-group">

                <label class="form-label">
                    Status
                    <span class="required">*</span>
                </label>

                <div class="status-options">


                    <div class="status-option">

                        <input
                            type="radio"
                            id="status-active"
                            name="status"
                            value="1"
                            @checked(
                                old('status', '1') == '1'
                            )
                        >

                        <label for="status-active">
                            Active
                        </label>

                    </div>


                    <div class="status-option">

                        <input
                            type="radio"
                            id="status-inactive"
                            name="status"
                            value="0"
                            @checked(
                                old('status') === '0'
                            )
                        >

                        <label for="status-inactive">
                            Inactive
                        </label>

                    </div>


                </div>

                @error('status')

                    <div class="form-error">
                        {{ $message }}
                    </div>

                @enderror

            </div>


            {{-- ACTIONS --}}

            <div class="form-actions">

                <a
                    href="{{ route('admin.categories.index') }}"
                    class="cancel-btn"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="save-btn"
                >
                    Save Category
                </button>

            </div>


        </form>

    </div>

</div>

@endsection