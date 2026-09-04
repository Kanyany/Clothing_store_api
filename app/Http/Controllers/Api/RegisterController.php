<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    /**
     * Register a new customer.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'gender' => [
                'required',
                'in:male,female,other',
            ],

            'first_name' => [
                'required',
                'string',
                'max:100',
            ],

            'last_name' => [
                'required',
                'string',
                'max:100',
            ],

            'phone' => [
                'required',
                'string',
                'max:30',
                'unique:users,phone',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],

            'city_province' => [
                'required',
                'string',
                'max:255',
            ],

            'terms_accepted' => [
                'required',
                'accepted',
            ],
        ]);

        $result = DB::transaction(function () use ($validated) {

            /*
             * Find the default Customer role.
             */
            $customerRole = Role::where('name', 'Customer')->first();

            /*
             * If Customer role does not exist,
             * stop registration instead of creating
             * a user without the correct role.
             */
            if (!$customerRole) {
                abort(
                    500,
                    'Customer role not found. Please create the Customer role first.'
                );
            }

            /*
             * Create full name from first + last name.
             */
            $name = trim(
                $validated['first_name']
                . ' '
                . $validated['last_name']
            );

            /*
             * Create user.
             *
             * Password is automatically hashed
             * because User model uses:
             *
             * 'password' => 'hashed'
             */
            $user = User::create([
                'role_id' => $customerRole->id,

                'name' => $name,

                'first_name' =>
                    $validated['first_name'],

                'last_name' =>
                    $validated['last_name'],

                'gender' =>
                    $validated['gender'],

                'email' =>
                    $validated['email'],

                'phone' =>
                    $validated['phone'],

                'city_province' =>
                    $validated['city_province'],

                'terms_accepted' => true,

                'password' =>
                    $validated['password'],
            ]);

            /*
             * Create Sanctum token.
             */
            $token = $user->createToken(
                'pos-app'
            )->plainTextToken;

            return [
                'user' => $user->load('role'),

                'token' => $token,
            ];
        });

        return response()->json([
            'status' => 'success',

            'message' =>
                'Registration successful',

            'data' => [
                'user' =>
                    $result['user'],

                'token' =>
                    $result['token'],

                'token_type' =>
                    'Bearer',
            ],
        ], 201);
    }
}