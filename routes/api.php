<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductVariantController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderPaymentController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\AddressController;


/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [RegisterController::class, 'register']);


/*
|--------------------------------------------------------------------------
| Protected API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::put('/user/profile', [AuthController::class, 'updateProfile']);

    Route::get('/me', function (Illuminate\Http\Request $request) {
            $user = $request->user()->load('role.permissions');

        return response()->json([
            'status' => 'success',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role?->name,
                'permissions' => $user->role?->permissions->pluck('name'),
            ],
        ]);
    });


    /*
    |--------------------------------------------------------------------------
    | Categories
    |--------------------------------------------------------------------------
    */

    Route::apiResource(
        'categories',
        CategoryController::class
    );


    /*
    |--------------------------------------------------------------------------
    | Products
    |--------------------------------------------------------------------------
    */

    Route::apiResource(
        'products',
        ProductController::class
    );


    /*
    |--------------------------------------------------------------------------
    | Product Variants
    |--------------------------------------------------------------------------
    */

    Route::apiResource(
        'product-variants',
        ProductVariantController::class
    );


    /*
    |--------------------------------------------------------------------------
    | Inventory
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/inventory',
        [InventoryController::class, 'index']
    );

    Route::post(
        '/inventory',
        [InventoryController::class, 'store']
    );

    Route::get(
        '/inventory/{id}',
        [InventoryController::class, 'show']
    );

    Route::put(
        '/inventory/{id}',
        [InventoryController::class, 'update']
    );

    Route::post(
        '/inventory/{id}/adjust',
        [InventoryController::class, 'adjust']
    );

    Route::get(
        '/inventory/{id}/movements',
        [InventoryController::class, 'movements']
    );


    /*
    |--------------------------------------------------------------------------
    | Purchases
    |--------------------------------------------------------------------------
    */

    Route::apiResource(
        'purchases',
        PurchaseController::class
    );


    /*
    |--------------------------------------------------------------------------
    | Sales
    |--------------------------------------------------------------------------
    */

    Route::apiResource(
        'sales',
        SaleController::class
    );


    /*
    |--------------------------------------------------------------------------
    | POS Payments
    |--------------------------------------------------------------------------
    */

    Route::apiResource(
        'payments',
        PaymentController::class
    );

    Route::get(
        'sales/{saleId}/payments',
        [PaymentController::class, 'salePayments']
    );


    /*
    |--------------------------------------------------------------------------
    | Reports
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/reports/sales-summary',
        [ReportController::class, 'salesSummary']
    );


    /*
    |--------------------------------------------------------------------------
    | Wishlist
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/wishlist',
        [WishlistController::class, 'index']
    );

    Route::post(
        '/wishlist',
        [WishlistController::class, 'store']
    );

    Route::get(
        '/wishlist/{id}',
        [WishlistController::class, 'show']
    );

    Route::delete(
        '/wishlist/{productId}',
        [WishlistController::class, 'destroy']
    );


    /*
    |--------------------------------------------------------------------------
    | Cart
    |--------------------------------------------------------------------------
    */

    Route::prefix('cart')->group(function () {

        Route::get(
            '/',
            [CartController::class, 'index']
        );

        Route::post(
            '/',
            [CartController::class, 'store']
        );

        Route::put(
            '/{id}',
            [CartController::class, 'update']
        );

        Route::delete(
            '/{id}',
            [CartController::class, 'destroy']
        );
    });


    /*
|--------------------------------------------------------------------------
| Orders
|--------------------------------------------------------------------------
*/

Route::get('/orders', [OrderController::class, 'index']);

Route::get('/orders/{order}', [OrderController::class, 'show']);

Route::post('/orders', [OrderController::class, 'store']);


    /*
    |--------------------------------------------------------------------------
    | Order Payments
    |--------------------------------------------------------------------------
    */

    // Create normal payment
    Route::post(
        '/orders/{order}/payment',
        [OrderPaymentController::class, 'store']
    );

    // Create Bakong payment/deeplink
    Route::post(
        '/orders/{order}/payment/deeplink',
        [OrderPaymentController::class, 'deeplink']
    );

    // Verify Bakong payment
    Route::post(
        '/orders/{order}/payment/verify',
        [OrderPaymentController::class, 'verify']
    );

    
    /*
    |--------------------------------------------------------------------------
    | setting
    |--------------------------------------------------------------------------
    */


    Route::get('/settings', [SettingController::class, 'show']);
    Route::put('/settings', [SettingController::class, 'update']);


    /*
    |--------------------------------------------------------------------------
    | addresses
    |--------------------------------------------------------------------------
    */


    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::get('/addresses/{id}', [AddressController::class, 'show']);
    Route::put('/addresses/{id}', [AddressController::class, 'update']);
    Route::delete('/addresses/{id}', [AddressController::class, 'destroy']);




});