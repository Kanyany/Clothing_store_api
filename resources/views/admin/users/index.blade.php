@extends('admin.layouts.app')

@section('title', 'Users & Roles')

@section('content')

<style>
    .users-page {
        padding: 24px;
        color: #24201E;
    }

    .users-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        margin-bottom: 22px;
    }

    .users-title h1 {
        margin: 0;
        font-size: 24px;
        font-weight: 700;
        color: #2C1E17;
    }

    .users-title p {
        margin: 6px 0 0;
        font-size: 13px;
        color: #8B8580;
    }

    .btn {
        border: 0;
        border-radius: 8px;
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: .2s ease;
    }

    .btn-primary {
        background: #6F4E37;
        color: #fff;
    }

    .btn-primary:hover {
        background: #2C1E17;
    }

    .btn-secondary {
        background: #F5EDE3;
        color: #6F4E37;
    }

    .btn-danger {
        background: #ff0000;
        color: #fff;
    }

    .btn-success {
        background: #64DD17;
        color: #fff;
    }

    .btn-light {
        background: #FAF8F5;
        color: #6F4E37;
        border: 1px solid #E7DDD2;
    }

    .filter-card {
        background: #fff;
        border: 1px solid #E7DDD2;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 18px;
    }

    .filter-form {
        display: grid;
        grid-template-columns: 1fr 220px auto auto;
        gap: 10px;
        align-items: end;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .form-group label {
        font-size: 12px;
        font-weight: 600;
        color: #6F4E37;
    }

    .form-control {
        width: 100%;
        box-sizing: border-box;
        border: 1px solid #DDD2C7;
        border-radius: 8px;
        padding: 10px 11px;
        font-size: 13px;
        background: #fff;
        color: #24201E;
        outline: none;
    }

    .form-control:focus {
        border-color: #A66A44;
        box-shadow: 0 0 0 2px rgba(166, 106, 68, .10);
    }

    .table-card {
        background: #fff;
        border: 1px solid #E7DDD2;
        border-radius: 12px;
        overflow: hidden;
    }

    .table-wrapper {
        width: 100%;
        overflow-x: auto;
    }

    .users-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 900px;
    }

    .users-table th {
        background: #F5EDE3;
        color: #6F4E37;
        text-align: left;
        font-size: 11px;
        font-weight: 700;
        padding: 12px 14px;
        white-space: nowrap;
    }

    .users-table td {
        border-top: 1px solid #EFE7DF;
        padding: 12px 14px;
        font-size: 12px;
        color: #24201E;
        vertical-align: middle;
    }

    .users-table tr:hover td {
        background: #FCFAF7;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: #C99A6B;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 700;
        flex-shrink: 0;
    }

    .user-name {
        font-weight: 600;
        color: #2C1E17;
    }

    .user-email {
        margin-top: 3px;
        font-size: 11px;
        color: #8B8580;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 5px 9px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
    }

    .badge-role {
        background: #F5EDE3;
        color: #6F4E37;
    }

    .badge-active {
        background: #E8F2E6;
        color: #64DD17;
    }

    .badge-inactive {
        background: #F9E8E5;
        color: #ff0000;
    }

    .action-buttons {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .action-btn {
        border: 1px solid #E3D8CD;
        background: #fff;
        color: #6F4E37;
        border-radius: 6px;
        padding: 6px 9px;
        font-size: 10px;
        cursor: pointer;
        font-weight: 600;
    }

    .action-btn:hover {
        background: #F5EDE3;
    }

    .action-btn.delete {
        color: #ff0000;
    }

    .empty-state {
        text-align: center;
        padding: 50px 20px;
        color: #8B8580;
    }

    .empty-state-title {
        font-size: 15px;
        font-weight: 700;
        color: #6F4E37;
        margin-bottom: 5px;
    }

    .pagination-area {
        padding: 14px 16px;
        border-top: 1px solid #EFE7DF;
    }

    .alert {
        border-radius: 8px;
        padding: 11px 14px;
        margin-bottom: 16px;
        font-size: 12px;
    }

    .alert-success {
        background: #E8F2E6;
        color: #4D7549;
    }

    .alert-error {
        background: #F9E8E5;
        color: #ff0000;
    }

    /* Modal */

    .modal {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(36, 32, 30, .48);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .modal.show {
        display: flex;
    }

    .modal-box {
        width: min(680px, 100%);
        max-height: 90vh;
        overflow-y: auto;
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 20px 60px rgba(0,0,0,.18);
    }

    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 20px;
        border-bottom: 1px solid #EFE7DF;
    }

    .modal-header h2 {
        margin: 0;
        font-size: 17px;
        color: #2C1E17;
    }

    .modal-close {
        width: 30px;
        height: 30px;
        border: 0;
        border-radius: 50%;
        background: #F5EDE3;
        color: #6F4E37;
        cursor: pointer;
        font-size: 17px;
    }

    .modal-body {
        padding: 20px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    .form-grid .full {
        grid-column: 1 / -1;
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        padding: 15px 20px;
        border-top: 1px solid #EFE7DF;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .detail-item {
        background: #FAF8F5;
        border-radius: 8px;
        padding: 11px 13px;
    }

    .detail-item.full {
        grid-column: 1 / -1;
    }

    .detail-label {
        font-size: 10px;
        color: #8B8580;
        margin-bottom: 4px;
    }

    .detail-value {
        font-size: 13px;
        font-weight: 600;
        color: #2C1E17;
        word-break: break-word;
    }

    .password-note {
        font-size: 10px;
        color: #8B8580;
        margin-top: 4px;
    }

    @media (max-width: 900px) {
        .filter-form {
            grid-template-columns: 1fr 1fr;
        }

        .users-header {
            align-items: flex-start;
        }
    }

    @media (max-width: 650px) {
        .users-page {
            padding: 15px;
        }

        .users-header {
            flex-direction: column;
        }

        .filter-form,
        .form-grid,
        .detail-grid {
            grid-template-columns: 1fr;
        }

        .form-grid .full,
        .detail-item.full {
            grid-column: auto;
        }
    }
</style>

<div class="users-page">

    {{-- Header --}}
    <div class="users-header">
        <div class="users-title">
            <h1>Users & Roles</h1>
            <p>Manage users, roles and account access.</p>
        </div>

        <button type="button" class="btn btn-primary" onclick="openModal('addUserModal')">
            + Add User
        </button>
    </div>

    {{-- Messages --}}
   @if(session('success'))
        <div id="successDialog" class="success-dialog">
            <div class="success-dialog-icon">
                ✓
            </div>

            <div class="success-dialog-content">
                <div class="success-dialog-title">
                    Success
                </div>

                <div class="success-dialog-message">
                    {{ session('success') }}
                </div>
            </div>

            <button
                type="button"
                class="success-dialog-close"
                onclick="closeSuccessDialog()"
            >
                ×
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">
            <strong>Please check the following:</strong>
            <ul style="margin:6px 0 0 18px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Search / Filter --}}
<div class="filter-card">

    <form
        method="GET"
        action="{{ route('admin.users.index') }}"
        class="filter-form"
    >

        {{-- Search --}}

        <div class="form-group">

            <label for="search">
                Search
            </label>

            <input
                type="text"
                id="search"
                name="search"
                class="form-control"
                value="{{ request('search', '') }}"
                placeholder="Search name, email or phone..."
            >

        </div>


        {{-- Role Filter --}}

        <div class="form-group">

            <label for="role_id">
                Role
            </label>

            <select
                name="role_id"
                id="role_id"
                class="form-control"
                onchange="this.form.submit()"
            >

                <option value="">
                    All Roles
                </option>

                @foreach($roles as $role)

                    <option
                        value="{{ $role->id }}"
                        @selected((string) request('role_id') === (string) $role->id)
                    >
                        {{ $role->name }}
                    </option>

                @endforeach

            </select>

        </div>


        {{-- Search Button --}}

        <button
            type="submit"
            class="btn btn-primary"
        >
            Search
        </button>


        {{-- Reset --}}

        <a
            href="{{ route('admin.users.index') }}"
            class="btn btn-light"
            style="text-decoration:none;text-align:center;"
        >
            Reset
        </a>

    </form>

</div>

    {{-- User Table --}}
    <div class="table-card">
        <div class="table-wrapper">

            <table class="users-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Phone</th>
                        <th>Gender</th>
                        <th>Role</th>

                        @if($hasStatus)
                            <th>Status</th>
                        @endif

                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                @forelse($users as $user)

                    @php
                        $displayName = trim(
                            ($user->first_name ?? '') . ' ' .
                            ($user->last_name ?? '')
                        );

                        if ($displayName === '') {
                            $displayName = $user->name;
                        }

                        $initial = strtoupper(
                            mb_substr($displayName ?: 'U', 0, 1)
                        );

                        $status = strtolower((string)($user->status ?? 'active'));
                    @endphp

                    <tr>

                        <td>
                            <div class="user-info">

                                <div class="avatar">
                                    {{ $initial }}
                                </div>

                                <div>
                                    <div class="user-name">
                                        {{ $displayName }}
                                    </div>

                                    <div class="user-email">
                                        {{ $user->email }}
                                    </div>
                                </div>

                            </div>
                        </td>

                        <td>
                            {{ $user->phone ?: '—' }}
                        </td>

                        <td>
                            {{ $user->gender ?: '—' }}
                        </td>

                        <td>
                            <span class="badge badge-role">
                                {{ $user->role?->name ?? '—' }}
                            </span>
                        </td>

                        @if($hasStatus)
                            <td>
                                @if($status === 'active')
                                    <span class="badge badge-active">
                                        Active
                                    </span>
                                @else
                                    <span class="badge badge-inactive">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                        @endif

                        <td>
                            {{ $user->created_at?->format('d M Y') ?? '—' }}
                        </td>

                        <td>

                            <div class="action-buttons">

                                {{-- View --}}
                                <button
                                    type="button"
                                    class="action-btn"
                                    onclick='viewUser(@json($user->loadMissing("role")))'
                                >
                                    View
                                </button>

                                {{-- Edit --}}
                                <button
                                    type="button"
                                    class="action-btn"
                                    onclick='editUser(@json($user->loadMissing("role")))'
                                >
                                    Edit
                                </button>

                                {{-- Status --}}
                                @if($hasStatus)

                                    <form
                                        method="POST"
                                        action="{{ route('admin.users.toggle-status', $user) }}"
                                        style="display:inline;"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="action-btn"
                                        >
                                            {{ $status === 'active' ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>

                                @endif

                                {{-- Delete --}}
                                <button
                                    type="button"
                                    class="action-btn delete"
                                    onclick='openDeleteModal({{ $user->id }}, @json($displayName))'
                                >
                                    Delete
                                </button>

                            </div>

                            <form
                                id="delete-user-{{ $user->id }}"
                                method="POST"
                                action="{{ route('admin.users.destroy', $user) }}"
                                style="display:none;"
                            >
                                @csrf
                                @method('DELETE')
                            </form>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td
                            colspan="{{ $hasStatus ? 7 : 6 }}"
                        >
                            <div class="empty-state">
                                <div class="empty-state-title">
                                    No users found
                                </div>

                                <div>
                                    There are no users matching your search or filter.
                                </div>
                            </div>
                        </td>
                    </tr>

                @endforelse

                </tbody>
            </table>

        </div>

        @if($users->hasPages())
            <div class="pagination-area">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>


{{-- ========================================================= --}}
{{-- ADD USER MODAL --}}
{{-- ========================================================= --}}

<div id="addUserModal" class="modal">

    <div class="modal-box">

        <div class="modal-header">
            <h2>Add User</h2>

            <button
                type="button"
                class="modal-close"
                onclick="closeModal('addUserModal')"
            >
                ×
            </button>
        </div>

        <form
            method="POST"
            action="{{ route('admin.users.store') }}"
        >
            @csrf

            <div class="modal-body">

                <div class="form-grid">

                    <div class="form-group">
                        <label>Full Name *</label>

                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            required
                        >
                    </div>

                   <div class="form-group">

                    <label for="role_id">
                        Role
                    </label>

                    <select
                        name="role_id"
                        id="role_id"
                        class="form-control"
                        onchange="this.form.submit()"
                    >

                        <option value="">
                            All Roles
                        </option>

                        @foreach($roles as $role)

                            <option
                                value="{{ $role->id }}"
                                @selected((string) $roleId === (string) $role->id)
                            >
                                {{ $role->name }}
                            </option>

                        @endforeach

                    </select>

                </div>

                    <div class="form-group">
                        <label>First Name</label>

                        <input
                            type="text"
                            name="first_name"
                            class="form-control"
                        >
                    </div>

                    <div class="form-group">
                        <label>Last Name</label>

                        <input
                            type="text"
                            name="last_name"
                            class="form-control"
                        >
                    </div>

                    <div class="form-group">
                        <label>Gender</label>

                        <select
                            name="gender"
                            class="form-control"
                        >
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Phone</label>

                        <input
                            type="text"
                            name="phone"
                            class="form-control"
                        >
                    </div>

                    <div class="form-group">
                        <label>Email *</label>

                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label>City / Province</label>

                        <input
                            type="text"
                            name="city_province"
                            class="form-control"
                        >
                    </div>

                    <div class="form-group">
                        <label>Password *</label>

                        <input
                            type="password"
                            name="password"
                            class="form-control"
                            minlength="6"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label>Confirm Password *</label>

                        <input
                            type="password"
                            name="password_confirmation"
                            class="form-control"
                            minlength="6"
                            required
                        >
                    </div>

                </div>

            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-light"
                    onclick="closeModal('addUserModal')"
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Create User
                </button>

            </div>

        </form>

    </div>

</div>


{{-- ========================================================= --}}
{{-- VIEW USER MODAL --}}
{{-- ========================================================= --}}

<div id="viewUserModal" class="modal">

    <div class="modal-box">

        <div class="modal-header">

            <h2>User Details</h2>

            <button
                type="button"
                class="modal-close"
                onclick="closeModal('viewUserModal')"
            >
                ×
            </button>

        </div>

        <div class="modal-body">

            <div class="detail-grid">

                <div class="detail-item">
                    <div class="detail-label">Full Name</div>
                    <div class="detail-value" id="view-name">—</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Role</div>
                    <div class="detail-value" id="view-role">—</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">First Name</div>
                    <div class="detail-value" id="view-first-name">—</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Last Name</div>
                    <div class="detail-value" id="view-last-name">—</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Gender</div>
                    <div class="detail-value" id="view-gender">—</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Phone</div>
                    <div class="detail-value" id="view-phone">—</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Email</div>
                    <div class="detail-value" id="view-email">—</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">City / Province</div>
                    <div class="detail-value" id="view-city">—</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Status</div>
                    <div class="detail-value" id="view-status">—</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Created</div>
                    <div class="detail-value" id="view-created">—</div>
                </div>

            </div>

        </div>

        <div class="modal-footer">

            <button
                type="button"
                class="btn btn-light"
                onclick="closeModal('viewUserModal')"
            >
                Close
            </button>

        </div>

    </div>

</div>


{{-- ========================================================= --}}
{{-- EDIT USER MODAL --}}
{{-- ========================================================= --}}

<div id="editUserModal" class="modal">

    <div class="modal-box">

        <div class="modal-header">

            <h2>Edit User</h2>

            <button
                type="button"
                class="modal-close"
                onclick="closeModal('editUserModal')"
            >
                ×
            </button>

        </div>

        <form
            id="editUserForm"
            method="POST"
        >

            @csrf
            @method('PUT')

            <div class="modal-body">

                <div class="form-grid">

                    <div class="form-group">
                        <label>Full Name *</label>

                        <input
                            type="text"
                            name="name"
                            id="edit-name"
                            class="form-control"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label>Role *</label>

                        <select
                            name="role_id"
                            id="edit-role"
                            class="form-control"
                            required
                        >
                            <option value="">Select Role</option>

                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">
                                    {{ $role->name }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    <div class="form-group">
                        <label>First Name</label>

                        <input
                            type="text"
                            name="first_name"
                            id="edit-first-name"
                            class="form-control"
                        >
                    </div>

                    <div class="form-group">
                        <label>Last Name</label>

                        <input
                            type="text"
                            name="last_name"
                            id="edit-last-name"
                            class="form-control"
                        >
                    </div>

                    <div class="form-group">
                        <label>Gender</label>

                        <select
                            name="gender"
                            id="edit-gender"
                            class="form-control"
                        >
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Phone</label>

                        <input
                            type="text"
                            name="phone"
                            id="edit-phone"
                            class="form-control"
                        >
                    </div>

                    <div class="form-group">
                        <label>Email *</label>

                        <input
                            type="email"
                            name="email"
                            id="edit-email"
                            class="form-control"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label>City / Province</label>

                        <input
                            type="text"
                            name="city_province"
                            id="edit-city"
                            class="form-control"
                        >
                    </div>

                    <div class="form-group">
                        <label>New Password</label>

                        <input
                            type="password"
                            name="password"
                            id="edit-password"
                            class="form-control"
                            minlength="6"
                        >

                        <div class="password-note">
                            Leave blank if you do not want to change the password.
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Confirm New Password</label>

                        <input
                            type="password"
                            name="password_confirmation"
                            class="form-control"
                            minlength="6"
                        >
                    </div>

                </div>

            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-light"
                    onclick="closeModal('editUserModal')"
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Save Changes
                </button>

            </div>

        </form>

    </div>

</div>
{{-- ========================================================= --}}
{{-- DELETE USER MODAL --}}
{{-- ========================================================= --}}

<div id="deleteUserModal" class="modal">

    <div class="modal-box delete-modal-box">

        <div class="modal-header">

            <h2>Delete User</h2>

            <button
                type="button"
                class="modal-close"
                onclick="closeModal('deleteUserModal')"
            >
                ×
            </button>

        </div>

        <div class="modal-body">

            <div class="delete-icon">
                !
            </div>

            <div class="delete-content">

                <h3>
                    Are you sure?
                </h3>

                <p>
                    You are about to delete:
                </p>

                <div
                    id="delete-user-name"
                    class="delete-user-name"
                >
                    —
                </div>

                <p class="delete-warning">
                    This action cannot be undone.
                </p>

            </div>

        </div>

        <div class="modal-footer">

            <button
                type="button"
                class="btn btn-light"
                onclick="closeModal('deleteUserModal')"
            >
                Cancel
            </button>

            <button
                type="button"
                class="btn btn-danger"
                onclick="confirmDeleteUser()"
            >
                Delete User
            </button>

        </div>

    </div>

</div>


<style>
.delete-modal-box { width: min(430px, 100%); }
.delete-modal-box .modal-body { text-align:center; padding:26px 24px; }
.delete-icon { width:48px; height:48px; margin:0 auto 14px; display:flex; align-items:center; justify-content:center; border-radius:50%; background:#F9E8E5; color:#ff0000; font-size:22px; font-weight:700; }
.delete-content h3 { margin:0 0 8px; color:#2C1E17; font-size:18px; font-weight:700; }
.delete-content p { margin:5px 0; color:#8B8580; font-size:12px; }
.delete-user-name { max-width:280px; margin:12px auto; padding:10px 14px; border-radius:8px; background:#F5EDE3; color:#6F4E37; font-size:13px; font-weight:700; word-break:break-word; }
.delete-warning { color:#ff0000 !important; font-size:11px !important; font-weight:600; }

/* =========================================================
   SUCCESS DIALOG
   ========================================================= */

.success-dialog {
    position: fixed;
    top: 24px;
    right: 24px;
    z-index: 10000;

    width: 360px;
    max-width: calc(100vw - 48px);

    display: flex;
    align-items: center;
    gap: 12px;

    padding: 14px 16px;

    background: #FFFFFF;

    border: 1px solid #DCEAD9;
    border-left: 4px solid #64DD17;

    border-radius: 10px;

    box-shadow:
        0 10px 30px rgba(36, 32, 30, .15);

    animation: successDialogIn .25s ease forwards;
}

.success-dialog-icon {
    width: 34px;
    height: 34px;

    flex-shrink: 0;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background: #E8F2E6;
    color: #64DD17;

    font-size: 18px;
    font-weight: 700;
}

.success-dialog-content {
    flex: 1;
    min-width: 0;
}

.success-dialog-title {
    font-size: 13px;
    font-weight: 700;
    color: #2C1E17;
}

.success-dialog-message {
    margin-top: 3px;

    font-size: 11px;
    color: #8B8580;

    word-break: break-word;
}

.success-dialog-close {
    width: 26px;
    height: 26px;

    border: none;
    background: transparent;

    color: #8B8580;

    font-size: 18px;
    line-height: 1;

    cursor: pointer;
}

.success-dialog-close:hover {
    color: #2C1E17;
}

@keyframes successDialogIn {

    from {
        opacity: 0;
        transform: translateX(25px);
    }

    to {
        opacity: 1;
        transform: translateX(0);
    }

}

@media (max-width: 600px) {

    .success-dialog {
        top: 15px;
        right: 15px;
        left: 15px;

        width: auto;
        max-width: none;
    }

}

</style>

<script>

/*
|--------------------------------------------------------------------------
| OPEN MODAL
|--------------------------------------------------------------------------
*/

function openModal(id) {

    const modal = document.getElementById(id);

    if (modal) {
        modal.classList.add('show');
    }
}


/*
|--------------------------------------------------------------------------
| CLOSE MODAL
|--------------------------------------------------------------------------
*/

function closeModal(id) {

    const modal = document.getElementById(id);

    if (modal) {
        modal.classList.remove('show');
    }
}


/*
|--------------------------------------------------------------------------
| VIEW USER
|--------------------------------------------------------------------------
*/

function viewUser(user) {

    const firstName = user.first_name ?? '';
    const lastName = user.last_name ?? '';

    let displayName =
        (firstName + ' ' + lastName).trim();

    if (!displayName) {
        displayName = user.name ?? '—';
    }


    const name = document.getElementById('view-name');

    const role = document.getElementById('view-role');

    const firstNameElement =
        document.getElementById('view-first-name');

    const lastNameElement =
        document.getElementById('view-last-name');

    const gender =
        document.getElementById('view-gender');

    const phone =
        document.getElementById('view-phone');

    const email =
        document.getElementById('view-email');

    const city =
        document.getElementById('view-city');

    const status =
        document.getElementById('view-status');

    const created =
        document.getElementById('view-created');


    if (name) {
        name.textContent = displayName;
    }

    if (role) {
        role.textContent =
            user.role?.name ?? '—';
    }

    if (firstNameElement) {
        firstNameElement.textContent =
            user.first_name || '—';
    }

    if (lastNameElement) {
        lastNameElement.textContent =
            user.last_name || '—';
    }

    if (gender) {
        gender.textContent =
            user.gender || '—';
    }

    if (phone) {
        phone.textContent =
            user.phone || '—';
    }

    if (email) {
        email.textContent =
            user.email || '—';
    }

    if (city) {
        city.textContent =
            user.city_province || '—';
    }

    if (status) {
        status.textContent =
            user.status || 'Active';
    }

    if (created) {
        created.textContent =
            user.created_at
                ? new Date(user.created_at)
                    .toLocaleDateString()
                : '—';
    }


    openModal('viewUserModal');
}


/*
|--------------------------------------------------------------------------
| EDIT USER
|--------------------------------------------------------------------------
*/

function editUser(user) {

    const form =
        document.getElementById('editUserForm');

    if (!form) {
        return;
    }


    form.action =
        "{{ url('/admin/users') }}/" + user.id;


    const name =
        document.getElementById('edit-name');

    const role =
        document.getElementById('edit-role');

    const firstName =
        document.getElementById('edit-first-name');

    const lastName =
        document.getElementById('edit-last-name');

    const gender =
        document.getElementById('edit-gender');

    const phone =
        document.getElementById('edit-phone');

    const email =
        document.getElementById('edit-email');

    const city =
        document.getElementById('edit-city');

    const password =
        document.getElementById('edit-password');

    const passwordConfirmation =
        document.querySelector(
            '#editUserForm input[name="password_confirmation"]'
        );


    if (name) {
        name.value =
            user.name || '';
    }

    if (role) {
        role.value =
            user.role_id || '';
    }

    if (firstName) {
        firstName.value =
            user.first_name || '';
    }

    if (lastName) {
        lastName.value =
            user.last_name || '';
    }

    if (gender) {
        gender.value =
            user.gender || '';
    }

    if (phone) {
        phone.value =
            user.phone || '';
    }

    if (email) {
        email.value =
            user.email || '';
    }

    if (city) {
        city.value =
            user.city_province || '';
    }


    /*
    | Always clear password fields
    | when opening Edit.
    */

    if (password) {
        password.value = '';
    }

    if (passwordConfirmation) {
        passwordConfirmation.value = '';
    }


    openModal('editUserModal');
}


/*
|--------------------------------------------------------------------------
| DELETE USER
|--------------------------------------------------------------------------
*/

let deleteUserId = null;


function openDeleteModal(id, name) {

    deleteUserId = id;


    const nameElement =
        document.getElementById(
            'delete-user-name'
        );


    if (nameElement) {
        nameElement.textContent =
            name || 'this user';
    }


    openModal('deleteUserModal');
}


function confirmDeleteUser() {

    if (!deleteUserId) {
        return;
    }


    const form =
        document.getElementById(
            'delete-user-' + deleteUserId
        );


    if (form) {
        form.submit();
    }
}


/*
|--------------------------------------------------------------------------
| SUCCESS DIALOG
|--------------------------------------------------------------------------
*/

function closeSuccessDialog() {

    const dialog =
        document.getElementById(
            'successDialog'
        );


    if (dialog) {

        dialog.style.opacity = '0';

        dialog.style.transform =
            'translateX(25px)';

        setTimeout(function () {

            dialog.remove();

        }, 300);

    }
}


/*
|--------------------------------------------------------------------------
| PAGE LOADED
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'DOMContentLoaded',
    function () {


        /*
        |--------------------------------------------------------------------------
        | SUCCESS MESSAGE
        |--------------------------------------------------------------------------
        */

        const successDialog =
            document.getElementById(
                'successDialog'
            );


        /*
        | Show for exactly 3 seconds.
        */

        if (successDialog) {

            setTimeout(
                function () {

                    closeSuccessDialog();

                },
                3000
            );

        }


        /*
        |--------------------------------------------------------------------------
        | CLICK OUTSIDE MODAL
        |--------------------------------------------------------------------------
        */

        document
            .querySelectorAll('.modal')
            .forEach(function (modal) {

                modal.addEventListener(
                    'click',
                    function (event) {

                        if (
                            event.target === modal
                        ) {

                            modal.classList
                                .remove('show');

                        }

                    }
                );

            });


        /*
        |--------------------------------------------------------------------------
        | ESC KEY
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'keydown',
            function (event) {

                if (event.key === 'Escape') {

                    document
                        .querySelectorAll(
                            '.modal.show'
                        )
                        .forEach(
                            function (modal) {

                                modal.classList
                                    .remove('show');

                            }
                        );

                }

            }
        );

    }
);

</script>

@endsection