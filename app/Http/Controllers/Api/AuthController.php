<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | REGISTER
    |--------------------------------------------------------------------------
    */

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        $staffRole = Role::where(
            'name',
            'Staff'
        )->first();

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
            'password' => Hash::make(
                $validated['password']
            ),
        ]);

        $token = $user
            ->createToken('auth_token')
            ->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Registration successful',

            'user' => $this->userResponse($user),

            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }


    /*
    |--------------------------------------------------------------------------
    | LOGIN
    |--------------------------------------------------------------------------
    */

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => [
                'required',
                'string',
            ],

            'password' => [
                'required',
                'string',
            ],
        ]);

        $identifier = $validated['email'];

        $user = User::with('role')
            ->where(function ($query) use ($identifier) {
                $query
                    ->where('email', $identifier)
                    ->orWhere('phone', $identifier);
            })
            ->first();

        if (
            !$user ||
            !Hash::check(
                $validated['password'],
                $user->password
            )
        ) {
            return response()->json([
                'status' => 'error',
                'message' =>
                    'Invalid email/phone or password',
            ], 401);
        }

        $token = $user
            ->createToken('auth_token')
            ->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',

            // FIX:
            // Return complete real user profile data.
            'user' => $this->userResponse($user),

            'token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }


    /*
    |--------------------------------------------------------------------------
    | CURRENT USER
    |--------------------------------------------------------------------------
    |
    | API:
    | GET /api/me
    |
    | FIX:
    | Return REAL data from users table.
    |
    */

    public function me(Request $request)
    {
        // API:
        // GET /api/me

        $user = $request
            ->user()
            ->load('role');

        return response()->json([
            'status' => 'success',

            // FIX:
            // Return all real profile fields.
            'user' => $this->userResponse($user),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    |
    | API:
    | POST /api/logout
    |
    */

    public function logout(Request $request)
    {
        $token = $request
            ->user()
            ->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Logout successful',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE PROFILE
    |--------------------------------------------------------------------------
    |
    | API:
    | PUT /api/user/profile
    |
    | FIX:
    | Save REAL profile data to users table.
    |
    */

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        // ==========================================================
        // VALIDATION
        // ==========================================================

        $validated = $request->validate([
            'first_name' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'last_name' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'gender' => [
                'sometimes',
                'nullable',
                'in:male,female,other',
            ],

            'phone' => [
                'sometimes',
                'nullable',
                'string',
                'max:30',
            ],

            'city_province' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        // ==========================================================
        // FIX:
        // Update REAL database columns.
        // ==========================================================

        if (array_key_exists(
            'first_name',
            $validated
        )) {
            $user->first_name =
                $validated['first_name'];
        }

        if (array_key_exists(
            'last_name',
            $validated
        )) {
            $user->last_name =
                $validated['last_name'];
        }

        if (array_key_exists(
            'gender',
            $validated
        )) {
            $user->gender =
                $validated['gender'];
        }

        if (array_key_exists(
            'phone',
            $validated
        )) {
            $user->phone =
                $validated['phone'];
        }

        if (array_key_exists(
            'city_province',
            $validated
        )) {
            $user->city_province =
                $validated['city_province'];
        }

        // ==========================================================
        // FIX:
        // Keep name synchronized.
        // ==========================================================

        if (
            array_key_exists(
                'first_name',
                $validated
            ) ||
            array_key_exists(
                'last_name',
                $validated
            )
        ) {
            $user->name = trim(
                ($user->first_name ?? '') .
                ' ' .
                ($user->last_name ?? '')
            );
        }

        // ==========================================================
        // SAVE TO MYSQL
        // ==========================================================

        $user->save();

        // ==========================================================
        // LOAD ROLE
        // ==========================================================

        $user->load('role');

        // ==========================================================
        // SUCCESS RESPONSE
        // ==========================================================

        return response()->json([
            'status' => 'success',

            'message' =>
                'Profile updated successfully',

            // FIX:
            // Return updated REAL user data.
            'user' => $this->userResponse($user),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | USER RESPONSE
    |--------------------------------------------------------------------------
    |
    | FIX:
    | One response structure for:
    |
    | LOGIN
    | REGISTER
    | GET /me
    | UPDATE PROFILE
    |
    */

    private function userResponse(User $user): array
    {
        return [
            // ======================================================
            // BASIC DATA
            // ======================================================

            'id' => $user->id,

            'name' => $user->name,

            'email' => $user->email,

            // ======================================================
            // FIX:
            // REAL PROFILE DATA FROM users TABLE
            // ======================================================

            'first_name' =>
                $user->first_name,

            'last_name' =>
                $user->last_name,

            'gender' =>
                $user->gender,

            'phone' =>
                $user->phone,

            'city_province' =>
                $user->city_province,

            // ======================================================
            // ROLE
            // ======================================================

            'role' => $user->role
                ? [
                    'id' => $user->role->id,
                    'name' => $user->role->name,
                ]
                : null,
        ];
    }
}