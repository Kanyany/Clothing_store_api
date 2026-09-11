<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display users.
     *
     * Supports:
     * - Search by name, first name, last name, email, phone
     * - Filter by role
     * - Pagination
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $roleId = $request->input('role_id');

        $usersQuery = User::query()
            ->with('role');

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */
        if ($search !== '') {
            $usersQuery->where(function ($query) use ($search) {
                $query
                    ->where('name', 'like', '%' . $search . '%')
                    ->orWhere('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Role Filter
        |--------------------------------------------------------------------------
        |
        | Filter through the User -> Role relationship.
        | This uses the actual roles table and prevents unrelated
        | users from appearing when a role is selected.
        |
        */
        if ($roleId !== null && $roleId !== '') {
            $usersQuery->whereHas('role', function ($query) use ($roleId) {
                $query->whereKey((int) $roleId);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */
        $users = $usersQuery
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */
        $roles = Role::query()
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Optional Status Column
        |--------------------------------------------------------------------------
        |
        | No migration or new column is created here.
        |
        */
        $hasStatus = Schema::hasColumn('users', 'status');

        return view('admin.users.index', compact(
            'users',
            'roles',
            'search',
            'roleId',
            'hasStatus'
        ));
    }

    /**
     * Store a new user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'role_id' => [
                'required',
                'integer',
                'exists:roles,id',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'first_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'last_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'gender' => [
                'nullable',
                Rule::in([
                    'male',
                    'female',
                    'other',
                    'Male',
                    'Female',
                    'Other',
                ]),
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'city_province' => [
                'nullable',
                'string',
                'max:150',
            ],

            'password' => [
                'required',
                'string',
                'min:6',
                'confirmed',
            ],
        ]);

        $user = new User();

        $user->role_id = $validated['role_id'];
        $user->name = $validated['name'];
        $user->first_name = $validated['first_name'] ?? null;
        $user->last_name = $validated['last_name'] ?? null;
        $user->gender = $validated['gender'] ?? null;
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? null;
        $user->city_province = $validated['city_province'] ?? null;

        /*
        |--------------------------------------------------------------------------
        | Password
        |--------------------------------------------------------------------------
        |
        | User.php uses Laravel's "hashed" password cast.
        | Passing the plain value here lets the model cast hash it.
        |
        */
        $user->password = $validated['password'];

        if (Schema::hasColumn('users', 'status')) {
            $user->status = 'active';
        }

        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Update an existing user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'role_id' => [
                'required',
                'integer',
                'exists:roles,id',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'first_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'last_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'gender' => [
                'nullable',
                Rule::in([
                    'male',
                    'female',
                    'other',
                    'Male',
                    'Female',
                    'Other',
                ]),
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'city_province' => [
                'nullable',
                'string',
                'max:150',
            ],

            'password' => [
                'nullable',
                'string',
                'min:6',
                'confirmed',
            ],
        ]);

        $user->role_id = $validated['role_id'];
        $user->name = $validated['name'];
        $user->first_name = $validated['first_name'] ?? null;
        $user->last_name = $validated['last_name'] ?? null;
        $user->gender = $validated['gender'] ?? null;
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? null;
        $user->city_province = $validated['city_province'] ?? null;

        /*
        |--------------------------------------------------------------------------
        | Change password only when a new password was entered.
        |--------------------------------------------------------------------------
        */
        if (
            isset($validated['password']) &&
            $validated['password'] !== ''
        ) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Activate / deactivate user.
     *
     * Only works if the existing users table already has a status column.
     * No migration is created here.
     */
    public function toggleStatus(User $user)
    {
        if (!Schema::hasColumn('users', 'status')) {
            return back()->withErrors([
                'status' => 'The users table does not contain a status column.',
            ]);
        }

        $currentStatus = strtolower(
            trim((string) $user->status)
        );

        $user->status = $currentStatus === 'active'
            ? 'inactive'
            : 'active';

        $user->save();

        return back()->with(
            'success',
            'User status updated successfully.'
        );
    }

    /**
     * Delete user.
     */
    public function destroy(User $user)
    {
        /*
        |--------------------------------------------------------------------------
        | Prevent the currently logged-in admin from deleting themselves.
        |--------------------------------------------------------------------------
        */
        if (
            auth()->check() &&
            auth()->id() === $user->id
        ) {
            return back()->withErrors([
                'user' => 'You cannot delete your own logged-in account.',
            ]);
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
