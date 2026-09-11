<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

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
| PUBLIC AUTHENTICATION
|--------------------------------------------------------------------------
|
| Guest can login/register.
|
*/

Route::post('/login', [AuthController::class, 'login']);

Route::post('/register', [RegisterController::class, 'register']);


/*
|--------------------------------------------------------------------------
| PUBLIC STORE DATA
|--------------------------------------------------------------------------
|
| Guest និង Logged-in User អាចមើល Products និង Categories បាន។
|
| GET  /api/categories
| GET  /api/categories/{category}
|
| GET  /api/products
| GET  /api/products/{product}
|
*/

Route::apiResource(
    'categories',
    CategoryController::class
)->only([
    'index',
    'show',
]);

Route::apiResource(
    'products',
    ProductController::class
)->only([
    'index',
    'show',
]);


/*
|--------------------------------------------------------------------------
| PRODUCT IMAGES
|--------------------------------------------------------------------------
|
| Serve product images through Laravel API.
|
*/

Route::get('/product-image/{path}', function ($path) {
    $file = storage_path('app/public/' . $path);

    if (!file_exists($file)) {
        abort(404);
    }

    return response()->file($file);
})->where('path', '.*');


/*
|--------------------------------------------------------------------------
| PROTECTED API ROUTES
|--------------------------------------------------------------------------
|
| Routes ខាងក្រោមត្រូវការ Login។
|
*/

Route::middleware('auth:sanctum')->group(function () {


    /*
    |--------------------------------------------------------------------------
    | AUTHENTICATION
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/logout',
        [AuthController::class, 'logout']
    );

    Route::put(
        '/user/profile',
        [AuthController::class, 'updateProfile']
    );

    Route::get('/me', function (Illuminate\Http\Request $request) {

        $user = $request->user()->load(
            'role.permissions'
        );

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
    | PRODUCT VARIANTS
    |--------------------------------------------------------------------------
    */

    Route::apiResource(
        'product-variants',
        ProductVariantController::class
    );


    /*
    |--------------------------------------------------------------------------
    | INVENTORY
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
    | PURCHASES
    |--------------------------------------------------------------------------
    */

    Route::apiResource(
        'purchases',
        PurchaseController::class
    );


    /*
    |--------------------------------------------------------------------------
    | SALES
    |--------------------------------------------------------------------------
    */

    Route::apiResource(
        'sales',
        SaleController::class
    );


    /*
    |--------------------------------------------------------------------------
    | POS PAYMENTS
    |--------------------------------------------------------------------------
    */

    Route::apiResource(
        'payments',
        PaymentController::class
    );

    Route::get(
        '/sales/{saleId}/payments',
        [PaymentController::class, 'salePayments']
    );


    /*
    |--------------------------------------------------------------------------
    | REPORTS
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/reports/sales-summary',
        [ReportController::class, 'salesSummary']
    );


    /*
    |--------------------------------------------------------------------------
    | WISHLIST
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
    | CART
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
    | ORDERS
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/orders',
        [OrderController::class, 'index']
    );

    Route::get(
        '/orders/{order}',
        [OrderController::class, 'show']
    );

    Route::post(
        '/orders',
        [OrderController::class, 'store']
    );


    /*
    |--------------------------------------------------------------------------
    | ORDER PAYMENTS
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/orders/{order}/payment',
        [OrderPaymentController::class, 'store']
    );

    Route::post(
        '/orders/{order}/payment/deeplink',
        [OrderPaymentController::class, 'deeplink']
    );

    Route::post(
        '/orders/{order}/payment/verify',
        [OrderPaymentController::class, 'verify']
    );


    /*
    |--------------------------------------------------------------------------
    | SETTINGS
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/settings',
        [SettingController::class, 'show']
    );

    Route::put(
        '/settings',
        [SettingController::class, 'update']
    );


    /*
    |--------------------------------------------------------------------------
    | ADDRESSES
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/addresses',
        [AddressController::class, 'index']
    );

    Route::post(
        '/addresses',
        [AddressController::class, 'store']
    );

    Route::get(
        '/addresses/{id}',
        [AddressController::class, 'show']
    );

    Route::put(
        '/addresses/{id}',
        [AddressController::class, 'update']
    );

    Route::delete(
        '/addresses/{id}',
        [AddressController::class, 'destroy']
    );

    Route::post(
    '/orders/{order}/payment/khqr',
    [OrderPaymentController::class, 'khqr']
);

});