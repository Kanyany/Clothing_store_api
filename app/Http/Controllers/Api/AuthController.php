<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{



    /**
     * REGISTER
     *
     * Public registration.
     * New users get Staff role by default.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $staffRole = Role::where('name', 'Staff')->first();

        if (!$staffRole) {
            return response()->json([
                'status' => 'error',
                'message' => 'Staff role not found',
            ], 500);
        }

        $user = User::create([
            'role_id' => $staffRole->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Registration successful',
            'user' => $this->userResponse($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    /**
     * LOGIN
     */
    public function login(Request $request)
{
    $validated = $request->validate([
        'email' => 'required|string',
        'password' => 'required|string',
    ]);

    $identifier = $validated['email'];

    $user = User::with('role')
        ->where(function ($query) use ($identifier) {
            $query->where('email', $identifier)
                ->orWhere('phone', $identifier);
        })
        ->first();

    if (!$user || !Hash::check($validated['password'], $user->password)) {
        return response()->json([
            'status' => 'error',
            'message' => 'Invalid email/phone or password',
        ], 401);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'status' => 'success',
        'message' => 'Login successful',
        'user' => $this->userResponse($user),
        'token' => $token,
        'token_type' => 'Bearer',
    ], 200);
}

    /**
     * CURRENT USER
     */
    public function me(Request $request)
    {
        $user = $request->user()->load('role');

        return response()->json([
            'status' => 'success',
            'user' => $this->userResponse($user),
        ]);
    }

    /**
     * LOGOUT
     */
    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Logout successful',
        ]);
    }

    /**
     * Format user response
     */
    private function userResponse(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role ? [
                'id' => $user->role->id,
                'name' => $user->role->name,
            ] : null,
        ];
    }

    public function updateProfile(Request $request)
{
    $user = $request->user();

    $validated = $request->validate([
        'first_name' => ['sometimes', 'string', 'max:255'],
        'last_name' => ['sometimes', 'string', 'max:255'],
        'gender' => ['sometimes', 'nullable', 'string', 'max:50'],
        'phone' => ['sometimes', 'string', 'max:30'],
        'city_province' => ['sometimes', 'string', 'max:255'],
    ]);

    if (isset($validated['first_name']) || isset($validated['last_name'])) {
        $firstName = $validated['first_name'] ?? $user->first_name;
        $lastName = $validated['last_name'] ?? $user->last_name;

        $validated['name'] = trim($firstName . ' ' . $lastName);
    }

    $user->update($validated);

    return response()->json([
        'status' => 'success',
        'message' => 'Profile updated successfully',
        'data' => $user->fresh(),
    ]);
}
}