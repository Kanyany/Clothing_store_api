
@extends('admin.layouts.app')

@section('content')
<div class="collections-page">
    {{-- Page header --}}
    <div class="collections-header">
        <div>
            <div class="collections-eyebrow">CATALOG MANAGEMENT</div>
            <h1>Collections</h1>
            <p>Organize products into collections for your store.</p>
        </div>

        <a href="{{ route('admin.collections.create') }}"
           class="collection-primary-btn">
            <span class="plus-icon">+</span>
            <span>Add Collection</span>
        </a>
    </div>

    {{-- Flash message --}}
    @if (session('success'))
        <div class="collection-alert collection-alert-success" role="status">
            <span class="alert-icon">✓</span>
            <div>{{ session('success') }}</div>
            <button type="button"
                    class="alert-close"
                    aria-label="Dismiss"
                    onclick="this.parentElement.remove()">×</button>
        </div>
    @endif

    @if ($errors->any())
        <div class="collection-alert collection-alert-error" role="alert">
            <div>
                <strong>Please check the following:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Search and collection list --}}
    <section class="collection-panel">
        <div class="collection-panel-header">
            <div>
                <h2>All Collections</h2>
                <p>Manage collection details and assigned products.</p>
            </div>

            <span class="collection-total">
                {{ $collections->total() }}
                {{ $collections->total() === 1 ? 'collection' : 'collections' }}
            </span>
        </div>

        <div class="collection-toolbar">
            <form method="GET"
                  action="{{ route('admin.collections.index') }}"
                  class="collection-search-form">
                <label class="collection-search">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"></circle>
                        <path d="m16 16 4 4"></path>
                    </svg>

                    <input type="search"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Search by collection name or slug..."
                           aria-label="Search collections">
                </label>

                <button type="submit" class="collection-search-btn">
                    Search
                </button>

                @if (request()->filled('search'))
                    <a href="{{ route('admin.collections.index') }}"
                       class="collection-clear-btn">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        <div class="collection-table-wrap">
            <table class="collection-table">
                <thead>
                    <tr>
                        <th class="image-column">IMAGE</th>
                        <th>COLLECTION</th>
                        <th>SLUG</th>
                        <th class="center-column">PRODUCTS</th>
                        <th class="center-column">STATUS</th>
                        <th class="center-column">ORDER</th>
                        <th class="actions-column">ACTIONS</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($collections as $collection)
                        <tr>
                            <td>
                                <div class="collection-image">
                                    @if ($collection->image)
                                        <img
                                            src="{{ asset('storage/' . $collection->image) }}"
                                            alt="{{ $collection->name }}"
                                            loading="lazy">
                                    @else
                                        <div class="collection-image-placeholder"
                                             aria-label="No collection image">
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <rect x="3" y="3" width="18" height="18" rx="3"></rect>
                                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                                <path d="m21 15-5-5L5 21"></path>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <td class="collection-name-cell">
                                <div class="collection-name">
                                    {{ $collection->name }}
                                </div>

                                @if ($collection->description)
                                    <div class="collection-description">
                                        {{ \Illuminate\Support\Str::limit($collection->description, 75) }}
                                    </div>
                                @else
                                    <div class="collection-description">
                                        No description
                                    </div>
                                @endif
                            </td>

                            <td>
                                <span class="collection-slug">
                                    {{ $collection->slug }}
                                </span>
                            </td>

                            <td class="center-column">
                                <span class="product-count">
                                    {{ $collection->products_count }}
                                </span>
                            </td>

                            <td class="center-column">
                                @if ($collection->status)
                                    <span class="status-pill status-active">
                                        <span></span> Active
                                    </span>
                                @else
                                    <span class="status-pill status-inactive">
                                        <span></span> Inactive
                                    </span>
                                @endif
                            </td>

                            <td class="center-column">
                                <span class="order-number">
                                    {{ $collection->sort_order }}
                                </span>
                            </td>

                            <td class="actions-column">
                                <div class="collection-actions">
                                    <a
                                        href="{{ route('admin.collections.edit', $collection) }}"
                                        class="collection-action-btn edit-action"
                                        title="Edit collection"
                                        aria-label="Edit {{ $collection->name }}">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M12 20h9"></path>
                                            <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z"></path>
                                        </svg>
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.collections.destroy', $collection) }}"
                                        onsubmit="return confirm('Delete {{ addslashes($collection->name) }}? Its assigned products will not be deleted.');">
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="collection-action-btn delete-action"
                                            title="Delete collection"
                                            aria-label="Delete {{ $collection->name }}">
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M3 6h18"></path>
                                                <path d="M8 6V4h8v2"></path>
                                                <path d="m19 6-1 14H6L5 6"></path>
                                                <path d="M10 11v5M14 11v5"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="collection-empty">
                                    <div class="empty-icon">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="m12 3 9 5-9 5-9-5 9-5Z"></path>
                                            <path d="m3 12 9 5 9-5"></path>
                                            <path d="m3 16 9 5 9-5"></path>
                                        </svg>
                                    </div>

                                    <h3>
                                        {{ request()->filled('search')
                                            ? 'No matching collections'
                                            : 'No collections yet' }}
                                    </h3>

                                    <p>
                                        {{ request()->filled('search')
                                            ? 'Try another search keyword.'
                                            : 'Create a collection to organize your store products.' }}
                                    </p>

                                    @if (request()->filled('search'))
                                        <a href="{{ route('admin.collections.index') }}"
                                           class="collection-secondary-btn">
                                            Clear search
                                        </a>
                                    @else
                                        <a href="{{ route('admin.collections.create') }}"
                                           class="collection-primary-btn">
                                            <span class="plus-icon">+</span>
                                            Add your first collection
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($collections->hasPages())
            <div class="collection-pagination">
                <div class="pagination-summary">
                    Showing
                    <strong>{{ $collections->firstItem() ?? 0 }}</strong>
                    to
                    <strong>{{ $collections->lastItem() ?? 0 }}</strong>
                    of
                    <strong>{{ $collections->total() }}</strong>
                    collections
                </div>

                <div class="pagination-links">
                    {{ $collections->withQueryString()->links() }}
                </div>
            </div>
        @endif
    </section>
</div>
@endsection

@push('styles')
<style>
    .collections-page {
        --collection-brown: #6F4E37;
        --collection-brown-dark: #503725;
        --collection-cream: #FAF8F5;
        --collection-border: #E9E1D8;
        --collection-text: #29251F;
        --collection-muted: #82786F;
        padding: 28px;
        min-width: 0;
        color: var(--collection-text);
        font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    .collections-page *,
    .collections-page *::before,
    .collections-page *::after {
        box-sizing: border-box;
    }

    .collections-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 28px;
    }

    .collections-eyebrow {
        margin-bottom: 8px;
        color: var(--collection-brown);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 1.8px;
    }

    .collections-header h1 {
        margin: 0;
        color: var(--collection-text);
        font-size: clamp(25px, 3vw, 31px);
        font-weight: 750;
        letter-spacing: -0.8px;
        line-height: 1.25;
    }

    .collections-header p {
        margin: 8px 0 0;
        color: var(--collection-muted);
        font-size: 13px;
        line-height: 1.6;
    }

    .collection-primary-btn,
    .collection-search-btn,
    .collection-secondary-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        min-height: 42px;
        padding: 0 17px;
        border: 1px solid var(--collection-brown);
        border-radius: 9px;
        background: var(--collection-brown);
        color: #fff !important;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none !important;
        white-space: nowrap;
        cursor: pointer;
        transition: background .18s ease, border-color .18s ease,
                    transform .18s ease;
    }

    .collection-primary-btn:hover,
    .collection-search-btn:hover {
        background: var(--collection-brown-dark);
        border-color: var(--collection-brown-dark);
        transform: translateY(-1px);
    }

    .plus-icon {
        font-size: 20px;
        font-weight: 400;
        line-height: 1;
    }

    .collection-alert {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 20px;
        padding: 15px 17px;
        border: 1px solid;
        border-radius: 10px;
        font-size: 13px;
        line-height: 1.6;
    }

    .collection-alert-success {
        border-color: #C8E4D1;
        background: #F0F9F2;
        color: #246B3D;
    }

    .collection-alert-error {
        border-color: #F0CDCD;
        background: #FFF5F5;
        color: #9E3030;
    }

    .collection-alert ul {
        margin: 6px 0 0;
        padding-left: 19px;
    }

    .alert-icon {
        display: grid;
        width: 22px;
        height: 22px;
        flex: 0 0 22px;
        place-items: center;
        border-radius: 50%;
        background: #D8EFDF;
        font-weight: 800;
    }

    .alert-close {
        margin-left: auto;
        border: 0;
        background: transparent;
        color: inherit;
        font-size: 22px;
        line-height: 1;
        cursor: pointer;
    }

    .collection-panel {
        overflow: hidden;
        border: 1px solid var(--collection-border);
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 5px 20px rgba(55, 37, 22, .035);
    }

    .collection-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 23px 24px 20px;
    }

    .collection-panel-header h2 {
        margin: 0;
        color: var(--collection-text);
        font-size: 16px;
        font-weight: 750;
    }

    .collection-panel-header p {
        margin: 6px 0 0;
        color: var(--collection-muted);
        font-size: 12px;
        line-height: 1.5;
    }

    .collection-total {
        padding: 7px 11px;
        border: 1px solid var(--collection-border);
        border-radius: 7px;
        background: var(--collection-cream);
        color: var(--collection-brown);
        font-size: 11px;
        font-weight: 750;
        white-space: nowrap;
    }

    .collection-toolbar {
        padding: 0 24px 20px;
        border-bottom: 1px solid var(--collection-border);
    }

    .collection-search-form {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 9px;
    }

    .collection-search {
        display: flex;
        align-items: center;
        gap: 10px;
        width: min(100%, 420px);
        min-height: 42px;
        padding: 0 12px;
        border: 1px solid #E4DDD5;
        border-radius: 9px;
        background: #fff;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .collection-search:focus-within {
        border-color: var(--collection-brown);
        box-shadow: 0 0 0 3px rgba(111, 78, 55, .09);
    }

    .collection-search svg {
        width: 18px;
        height: 18px;
        flex: 0 0 18px;
        fill: none;
        stroke: #94877B;
        stroke-width: 1.7;
        stroke-linecap: round;
    }

    .collection-search input {
        width: 100%;
        min-width: 0;
        border: 0;
        outline: 0;
        background: transparent;
        color: var(--collection-text);
        font: inherit;
        font-size: 12px;
    }

    .collection-search input::placeholder {
        color: #A49A91;
    }

    .collection-search-btn {
        min-height: 42px;
    }

    .collection-clear-btn {
        display: inline-flex;
        align-items: center;
        min-height: 42px;
        padding: 0 12px;
        border: 1px solid var(--collection-border);
        border-radius: 9px;
        color: #65574B;
        font-size: 12px;
        font-weight: 650;
        text-decoration: none;
    }

    .collection-clear-btn:hover {
        background: var(--collection-cream);
    }

    .collection-table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .collection-table {
        width: 100%;
        min-width: 850px;
        border-collapse: collapse;
        text-align: left;
    }

    .collection-table thead {
        background: #FAF8F5;
    }

    .collection-table th {
        height: 46px;
        padding: 0 16px;
        border-bottom: 1px solid var(--collection-border);
        color: #887A6D;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: 1px;
        white-space: nowrap;
    }

    .collection-table th:first-child,
    .collection-table td:first-child {
        padding-left: 24px;
    }

    .collection-table th:last-child,
    .collection-table td:last-child {
        padding-right: 24px;
    }

    .collection-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #F0EBE6;
        color: #554A40;
        font-size: 12px;
        vertical-align: middle;
    }

    .collection-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .collection-table tbody tr:not(:has(.collection-empty)):hover {
        background: #FEFCFA;
    }

    .image-column {
        width: 82px;
    }

    .center-column {
        text-align: center !important;
    }

    .actions-column {
        width: 110px;
        text-align: right !important;
    }

    .collection-image,
    .collection-image-placeholder {
        display: grid;
        width: 52px;
        height: 58px;
        overflow: hidden;
        place-items: center;
        border: 1px solid #EDE5DC;
        border-radius: 8px;
        background: #F8F4EF;
    }

    .collection-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .collection-image-placeholder svg {
        width: 22px;
        height: 22px;
        fill: none;
        stroke: #B3A293;
        stroke-width: 1.5;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .collection-name {
        color: #302820;
        font-size: 12px;
        font-weight: 750;
        line-height: 1.5;
    }

    .collection-description {
        max-width: 260px;
        margin-top: 4px;
        color: #968A7F;
        font-size: 11px;
        line-height: 1.5;
    }

    .collection-slug {
        display: inline-block;
        max-width: 180px;
        overflow: hidden;
        padding: 5px 7px;
        border: 1px solid #EEE7DF;
        border-radius: 5px;
        background: #FAF8F5;
        color: #786451;
        font-family: ui-monospace, SFMono-Regular, Consolas, monospace;
        font-size: 10px;
        text-overflow: ellipsis;
        white-space: nowrap;
        vertical-align: middle;
    }

    .product-count {
        display: inline-flex;
        min-width: 30px;
        height: 27px;
        align-items: center;
        justify-content: center;
        padding: 0 8px;
        border: 1px solid #E8DCCF;
        border-radius: 7px;
        background: #F7F0E8;
        color: var(--collection-brown);
        font-size: 11px;
        font-weight: 800;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 6px 9px;
        border: 1px solid;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 750;
        white-space: nowrap;
    }

    .status-pill > span {
        width: 6px;
        height: 6px;
        border-radius: 50%;
    }

    .status-active {
        border-color: #D3E9D8;
        background: #F0F8F1;
        color: #2F7643;
    }

    .status-active > span {
        background: #3B9957;
    }

    .status-inactive {
        border-color: #E6E1DC;
        background: #F5F3F1;
        color: #81766D;
    }

    .status-inactive > span {
        background: #A69A90;
    }

    .order-number {
        color: #68594B;
        font-size: 12px;
        font-weight: 650;
    }

    .collection-actions {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 7px;
    }

    .collection-actions form {
        display: inline-flex;
        margin: 0;
    }

    .collection-action-btn {
        display: inline-grid;
        width: 32px;
        height: 32px;
        padding: 0;
        place-items: center;
        border: 1px solid;
        border-radius: 8px;
        background: #fff;
        cursor: pointer;
        transition: background .15s ease, border-color .15s ease;
    }

    .collection-action-btn svg {
        width: 15px;
        height: 15px;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.7;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .edit-action {
        border-color: #E1D6CA;
        color: var(--collection-brown);
    }

    .edit-action:hover {
        border-color: #C8AE95;
        background: #F7F0E8;
    }

    .delete-action {
        border-color: #F0D8D5;
        color: #B34B43;
    }

    .delete-action:hover {
        border-color: #E8BDB8;
        background: #FFF3F2;
    }

    .collection-empty {
        display: flex;
        min-height: 280px;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 35px 20px;
        text-align: center;
    }

    .empty-icon {
        display: grid;
        width: 58px;
        height: 58px;
        margin-bottom: 15px;
        place-items: center;
        border: 1px solid #E8DCCF;
        border-radius: 16px;
        background: #F7F0E8;
        color: var(--collection-brown);
    }

    .empty-icon svg {
        width: 27px;
        height: 27px;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.5;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .collection-empty h3 {
        margin: 0;
        color: #352A20;
        font-size: 15px;
        font-weight: 750;
    }

    .collection-empty p {
        max-width: 360px;
        margin: 8px 0 18px;
        color: #8C8075;
        font-size: 12px;
        line-height: 1.7;
    }

    .collection-secondary-btn {
        min-height: 36px;
        border-color: var(--collection-border);
        background: #fff;
        color: var(--collection-brown) !important;
    }

    .collection-secondary-btn:hover {
        background: var(--collection-cream);
    }

    .collection-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 15px;
        padding: 18px 24px;
        border-top: 1px solid var(--collection-border);
    }

    .pagination-summary {
        color: #897D72;
        font-size: 11px;
    }

    .pagination-summary strong {
        color: #493B2E;
    }

    .pagination-links nav > div:first-child {
        display: none;
    }

    .pagination-links nav > div:last-child {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 4px;
    }

    .pagination-links a,
    .pagination-links span[aria-current],
    .pagination-links span[aria-disabled] {
        display: inline-flex;
        min-width: 32px;
        min-height: 32px;
        align-items: center;
        justify-content: center;
        padding: 0 9px;
        border: 1px solid #E8DFD5;
        border-radius: 7px;
        background: #fff;
        color: #6F4E37;
        font-size: 11px;
        text-decoration: none;
    }

    .pagination-links span[aria-current] {
        border-color: var(--collection-brown);
        background: var(--collection-brown);
        color: #fff;
    }

    .pagination-links a:hover {
        background: #F7F0E8;
    }

    .pagination-links span[aria-disabled] {
        color: #B6AAA0;
    }

    @media (max-width: 900px) {
        .collections-page {
            padding: 22px 18px;
        }

        .collection-panel-header,
        .collection-toolbar {
            padding-right: 18px;
            padding-left: 18px;
        }

        .collection-table th:first-child,
        .collection-table td:first-child {
            padding-left: 18px;
        }

        .collection-table th:last-child,
        .collection-table td:last-child {
            padding-right: 18px;
        }
    }

    @media (max-width: 600px) {
        .collections-page {
            padding: 18px 12px;
        }

        .collections-header {
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .collections-header h1 {
            font-size: 25px;
        }

        .collection-primary-btn {
            min-height: 40px;
            padding: 0 12px;
        }

        .collection-panel-header {
            align-items: flex-start;
            padding: 18px 14px;
        }

        .collection-toolbar {
            padding: 0 14px 16px;
        }

        .collection-search {
            width: 100%;
            flex: 1 1 100%;
        }

        .collection-search-form {
            gap: 8px;
        }

        .collection-pagination {
            padding: 16px 14px;
        }

        .pagination-summary {
            width: 100%;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .collections-page *,
        .collections-page *::before,
        .collections-page *::after {
            transition: none !important;
        }
    }
</style>
@endpush
