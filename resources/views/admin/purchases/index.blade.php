@extends('admin.layouts.app')

@section('title', 'Purchases')

@section('page-title', 'Purchases')

@section('content')

<style>

    /* =========================================================
       GLOBAL PURCHASE PAGE
    ========================================================= */

    .purchase-page {
        width: 100%;
        color: #24201E;
    }

    .purchase-page * {
        box-sizing: border-box;
    }


    /* =========================================================
       HEADER
    ========================================================= */

    .purchase-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 24px;
    }

    .purchase-title {
        font-size: 24px;
        line-height: 1.2;
        font-weight: 800;
        color: #24201E;
    }

    .purchase-subtitle {
        margin-top: 7px;
        font-size: 13px;
        line-height: 1.5;
        color: #8B8580;
    }

    .purchase-add-button {
        height: 42px;
        padding: 0 18px;
        border: 0;
        border-radius: 9px;
        background: #6F4E37;
        color: #FFFFFF;
        font-family: inherit;
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
    }

    .purchase-add-button:hover {
        background: #5D402E;
    }


    /* =========================================================
       ALERT
    ========================================================= */

    .purchase-alert {
        margin-bottom: 18px;
        padding: 13px 16px;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 700;
    }

    .purchase-alert.success {
        color: #64dd17;
        background: rgba(95, 141, 90, .10);
        border: 1px solid rgba(95, 141, 90, .18);
    }

    .purchase-alert.error {
        color: #ff0000;
        background: rgba(200, 92, 74, .10);
        border: 1px solid rgba(200, 92, 74, .18);
    }


    /* =========================================================
       STAT CARDS
    ========================================================= */

    .purchase-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 20px;
    }

    .purchase-stat {
        padding: 18px;
        background: #FFFFFF;
        border: 1px solid rgba(44, 30, 23, .08);
        border-radius: 12px;
    }

    .purchase-stat-label {
        font-size: 11px;
        font-weight: 800;
        color: #8B8580;
        letter-spacing: .2px;
    }

    .purchase-stat-value {
        margin-top: 9px;
        font-size: 25px;
        line-height: 1.2;
        font-weight: 800;
        color: #24201E;
    }

    .purchase-stat-note {
        margin-top: 6px;
        font-size: 11px;
        color: #A66A44;
    }


    /* =========================================================
       HISTORY CARD
    ========================================================= */

    .purchase-card {
        background: #FFFFFF;
        border: 1px solid rgba(44, 30, 23, .08);
        border-radius: 12px;
        overflow: hidden;
    }

    .purchase-card-header {
        padding: 18px 20px;
        border-bottom: 1px solid #EEE8E2;
    }

    .purchase-card-title {
        font-size: 16px;
        font-weight: 800;
        color: #24201E;
    }


    /* =========================================================
       TOOLBAR
    ========================================================= */

    .purchase-toolbar {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        padding: 16px 20px;
    }

    .purchase-search {
        width: 280px;
        height: 40px;
        padding: 0 13px;
        border: 1px solid #DED5CD;
        border-radius: 8px;
        background: #FFFFFF;
        color: #24201E;
        outline: none;
        font-family: inherit;
        font-size: 13px;
    }

    .purchase-search::placeholder {
        color: #A39A93;
    }

    .purchase-select {
        height: 40px;
        padding: 0 12px;
        border: 1px solid #DED5CD;
        border-radius: 8px;
        background: #FFFFFF;
        color: #24201E;
        outline: none;
        font-family: inherit;
        font-size: 13px;
    }

    .purchase-filter-button {
        height: 40px;
        padding: 0 15px;
        border: 0;
        border-radius: 8px;
        background: #F5EDE3;
        color: #6F4E37;
        font-family: inherit;
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
    }

    .purchase-search:focus,
    .purchase-select:focus,
    .purchase-modal input:focus,
    .purchase-modal select:focus,
    .purchase-modal textarea:focus {
        border-color: #A66A44;
        box-shadow: 0 0 0 3px rgba(166, 106, 68, .09);
    }


    /* =========================================================
       TABLE
    ========================================================= */

    .purchase-table-wrapper {
        width: 100%;
        overflow-x: auto;
    }

    .purchase-table {
        width: 100%;
        min-width: 900px;
        border-collapse: collapse;
    }

    .purchase-table th {
        padding: 13px 15px;
        background: #FAF8F5;
        border-top: 1px solid #EEE8E2;
        border-bottom: 1px solid #EEE8E2;
        color: #766F6A;
        text-align: left;
        font-size: 11px;
        font-weight: 800;
    }

    .purchase-table td {
        padding: 15px;
        border-bottom: 1px solid #F1ECE8;
        color: #24201E;
        font-size: 12px;
        vertical-align: middle;
    }

    .purchase-table tbody tr:hover {
        background: #FFFCF9;
    }

    .purchase-number {
        color: #6F4E37;
        font-size: 12px;
        font-weight: 800;
    }

    .purchase-total {
        font-size: 13px;
        font-weight: 800;
    }


    /* =========================================================
       STATUS
    ========================================================= */

    .purchase-status {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 6px 10px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 800;
    }

    .purchase-status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
    }

    .purchase-status.draft {
        background: rgba(166, 106, 68, .11);
        color: #A66A44;
    }

    .purchase-status.draft .purchase-status-dot {
        background: #A66A44;
    }

    .purchase-status.received {
        background: rgba(95, 141, 90, .11);
        color: #64dd17;
    }

    .purchase-status.received .purchase-status-dot {
        background: #64dd17;
    }

    .purchase-status.cancelled {
        background: rgba(200, 92, 74, .11);
        color: #ff0000;
    }

    .purchase-status.cancelled .purchase-status-dot {
        background: #ff0000;
    }


    /* =========================================================
       ACTIONS
    ========================================================= */

    .purchase-actions {
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .purchase-view-button,
    .purchase-receive-button {
        height: 34px;
        padding: 0 12px;
        border-radius: 8px;
        font-family: inherit;
        font-size: 11px;
        font-weight: 800;
        cursor: pointer;
    }

    .purchase-view-button {
        border: 1px solid #DDD3CB;
        background: #FFFFFF;
        color: #6F4E37;
    }

    .purchase-view-button:hover {
        background: #F5EDE3;
    }

    .purchase-receive-button {
        border: 0;
        background: #6F4E37;
        color: #FFFFFF;
    }


    /* =========================================================
       EMPTY
    ========================================================= */

    .purchase-empty {
        padding: 80px 20px;
        text-align: center;
    }

    .purchase-empty-icon {
        width: 56px;
        height: 56px;
        margin: 0 auto 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        background: #F5EDE3;
        color: #6F4E37;
        font-size: 25px;
    }

    .purchase-empty-title {
        font-size: 16px;
        font-weight: 800;
        color: #24201E;
    }

    .purchase-empty-text {
        margin-top: 6px;
        font-size: 13px;
        color: #8B8580;
    }


    /* =========================================================
       MODAL BACKDROP
    ========================================================= */

    .purchase-modal-backdrop {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 25px;
        background: rgba(36, 32, 30, .52);
        backdrop-filter: blur(4px);
    }

    .purchase-modal-backdrop.show {
        display: flex;
    }


    /* =========================================================
       MAIN MODAL
    ========================================================= */

    .purchase-modal {
        width: min(100%, 1000px);
        max-height: calc(100vh - 50px);
        overflow-y: auto;
        background: #FFFFFF;
        border-radius: 16px;
        box-shadow: 0 25px 80px rgba(36, 32, 30, .28);
    }

    .purchase-modal-header {
        position: sticky;
        top: 0;
        z-index: 5;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 20px 24px;
        background: #FFFFFF;
        border-bottom: 1px solid #EEE8E2;
    }

    .purchase-modal-title {
        font-size: 20px;
        line-height: 1.3;
        font-weight: 800;
        color: #24201E;
    }

    .purchase-modal-subtitle {
        margin-top: 5px;
        font-size: 12px;
        line-height: 1.5;
        color: #8B8580;
    }

    .purchase-modal-close {
        flex: 0 0 auto;
        width: 38px;
        height: 38px;
        border: 0;
        border-radius: 9px;
        background: #F5EDE3;
        color: #6F4E37;
        font-size: 22px;
        line-height: 1;
        cursor: pointer;
    }

    .purchase-modal-body {
        padding: 24px;
    }


    /* =========================================================
       FORM
    ========================================================= */

    .purchase-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .purchase-form-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }

    .purchase-form-field.full {
        grid-column: 1 / -1;
    }

    .purchase-form-field label {
        color: #6F4E37;
        font-size: 12px;
        font-weight: 800;
    }

    .purchase-form-field input,
    .purchase-form-field select,
    .purchase-form-field textarea {
        width: 100%;
        padding: 11px 12px;
        border: 1px solid #DED5CD;
        border-radius: 8px;
        background: #FFFFFF;
        color: #24201E;
        outline: none;
        font-family: inherit;
        font-size: 13px;
    }

    .purchase-form-field input,
    .purchase-form-field select {
        height: 42px;
    }

    .purchase-form-field textarea {
        min-height: 95px;
        resize: vertical;
    }

    .purchase-error {
        margin-top: 2px;
        color: #ff0000;
        font-size: 11px;
        line-height: 1.5;
    }


    /* =========================================================
       ITEMS
    ========================================================= */

    .purchase-items-section {
        margin-top: 24px;
        padding: 18px;
        border: 1px solid #E9E1DA;
        border-radius: 12px;
        background: #FAF8F5;
    }

    .purchase-items-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 15px;
    }

    .purchase-items-title {
        font-size: 15px;
        font-weight: 800;
        color: #24201E;
    }

    .purchase-add-item-button {
        height: 38px;
        padding: 0 13px;
        border: 1px solid #D9CCC1;
        border-radius: 8px;
        background: #FFFFFF;
        color: #6F4E37;
        font-family: inherit;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
    }

    .purchase-add-item-button:hover {
        background: #F5EDE3;
    }

    .purchase-items-table-wrapper {
        overflow-x: auto;
    }

    .purchase-items-table {
        width: 100%;
        min-width: 760px;
        border-collapse: collapse;
    }

    .purchase-items-table th {
        padding: 11px 10px;
        background: #FFFFFF;
        border-bottom: 1px solid #E9E1DA;
        color: #766F6A;
        text-align: left;
        font-size: 11px;
        font-weight: 800;
    }

    .purchase-items-table td {
        padding: 10px;
        border-bottom: 1px solid #E9E1DA;
        font-size: 12px;
        vertical-align: middle;
    }

    .purchase-item-product {
        min-width: 300px;
    }

    .purchase-item-input {
        width: 100%;
        height: 40px;
        padding: 0 10px;
        border: 1px solid #DED5CD;
        border-radius: 8px;
        background: #FFFFFF;
        color: #24201E;
        outline: none;
        font-family: inherit;
        font-size: 12px;
    }

    .purchase-item-subtotal {
        font-size: 13px;
        font-weight: 800;
        white-space: nowrap;
    }

    .purchase-item-remove {
        width: 34px;
        height: 34px;
        border: 0;
        border-radius: 8px;
        background: rgba(200, 92, 74, .10);
        color: #ff0000;
        font-size: 18px;
        cursor: pointer;
    }

    .purchase-no-items {
        padding: 28px 12px !important;
        text-align: center;
        color: #8B8580;
        font-size: 12px !important;
    }


    /* =========================================================
       TOTAL
    ========================================================= */

    .purchase-total-box {
        display: flex;
        justify-content: flex-end;
        margin-top: 18px;
    }

    .purchase-total-inner {
        min-width: 280px;
        padding: 15px 18px;
        border-radius: 10px;
        background: #F5EDE3;
    }

    .purchase-total-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 25px;
        color: #6F4E37;
        font-size: 13px;
    }

    .purchase-total-row strong {
        color: #24201E;
        font-size: 19px;
    }


    /* =========================================================
       FOOTER
    ========================================================= */

    .purchase-modal-footer {
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

    .purchase-cancel-button,
    .purchase-save-button {
        height: 42px;
        padding: 0 18px;
        border-radius: 8px;
        font-family: inherit;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
    }

    .purchase-cancel-button {
        border: 1px solid #DED5CD;
        background: #FFFFFF;
        color: #6F4E37;
    }

    .purchase-save-button {
        border: 0;
        background: #6F4E37;
        color: #FFFFFF;
    }

    .purchase-save-button:hover {
        background: #5D402E;
    }


    /* =========================================================
       ADD ITEM DIALOG
    ========================================================= */

    .purchase-item-dialog {
        width: min(100%, 540px);
        background: #FFFFFF;
        border-radius: 15px;
        box-shadow: 0 25px 80px rgba(36, 32, 30, .28);
    }

    .purchase-item-dialog-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 19px 21px;
        border-bottom: 1px solid #EEE8E2;
    }

    .purchase-item-dialog-title {
        font-size: 17px;
        font-weight: 800;
        color: #24201E;
    }

    .purchase-item-dialog-body {
        padding: 21px;
    }

    .purchase-item-dialog-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .purchase-item-dialog-grid .full {
        grid-column: 1 / -1;
    }

    .purchase-dialog-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }

    .purchase-dialog-field label {
        color: #6F4E37;
        font-size: 12px;
        font-weight: 800;
    }

    .purchase-dialog-field input,
    .purchase-dialog-field select {
        width: 100%;
        height: 42px;
        padding: 0 11px;
        border: 1px solid #DED5CD;
        border-radius: 8px;
        outline: none;
        background: #FFFFFF;
        color: #24201E;
        font-family: inherit;
        font-size: 13px;
    }

    .purchase-dialog-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 15px 21px;
        border-top: 1px solid #EEE8E2;
    }


    /* =========================================================
       VIEW PURCHASE DIALOG
    ========================================================= */

    .purchase-view-modal {
        align-items: center;
    }

    .purchase-view-modal-box {
        width: min(100%, 920px);
    }

    .purchase-view-info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1px;
        margin-bottom: 20px;
        overflow: hidden;
        border: 1px solid #E9E1DA;
        border-radius: 11px;
        background: #E9E1DA;
    }

    .purchase-view-info-item {
        padding: 15px;
        background: #FFFFFF;
    }

    .purchase-view-info-item span {
        display: block;
        margin-bottom: 6px;
        color: #8B8580;
        font-size: 11px;
    }

    .purchase-view-info-item strong {
        color: #24201E;
        font-size: 13px;
        font-weight: 800;
    }

    .purchase-view-products {
        overflow: hidden;
        border: 1px solid #E9E1DA;
        border-radius: 11px;
    }

    .purchase-view-section-title {
        padding: 14px 16px;
        border-bottom: 1px solid #E9E1DA;
        color: #24201E;
        font-size: 14px;
        font-weight: 800;
    }

    .purchase-detail-table {
        width: 100%;
        min-width: 650px;
        border-collapse: collapse;
    }

    .purchase-detail-table th {
        padding: 12px 13px;
        background: #FAF8F5;
        color: #766F6A;
        text-align: left;
        font-size: 11px;
        font-weight: 800;
    }

    .purchase-detail-table td {
        padding: 13px;
        border-top: 1px solid #F0EBE6;
        color: #24201E;
        font-size: 12px;
    }

    .purchase-product {
        color: #6F4E37;
        font-size: 12px;
        font-weight: 800;
    }

    .purchase-view-summary {
        width: min(100%, 320px);
        margin: 18px 0 0 auto;
        padding: 16px;
        border-radius: 10px;
        background: #F5EDE3;
    }

    .purchase-view-summary-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 7px 0;
        color: #6F4E37;
        font-size: 12px;
    }

    .purchase-view-summary-row strong {
        color: #24201E;
        font-size: 13px;
    }

    .purchase-view-summary-row.total {
        margin-top: 7px;
        padding-top: 12px;
        border-top: 1px solid #DCCFC3;
    }

    .purchase-view-summary-row.total strong {
        font-size: 19px;
    }

    .purchase-view-note {
        margin-top: 18px;
        overflow: hidden;
        border: 1px solid #E9E1DA;
        border-radius: 10px;
    }

    .purchase-view-note-text {
        padding: 15px 16px;
        color: #24201E;
        font-size: 13px;
        line-height: 1.6;
    }


    /* =========================================================
       CONFIRM DIALOG
    ========================================================= */

    .purchase-confirm-dialog {
        width: min(100%, 440px);
        padding: 28px;
        background: #FFFFFF;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0 25px 80px rgba(36, 32, 30, .28);
    }

    .purchase-confirm-icon {
        width: 54px;
        height: 54px;
        margin: 0 auto 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #F5EDE3;
        color: #6F4E37;
        font-size: 23px;
        font-weight: 800;
    }

    .purchase-confirm-title {
        font-size: 19px;
        font-weight: 800;
        color: #24201E;
    }

    .purchase-confirm-text {
        margin-top: 8px;
        color: #8B8580;
        font-size: 13px;
        line-height: 1.6;
    }

    .purchase-confirm-actions {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 22px;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 1000px) {

        .purchase-stats {
            grid-template-columns: repeat(2, 1fr);
        }

    }


    @media (max-width: 700px) {

        .purchase-header {
            align-items: stretch;
            flex-direction: column;
        }

        .purchase-add-button {
            width: 100%;
        }

        .purchase-form-grid {
            grid-template-columns: 1fr;
        }

        .purchase-form-field.full {
            grid-column: auto;
        }

        .purchase-search {
            width: 100%;
        }

        .purchase-select,
        .purchase-filter-button {
            width: 100%;
        }

        .purchase-toolbar {
            flex-direction: column;
            align-items: stretch;
        }

        .purchase-view-info-grid {
            grid-template-columns: 1fr;
        }

    }


    @media (max-width: 520px) {

        .purchase-modal-backdrop {
            padding: 10px;
        }

        .purchase-modal-body {
            padding: 17px;
        }

        .purchase-modal-header {
            padding: 16px 17px;
        }

        .purchase-modal-footer {
            padding: 13px 17px;
        }

        .purchase-stats {
            grid-template-columns: 1fr;
        }

        .purchase-item-dialog-grid {
            grid-template-columns: 1fr;
        }

        .purchase-item-dialog-grid .full {
            grid-column: auto;
        }

    }

</style>


<div class="purchase-page">


    {{-- =====================================================
         ALERTS
    ====================================================== --}}

    @if(session('success'))

        <div class="purchase-alert success">
            {{ session('success') }}
        </div>

    @endif


    @if(session('error'))

        <div class="purchase-alert error">
            {{ session('error') }}
        </div>

    @endif


    {{-- =====================================================
         HEADER
    ====================================================== --}}

    <div class="purchase-header">

        <div>

            <div class="purchase-title">
                Purchases
            </div>

            <div class="purchase-subtitle">
                Procurement & Receiving
            </div>

        </div>


        <button
            type="button"
            class="purchase-add-button"
            onclick="openPurchaseModal()"
        >
            + New Purchase
        </button>

    </div>


    {{-- =====================================================
         STATS
    ====================================================== --}}

    <div class="purchase-stats">

        <div class="purchase-stat">

            <div class="purchase-stat-label">
                TOTAL PURCHASES
            </div>

            <div class="purchase-stat-value">
                {{ number_format($stats['total']) }}
            </div>

            <div class="purchase-stat-note">
                Database records
            </div>

        </div>


        <div class="purchase-stat">

            <div class="purchase-stat-label">
                DRAFT
            </div>

            <div class="purchase-stat-value">
                {{ number_format($stats['draft']) }}
            </div>

            <div class="purchase-stat-note">
                Not received
            </div>

        </div>


        <div class="purchase-stat">

            <div class="purchase-stat-label">
                RECEIVED
            </div>

            <div class="purchase-stat-value">
                {{ number_format($stats['received']) }}
            </div>

            <div class="purchase-stat-note">
                Inventory updated
            </div>

        </div>


        <div class="purchase-stat">

            <div class="purchase-stat-label">
                RECEIVED VALUE
            </div>

            <div class="purchase-stat-value">
                ${{ number_format($stats['received_value'], 2) }}
            </div>

            <div class="purchase-stat-note">
                Received purchases
            </div>

        </div>

    </div>


    {{-- =====================================================
         PURCHASE HISTORY
    ====================================================== --}}

    <div class="purchase-card">

        <div class="purchase-card-header">

            <div class="purchase-card-title">
                Purchase History
            </div>

        </div>


        {{-- FILTER --}}

        <form
            method="GET"
            action="{{ route('admin.purchases.index') }}"
            class="purchase-toolbar"
        >

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                class="purchase-search"
                placeholder="Search supplier or product..."
            >


            <select
                name="sort"
                class="purchase-select"
            >

                <option
                    value="latest"
                    {{ request('sort', 'latest') === 'latest' ? 'selected' : '' }}
                >
                    Latest
                </option>

                <option
                    value="oldest"
                    {{ request('sort') === 'oldest' ? 'selected' : '' }}
                >
                    Oldest
                </option>

                <option
                    value="highest"
                    {{ request('sort') === 'highest' ? 'selected' : '' }}
                >
                    Highest Total
                </option>

                <option
                    value="lowest"
                    {{ request('sort') === 'lowest' ? 'selected' : '' }}
                >
                    Lowest Total
                </option>

            </select>


            <select
                name="status"
                class="purchase-select"
            >

                <option value="">
                    All Status
                </option>

                <option
                    value="draft"
                    {{ request('status') === 'draft' ? 'selected' : '' }}
                >
                    Draft
                </option>

                <option
                    value="received"
                    {{ request('status') === 'received' ? 'selected' : '' }}
                >
                    Received
                </option>

                <option
                    value="cancelled"
                    {{ request('status') === 'cancelled' ? 'selected' : '' }}
                >
                    Cancelled
                </option>

            </select>


            <button
                type="submit"
                class="purchase-filter-button"
            >
                Filter
            </button>

        </form>


        {{-- =================================================
             TABLE
        ================================================== --}}

        @if($purchases->count())

            <div class="purchase-table-wrapper">

                <table class="purchase-table">

                    <thead>

                        <tr>

                            <th>
                                Purchase ID
                            </th>

                            <th>
                                Supplier
                            </th>

                            <th>
                                Date
                            </th>

                            <th>
                                Items
                            </th>

                            <th>
                                Total
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

                        @foreach($purchases as $purchase)

                            <tr>

                                <td>

                                    <span class="purchase-number">
                                        #PO-{{
                                            str_pad(
                                                $purchase->id,
                                                5,
                                                '0',
                                                STR_PAD_LEFT
                                            )
                                        }}
                                    </span>

                                </td>


                                <td>
                                    {{ $purchase->supplier_name ?: '—' }}
                                </td>


                                <td>
                                    {{
                                        $purchase->purchase_date
                                            ?->format('d M Y')
                                            ?? '—'
                                    }}
                                </td>


                                <td>
                                    {{ $purchase->items->sum('quantity') }}
                                </td>


                                <td>

                                    <span class="purchase-total">
                                        ${{ number_format(
                                            (float) $purchase->total_amount,
                                            2
                                        ) }}
                                    </span>

                                </td>


                                <td>

                                    @if($purchase->status === 'received')

                                        <span class="purchase-status received">
                                            <span class="purchase-status-dot"></span>
                                            Received
                                        </span>

                                    @elseif($purchase->status === 'cancelled')

                                        <span class="purchase-status cancelled">
                                            <span class="purchase-status-dot"></span>
                                            Cancelled
                                        </span>

                                    @else

                                        <span class="purchase-status draft">
                                            <span class="purchase-status-dot"></span>
                                            Draft
                                        </span>

                                    @endif

                                </td>


                                <td>

                                    <div class="purchase-actions">

                                        {{-- VIEW DIALOG --}}

                                        <button
                                            type="button"
                                            class="purchase-view-button"
                                            onclick="openPurchaseViewModal({{ $purchase->id }})"
                                        >
                                            View
                                        </button>


                                        {{-- RECEIVE --}}

                                        @if($purchase->status === 'draft')

                                            <button
                                                type="button"
                                                class="purchase-receive-button"
                                                onclick="openReceiveDialog({{ $purchase->id }})"
                                            >
                                                Receive
                                            </button>

                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>


            <div style="padding:18px 20px;">
                {{ $purchases->links() }}
            </div>

        @else

            <div class="purchase-empty">

                <div class="purchase-empty-icon">
                    +
                </div>

                <div class="purchase-empty-title">
                    No purchases found
                </div>

                <div class="purchase-empty-text">
                    There are no purchase records in the database.
                </div>

            </div>

        @endif

    </div>

</div>



{{-- =========================================================
     NEW PURCHASE MODAL
========================================================== --}}

<div
    class="purchase-modal-backdrop"
    id="purchaseModal"
    onclick="closePurchaseModalOnBackdrop(event)"
>

    <div
        class="purchase-modal"
        onclick="event.stopPropagation()"
    >

        <form
            id="purchaseForm"
            method="POST"
            action="{{ route('admin.purchases.store') }}"
        >

            @csrf


            <div class="purchase-modal-header">

                <div>

                    <div class="purchase-modal-title">
                        New Purchase
                    </div>

                    <div class="purchase-modal-subtitle">
                        Create a purchase using products and variants from the database.
                    </div>

                </div>


                <button
                    type="button"
                    class="purchase-modal-close"
                    onclick="closePurchaseModal()"
                >
                    ×
                </button>

            </div>


            <div class="purchase-modal-body">


                {{-- PURCHASE INFORMATION --}}

                <div class="purchase-form-grid">

                    <div class="purchase-form-field">

                        <label>
                            Supplier Name
                        </label>

                        <input
                            type="text"
                            name="supplier_name"
                            value="{{ old('supplier_name') }}"
                            placeholder="Enter supplier name"
                        >

                    </div>


                    <div class="purchase-form-field">

                        <label>
                            Purchase Date
                        </label>

                        <input
                            type="date"
                            name="purchase_date"
                            value="{{ old(
                                'purchase_date',
                                now()->format('Y-m-d')
                            ) }}"
                            required
                        >

                    </div>


                    <div class="purchase-form-field">

                        <label>
                            Status
                        </label>

                        <select
                            name="status"
                            required
                        >

                            <option
                                value="draft"
                                {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}
                            >
                                Draft
                            </option>

                            <option
                                value="received"
                                {{ old('status') === 'received' ? 'selected' : '' }}
                            >
                                Received
                            </option>

                            <option
                                value="cancelled"
                                {{ old('status') === 'cancelled' ? 'selected' : '' }}
                            >
                                Cancelled
                            </option>

                        </select>

                    </div>


                    <div class="purchase-form-field full">

                        <label>
                            Note
                        </label>

                        <textarea
                            name="note"
                            placeholder="Optional note..."
                        >{{ old('note') }}</textarea>

                    </div>

                </div>



                {{-- ITEMS --}}

                <div class="purchase-items-section">

                    <div class="purchase-items-header">

                        <div class="purchase-items-title">
                            Purchase Items
                        </div>


                        <button
                            type="button"
                            class="purchase-add-item-button"
                            onclick="openPurchaseItemDialog()"
                        >
                            + Add Item
                        </button>

                    </div>


                    <div class="purchase-items-table-wrapper">

                        <table class="purchase-items-table">

                            <thead>

                                <tr>

                                    <th>
                                        Product / Variant
                                    </th>

                                    <th>
                                        Quantity
                                    </th>

                                    <th>
                                        Cost Price
                                    </th>

                                    <th>
                                        Subtotal
                                    </th>

                                    <th></th>

                                </tr>

                            </thead>


                            <tbody id="purchaseItemsBody">

                                <tr id="purchaseNoItemsRow">

                                    <td
                                        colspan="5"
                                        class="purchase-no-items"
                                    >
                                        No items added yet.
                                        Click <strong>+ Add Item</strong>.
                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>


                    <div class="purchase-total-box">

                        <div class="purchase-total-inner">

                            <div class="purchase-total-row">

                                <span>
                                    Grand Total
                                </span>

                                <strong id="purchaseGrandTotal">
                                    $0.00
                                </strong>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <div class="purchase-modal-footer">

                <button
                    type="button"
                    class="purchase-cancel-button"
                    onclick="closePurchaseModal()"
                >
                    Cancel
                </button>


                <button
                    type="button"
                    class="purchase-save-button"
                    onclick="openPurchaseConfirmDialog()"
                >
                    Save Purchase
                </button>

            </div>

        </form>

    </div>

</div>



{{-- =========================================================
     ADD ITEM DIALOG
========================================================== --}}

<div
    class="purchase-modal-backdrop"
    id="purchaseItemDialog"
    onclick="closePurchaseItemDialogOnBackdrop(event)"
>

    <div
        class="purchase-item-dialog"
        onclick="event.stopPropagation()"
    >

        <div class="purchase-item-dialog-header">

            <div class="purchase-item-dialog-title">
                Add Purchase Item
            </div>


            <button
                type="button"
                class="purchase-modal-close"
                onclick="closePurchaseItemDialog()"
            >
                ×
            </button>

        </div>


        <div class="purchase-item-dialog-body">

            <div class="purchase-item-dialog-grid">

                <div class="purchase-dialog-field full">

                    <label>
                        Product / Variant
                    </label>

                    <select
                        id="dialog_variant"
                        onchange="setDialogCostPrice()"
                    >

                        <option value="">
                            Select product / variant
                        </option>

                        @foreach($variants as $variant)

                            <option
                                value="{{ $variant->id }}"
                                data-cost="{{ $variant->cost_price }}"
                            >

                                {{ $variant->product?->name }}

                                @if($variant->size)
                                    · Size {{ $variant->size }}
                                @endif

                                @if($variant->color)
                                    · {{ $variant->color }}
                                @endif

                            </option>

                        @endforeach

                    </select>

                </div>


                <div class="purchase-dialog-field">

                    <label>
                        Quantity
                    </label>

                    <input
                        id="dialog_quantity"
                        type="number"
                        min="1"
                        step="1"
                        value="1"
                    >

                </div>


                <div class="purchase-dialog-field">

                    <label>
                        Cost Price
                    </label>

                    <input
                        id="dialog_cost_price"
                        type="number"
                        min="0"
                        step="0.01"
                        placeholder="0.00"
                    >

                </div>

            </div>

        </div>


        <div class="purchase-dialog-footer">

            <button
                type="button"
                class="purchase-cancel-button"
                onclick="closePurchaseItemDialog()"
            >
                Cancel
            </button>


            <button
                type="button"
                class="purchase-save-button"
                onclick="addPurchaseItem()"
            >
                Add Item
            </button>

        </div>

    </div>

</div>



{{-- =========================================================
     VIEW PURCHASE DIALOGS
========================================================== --}}

@foreach($purchases as $purchase)

    <div
        class="purchase-modal-backdrop purchase-view-modal"
        id="purchaseViewModal-{{ $purchase->id }}"
        onclick="closePurchaseViewModalOnBackdrop(
            event,
            {{ $purchase->id }}
        )"
    >

        <div
            class="purchase-modal purchase-view-modal-box"
            onclick="event.stopPropagation()"
        >

            <div class="purchase-modal-header">

                <div>

                    <div class="purchase-modal-title">

                        Purchase #PO-{{
                            str_pad(
                                $purchase->id,
                                5,
                                '0',
                                STR_PAD_LEFT
                            )
                        }}

                    </div>

                    <div class="purchase-modal-subtitle">
                        Purchase details and receiving
                    </div>

                </div>


                <button
                    type="button"
                    class="purchase-modal-close"
                    onclick="closePurchaseViewModal({{ $purchase->id }})"
                >
                    ×
                </button>

            </div>


            <div class="purchase-modal-body">


                {{-- INFORMATION --}}

                <div class="purchase-view-info-grid">

                    <div class="purchase-view-info-item">

                        <span>
                            Purchase ID
                        </span>

                        <strong>
                            #PO-{{
                                str_pad(
                                    $purchase->id,
                                    5,
                                    '0',
                                    STR_PAD_LEFT
                                )
                            }}
                        </strong>

                    </div>


                    <div class="purchase-view-info-item">

                        <span>
                            Supplier
                        </span>

                        <strong>
                            {{ $purchase->supplier_name ?: '—' }}
                        </strong>

                    </div>


                    <div class="purchase-view-info-item">

                        <span>
                            Purchase Date
                        </span>

                        <strong>
                            {{
                                $purchase->purchase_date
                                    ?->format('d M Y')
                                    ?? '—'
                            }}
                        </strong>

                    </div>


                    <div class="purchase-view-info-item">

                        <span>
                            Status
                        </span>

                        <strong>

                            @if($purchase->status === 'received')

                                <span class="purchase-status received">
                                    <span class="purchase-status-dot"></span>
                                    Received
                                </span>

                            @elseif($purchase->status === 'cancelled')

                                <span class="purchase-status cancelled">
                                    <span class="purchase-status-dot"></span>
                                    Cancelled
                                </span>

                            @else

                                <span class="purchase-status draft">
                                    <span class="purchase-status-dot"></span>
                                    Draft
                                </span>

                            @endif

                        </strong>

                    </div>

                </div>



                {{-- PRODUCTS --}}

                <div class="purchase-view-products">

                    <div class="purchase-view-section-title">
                        Products
                    </div>


                    <div class="purchase-table-wrapper">

                        <table class="purchase-detail-table">

                            <thead>

                                <tr>

                                    <th>
                                        Product
                                    </th>

                                    <th>
                                        Variant
                                    </th>

                                    <th>
                                        Quantity
                                    </th>

                                    <th>
                                        Cost
                                    </th>

                                    <th>
                                        Subtotal
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                @foreach($purchase->items as $item)

                                    <tr>

                                        <td>

                                            <span class="purchase-product">

                                                {{
                                                    $item
                                                        ->productVariant
                                                        ?->product
                                                        ?->name
                                                        ?? '—'
                                                }}

                                            </span>

                                        </td>


                                        <td>

                                            {{
                                                $item
                                                    ->productVariant
                                                    ?->size
                                                    ?? '—'
                                            }}

                                            @if(
                                                $item
                                                    ->productVariant
                                                    ?->color
                                            )

                                                /
                                                {{
                                                    $item
                                                        ->productVariant
                                                        ->color
                                                }}

                                            @endif

                                        </td>


                                        <td>
                                            {{ $item->quantity }}
                                        </td>


                                        <td>
                                            ${{ number_format(
                                                (float) $item->cost_price,
                                                2
                                            ) }}
                                        </td>


                                        <td>
                                            <strong>
                                                ${{ number_format(
                                                    (float) $item->subtotal,
                                                    2
                                                ) }}
                                            </strong>
                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                </div>



                {{-- SUMMARY --}}

                <div class="purchase-view-summary">

                    <div class="purchase-view-summary-row">

                        <span>
                            Total Items
                        </span>

                        <strong>
                            {{ $purchase->items->sum('quantity') }}
                        </strong>

                    </div>


                    <div class="purchase-view-summary-row total">

                        <span>
                            TOTAL
                        </span>

                        <strong>
                            ${{ number_format(
                                (float) $purchase->total_amount,
                                2
                            ) }}
                        </strong>

                    </div>

                </div>



                {{-- NOTE --}}

                @if($purchase->note)

                    <div class="purchase-view-note">

                        <div class="purchase-view-section-title">
                            Note
                        </div>

                        <div class="purchase-view-note-text">
                            {{ $purchase->note }}
                        </div>

                    </div>

                @endif

            </div>


            {{-- VIEW FOOTER --}}

            <div class="purchase-modal-footer">

                @if($purchase->status === 'draft')

                    <button
                        type="button"
                        class="purchase-save-button"
                        onclick="openReceiveDialog({{ $purchase->id }})"
                    >
                        Receive Goods
                    </button>

                @endif


                <button
                    type="button"
                    class="purchase-cancel-button"
                    onclick="closePurchaseViewModal({{ $purchase->id }})"
                >
                    Close
                </button>

            </div>

        </div>

    </div>

@endforeach



{{-- =========================================================
     CONFIRM SAVE DIALOG
========================================================== --}}

<div
    class="purchase-modal-backdrop"
    id="purchaseConfirmDialog"
    onclick="closePurchaseConfirmOnBackdrop(event)"
>

    <div
        class="purchase-confirm-dialog"
        onclick="event.stopPropagation()"
    >

        <div class="purchase-confirm-icon">
            ✓
        </div>

        <div class="purchase-confirm-title">
            Save Purchase?
        </div>

        <div class="purchase-confirm-text">
            Please confirm that the purchase information and items are correct.
        </div>

        <div class="purchase-confirm-actions">

            <button
                type="button"
                class="purchase-cancel-button"
                onclick="closePurchaseConfirmDialog()"
            >
                Cancel
            </button>


            <button
                type="button"
                class="purchase-save-button"
                onclick="submitPurchaseForm()"
            >
                Confirm Save
            </button>

        </div>

    </div>

</div>



{{-- =========================================================
     RECEIVE CONFIRM DIALOG
========================================================== --}}

@foreach($purchases as $purchase)

    @if($purchase->status === 'draft')

        <div
            class="purchase-modal-backdrop"
            id="receiveDialog-{{ $purchase->id }}"
            onclick="closeReceiveDialogOnBackdrop(
                event,
                {{ $purchase->id }}
            )"
        >

            <div
                class="purchase-confirm-dialog"
                onclick="event.stopPropagation()"
            >

                <div class="purchase-confirm-icon">
                    ✓
                </div>

                <div class="purchase-confirm-title">
                    Receive Purchase?
                </div>

                <div class="purchase-confirm-text">

                    Receive this purchase and add the quantities
                    to inventory?

                </div>


                <div class="purchase-confirm-actions">

                    <button
                        type="button"
                        class="purchase-cancel-button"
                        onclick="closeReceiveDialog({{ $purchase->id }})"
                    >
                        Cancel
                    </button>


                    <form
                        method="POST"
                        action="{{ route(
                            'admin.purchases.receive',
                            $purchase->id
                        ) }}"
                    >

                        @csrf

                        <button
                            type="submit"
                            class="purchase-save-button"
                        >
                            Confirm Receive
                        </button>

                    </form>

                </div>

            </div>

        </div>

    @endif

@endforeach



<script>

    /* =========================================================
       NEW PURCHASE MODAL
    ========================================================= */

    function openPurchaseModal() {

        const modal =
            document.getElementById('purchaseModal');

        if (!modal) {
            return;
        }

        modal.classList.add('show');

        document.body.style.overflow = 'hidden';

        recalculatePurchaseTotal();
    }


    function closePurchaseModal() {

        const modal =
            document.getElementById('purchaseModal');

        if (!modal) {
            return;
        }

        modal.classList.remove('show');

        document.body.style.overflow = '';
    }


    function closePurchaseModalOnBackdrop(event) {

        if (
            event.target.id === 'purchaseModal'
        ) {
            closePurchaseModal();
        }

    }



    /* =========================================================
       ADD ITEM DIALOG
    ========================================================= */

    function openPurchaseItemDialog() {

        const dialog =
            document.getElementById(
                'purchaseItemDialog'
            );

        if (!dialog) {
            return;
        }

        dialog.classList.add('show');

        document.body.style.overflow = 'hidden';
    }


    function closePurchaseItemDialog() {

        const dialog =
            document.getElementById(
                'purchaseItemDialog'
            );

        if (!dialog) {
            return;
        }

        dialog.classList.remove('show');

        document.body.style.overflow = 'hidden';
    }


    function closePurchaseItemDialogOnBackdrop(event) {

        if (
            event.target.id ===
            'purchaseItemDialog'
        ) {
            closePurchaseItemDialog();
        }

    }



    /* =========================================================
       AUTO COST PRICE
    ========================================================= */

    function setDialogCostPrice() {

        const select =
            document.getElementById(
                'dialog_variant'
            );

        const costInput =
            document.getElementById(
                'dialog_cost_price'
            );

        if (!select || !costInput) {
            return;
        }

        const option =
            select.options[
                select.selectedIndex
            ];

        costInput.value =
            option?.dataset?.cost || '';

    }



    /* =========================================================
       ADD PURCHASE ITEM
    ========================================================= */

    function addPurchaseItem() {

        const variantSelect =
            document.getElementById(
                'dialog_variant'
            );

        const quantityInput =
            document.getElementById(
                'dialog_quantity'
            );

        const costInput =
            document.getElementById(
                'dialog_cost_price'
            );


        const variantId =
            variantSelect?.value;

        const quantity =
            parseInt(
                quantityInput?.value || 0,
                10
            );

        const costPrice =
            parseFloat(
                costInput?.value || 0
            );


        if (!variantId) {

            alert(
                'Please select a product / variant.'
            );

            return;
        }


        if (
            Number.isNaN(quantity) ||
            quantity < 1
        ) {

            alert(
                'Quantity must be at least 1.'
            );

            return;
        }


        if (
            Number.isNaN(costPrice) ||
            costPrice < 0
        ) {

            alert(
                'Please enter a valid cost price.'
            );

            return;
        }


        const body =
            document.getElementById(
                'purchaseItemsBody'
            );


        const emptyRow =
            document.getElementById(
                'purchaseNoItemsRow'
            );


        if (emptyRow) {
            emptyRow.remove();
        }


        /* Prevent duplicate variant */

        const existingRows =
            body.querySelectorAll(
                'tr[data-variant-id]'
            );


        for (const row of existingRows) {

            if (
                String(row.dataset.variantId) ===
                String(variantId)
            ) {

                const qtyInput =
                    row.querySelector(
                        '.purchase-quantity'
                    );

                if (qtyInput) {

                    qtyInput.value =
                        parseInt(
                            qtyInput.value || 0,
                            10
                        ) + quantity;

                }


                const priceInput =
                    row.querySelector(
                        '.purchase-cost'
                    );

                if (priceInput) {
                    priceInput.value =
                        costPrice.toFixed(2);
                }


                closePurchaseItemDialog();

                recalculatePurchaseTotal();

                resetPurchaseItemDialog();

                return;
            }

        }


        const index =
            getNextPurchaseItemIndex();


        const selectedOption =
            variantSelect.options[
                variantSelect.selectedIndex
            ];


        const variantText =
            selectedOption.textContent.trim();


        const row =
            document.createElement('tr');


        row.dataset.variantId =
            variantId;


        row.dataset.itemIndex =
            index;


        row.innerHTML = `

            <td class="purchase-item-product">

                <select
                    name="items[${index}][product_variant_id]"
                    class="purchase-item-input"
                    required
                >

                    <option
                        value="${variantId}"
                        selected
                    >
                        ${escapeHtml(variantText)}
                    </option>

                </select>

            </td>


            <td>

                <input
                    type="number"
                    name="items[${index}][quantity]"
                    class="purchase-item-input purchase-quantity"
                    value="${quantity}"
                    min="1"
                    step="1"
                    oninput="recalculatePurchaseTotal()"
                    required
                >

            </td>


            <td>

                <input
                    type="number"
                    name="items[${index}][cost_price]"
                    class="purchase-item-input purchase-cost"
                    value="${costPrice.toFixed(2)}"
                    min="0"
                    step="0.01"
                    oninput="recalculatePurchaseTotal()"
                    required
                >

            </td>


            <td class="purchase-item-subtotal">
                $0.00
            </td>


            <td>

                <button
                    type="button"
                    class="purchase-item-remove"
                    onclick="removePurchaseItem(this)"
                    title="Remove"
                >
                    ×
                </button>

            </td>

        `;


        body.appendChild(row);


        closePurchaseItemDialog();

        resetPurchaseItemDialog();

        recalculatePurchaseTotal();
    }



    /* =========================================================
       NEXT ITEM INDEX
    ========================================================= */

    function getNextPurchaseItemIndex() {

        const rows =
            document.querySelectorAll(
                '#purchaseItemsBody tr[data-item-index]'
            );


        let maxIndex = -1;


        rows.forEach(function(row) {

            const index =
                parseInt(
                    row.dataset.itemIndex,
                    10
                );


            if (
                !Number.isNaN(index) &&
                index > maxIndex
            ) {

                maxIndex = index;

            }

        });


        return maxIndex + 1;
    }



    /* =========================================================
       REMOVE ITEM
    ========================================================= */

    function removePurchaseItem(button) {

        const row =
            button.closest('tr');


        if (!row) {
            return;
        }


        row.remove();


        const body =
            document.getElementById(
                'purchaseItemsBody'
            );


        const rows =
            body.querySelectorAll(
                'tr[data-variant-id]'
            );


        if (rows.length === 0) {

            const emptyRow =
                document.createElement('tr');


            emptyRow.id =
                'purchaseNoItemsRow';


            emptyRow.innerHTML = `

                <td
                    colspan="5"
                    class="purchase-no-items"
                >
                    No items added yet.
                    Click <strong>+ Add Item</strong>.
                </td>

            `;


            body.appendChild(emptyRow);
        }


        recalculatePurchaseTotal();
    }



    /* =========================================================
       TOTAL
    ========================================================= */

    function recalculatePurchaseTotal() {

        const rows =
            document.querySelectorAll(
                '#purchaseItemsBody tr[data-variant-id]'
            );


        let total = 0;


        rows.forEach(function(row) {

            const quantity =
                parseFloat(
                    row.querySelector(
                        '.purchase-quantity'
                    )?.value || 0
                );


            const cost =
                parseFloat(
                    row.querySelector(
                        '.purchase-cost'
                    )?.value || 0
                );


            const subtotal =
                quantity * cost;


            total += subtotal;


            const subtotalElement =
                row.querySelector(
                    '.purchase-item-subtotal'
                );


            if (subtotalElement) {

                subtotalElement.textContent =
                    '$' + subtotal.toFixed(2);

            }

        });


        const totalElement =
            document.getElementById(
                'purchaseGrandTotal'
            );


        if (totalElement) {

            totalElement.textContent =
                '$' + total.toFixed(2);

        }

    }



    /* =========================================================
       RESET ADD ITEM DIALOG
    ========================================================= */

    function resetPurchaseItemDialog() {

        const variant =
            document.getElementById(
                'dialog_variant'
            );

        const quantity =
            document.getElementById(
                'dialog_quantity'
            );

        const cost =
            document.getElementById(
                'dialog_cost_price'
            );


        if (variant) {
            variant.value = '';
        }


        if (quantity) {
            quantity.value = '1';
        }


        if (cost) {
            cost.value = '';
        }

    }



    /* =========================================================
       CONFIRM SAVE
    ========================================================= */

    function openPurchaseConfirmDialog() {

        const rows =
            document.querySelectorAll(
                '#purchaseItemsBody tr[data-variant-id]'
            );


        if (rows.length === 0) {

            alert(
                'Please add at least one purchase item.'
            );

            return;
        }


        const dialog =
            document.getElementById(
                'purchaseConfirmDialog'
            );


        if (dialog) {

            dialog.classList.add('show');

            document.body.style.overflow =
                'hidden';

        }

    }


    function closePurchaseConfirmDialog() {

        const dialog =
            document.getElementById(
                'purchaseConfirmDialog'
            );


        if (dialog) {
            dialog.classList.remove('show');
        }


        document.body.style.overflow =
            'hidden';
    }


    function closePurchaseConfirmOnBackdrop(event) {

        if (
            event.target.id ===
            'purchaseConfirmDialog'
        ) {

            closePurchaseConfirmDialog();

        }

    }


    function submitPurchaseForm() {

        const form =
            document.getElementById(
                'purchaseForm'
            );


        if (form) {
            form.submit();
        }

    }



    /* =========================================================
       VIEW PURCHASE
    ========================================================= */

    function openPurchaseViewModal(purchaseId) {

        const modal =
            document.getElementById(
                'purchaseViewModal-' + purchaseId
            );


        if (!modal) {
            return;
        }


        modal.classList.add('show');

        document.body.style.overflow =
            'hidden';
    }


    function closePurchaseViewModal(purchaseId) {

        const modal =
            document.getElementById(
                'purchaseViewModal-' + purchaseId
            );


        if (!modal) {
            return;
        }


        modal.classList.remove('show');

        document.body.style.overflow =
            '';
    }


    function closePurchaseViewModalOnBackdrop(
        event,
        purchaseId
    ) {

        if (
            event.target.id ===
            'purchaseViewModal-' + purchaseId
        ) {

            closePurchaseViewModal(
                purchaseId
            );

        }

    }



    /* =========================================================
       RECEIVE DIALOG
    ========================================================= */

    function openReceiveDialog(purchaseId) {

        const dialog =
            document.getElementById(
                'receiveDialog-' + purchaseId
            );


        if (!dialog) {
            return;
        }


        /*
         * Close View Dialog if it is open.
         */

        const viewModal =
            document.getElementById(
                'purchaseViewModal-' + purchaseId
            );


        if (viewModal) {
            viewModal.classList.remove('show');
        }


        dialog.classList.add('show');

        document.body.style.overflow =
            'hidden';
    }


    function closeReceiveDialog(purchaseId) {

        const dialog =
            document.getElementById(
                'receiveDialog-' + purchaseId
            );


        if (dialog) {
            dialog.classList.remove('show');
        }


        document.body.style.overflow =
            '';
    }


    function closeReceiveDialogOnBackdrop(
        event,
        purchaseId
    ) {

        if (
            event.target.id ===
            'receiveDialog-' + purchaseId
        ) {

            closeReceiveDialog(
                purchaseId
            );

        }

    }



    /* =========================================================
       HTML ESCAPE
    ========================================================= */

    function escapeHtml(value) {

        const div =
            document.createElement('div');


        div.textContent =
            value;


        return div.innerHTML;
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
                    '.purchase-modal-backdrop.show'
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
       AUTO OPEN AFTER VALIDATION ERROR
    ========================================================= */

    document.addEventListener(
        'DOMContentLoaded',
        function() {

            recalculatePurchaseTotal();

            @if($errors->any())

                openPurchaseModal();

            @endif

        }
    );

</script>

@endsection