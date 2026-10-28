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
use App\Http\Controllers\Api\CurrencyRateController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\BannerController;

/*
|--------------------------------------------------------------------------
| PUBLIC AUTHENTICATION
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [RegisterController::class, 'register']);

/*
|--------------------------------------------------------------------------
| PUBLIC STORE DATA
|--------------------------------------------------------------------------
*/

Route::apiResource('categories', CategoryController::class)
    ->only(['index', 'show']);

Route::apiResource('products', ProductController::class)
    ->only(['index', 'show']);

/*
|--------------------------------------------------------------------------
| PUBLIC BANNERS
|--------------------------------------------------------------------------
*/

Route::get('/banners', [BannerController::class, 'index']);

/*
|--------------------------------------------------------------------------
| PUBLIC IMAGE DELIVERY
|--------------------------------------------------------------------------
|
| Images are stored on Laravel's public disk:
| storage/app/public/
|
| Product example:
| /api/product-image/products/example.webp
|
| Banner example:
| /api/home-banner-image/banners/example.jpg
|
*/

$imageResponse = static function (string $path) {
    $disk = \Illuminate\Support\Facades\Storage::disk('public');

    $path = ltrim(str_replace('\\', '/', $path), '/');

    // Reject empty paths and path traversal.
    if (
        $path === '' ||
        str_contains($path, '..') ||
        str_contains($path, "\0") ||
        !$disk->exists($path)
    ) {
        abort(404, 'Image not found');
    }

    $fullPath = $disk->path($path);
    $publicRoot = realpath(storage_path('app/public'));
    $resolvedFile = realpath($fullPath);

    // Ensure the resolved file stays inside storage/app/public.
    if (
        $publicRoot === false ||
        $resolvedFile === false ||
        !str_starts_with(
            $resolvedFile,
            $publicRoot . DIRECTORY_SEPARATOR
        ) ||
        !is_file($resolvedFile) ||
        !is_readable($resolvedFile)
    ) {
        abort(404, 'Image not found');
    }

    $contents = file_get_contents($resolvedFile);

    if ($contents === false || $contents === '') {
        abort(404, 'Unable to read image');
    }

    /*
     * Some stored image responses may contain leading whitespace
     * before their image signature. Remove it only if a recognized
     * image signature follows.
     */
    $candidate = ltrim($contents, " \t\n\r\0\x0B");

    $isWebp =
        strlen($candidate) >= 12 &&
        substr($candidate, 0, 4) === 'RIFF' &&
        substr($candidate, 8, 4) === 'WEBP';

    $isJpeg = str_starts_with(
        $candidate,
        "\xFF\xD8\xFF"
    );

    $isPng = str_starts_with(
        $candidate,
        "\x89PNG\r\n\x1A\n"
    );

    $isGif =
        str_starts_with($candidate, 'GIF87a') ||
        str_starts_with($candidate, 'GIF89a');

    $isBmp = str_starts_with($candidate, 'BM');

    $isImageSignatureValid =
        $isWebp || $isJpeg || $isPng || $isGif || $isBmp;

    if ($isImageSignatureValid) {
        $contents = $candidate;
    }

    // Determine the MIME type from the actual file content.
    $mime = (new \finfo(FILEINFO_MIME_TYPE))
        ->buffer($contents);

    $allowedMimeTypes = [
        'image/webp',
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/bmp',
        'image/avif',
    ];

    if (
        !in_array($mime, $allowedMimeTypes, true) &&
        !$isImageSignatureValid
    ) {
        abort(415, 'Unsupported or invalid image file');
    }

    return response($contents, 200, [
        'Content-Type' => $mime ?: 'application/octet-stream',
        'X-Content-Type-Options' => 'nosniff',
        'Cache-Control' => 'public, max-age=3600',
    ]);
};

Route::get('/product-image/{path}', $imageResponse)
    ->where('path', '.*');

Route::get('/home-banner-image/{path}', $imageResponse)
    ->where('path', '.*');

/*
|--------------------------------------------------------------------------
| PUBLIC CURRENCY RATE
|--------------------------------------------------------------------------
*/

Route::get(
    '/currency-rate',
    [CurrencyRateController::class, 'show']
);

/*
|--------------------------------------------------------------------------
| PROTECTED API ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | AUTHENTICATION
    |--------------------------------------------------------------------------
    */

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::put(
        '/user/profile',
        [AuthController::class, 'updateProfile']
    );

    Route::get('/me', [AuthController::class, 'me']);

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

    Route::get('/inventory', [InventoryController::class, 'index']);
    Route::post('/inventory', [InventoryController::class, 'store']);
    Route::get('/inventory/{id}', [InventoryController::class, 'show']);
    Route::put('/inventory/{id}', [InventoryController::class, 'update']);

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

    Route::apiResource('purchases', PurchaseController::class);

    /*
    |--------------------------------------------------------------------------
    | SALES
    |--------------------------------------------------------------------------
    */

    Route::apiResource('sales', SaleController::class);

    /*
    |--------------------------------------------------------------------------
    | POS PAYMENTS
    |--------------------------------------------------------------------------
    */

    Route::apiResource('payments', PaymentController::class);

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

    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist', [WishlistController::class, 'store']);
    Route::get('/wishlist/{id}', [WishlistController::class, 'show']);

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
        Route::get('/', [CartController::class, 'index']);
        Route::post('/', [CartController::class, 'store']);
        Route::put('/{id}', [CartController::class, 'update']);
        Route::delete('/{id}', [CartController::class, 'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | ORDERS
    |--------------------------------------------------------------------------
    */

    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);

    Route::patch(
        '/orders/{order}/status',
        [OrderController::class, 'updateStatus']
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

    Route::post(
        '/orders/{order}/payment/khqr',
        [OrderPaymentController::class, 'khqr']
    );

    /*
    |--------------------------------------------------------------------------
    | SETTINGS
    |--------------------------------------------------------------------------
    */

    Route::get('/settings', [SettingController::class, 'show']);
    Route::put('/settings', [SettingController::class, 'update']);

    /*
    |--------------------------------------------------------------------------
    | ADDRESSES
    |--------------------------------------------------------------------------
    */

    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::get('/addresses/{id}', [AddressController::class, 'show']);
    Route::put('/addresses/{id}', [AddressController::class, 'update']);

    Route::delete(
        '/addresses/{id}',
        [AddressController::class, 'destroy']
    );

    /*
    |--------------------------------------------------------------------------
    | CURRENCY RATE MANAGEMENT
    |--------------------------------------------------------------------------
    */

    Route::put(
        '/currency-rate',
        [CurrencyRateController::class, 'update']
    );

    /*
    |--------------------------------------------------------------------------
    | CUSTOMER CHAT & CHATBOT
    |--------------------------------------------------------------------------
    */

    Route::prefix('chat')->group(function () {
        Route::get(
            '/conversation',
            [ChatController::class, 'conversation']
        );

        Route::get('/messages', [ChatController::class, 'messages']);

        Route::post('/messages', [ChatController::class, 'sendMessage']);

        Route::post(
            '/request-admin',
            [ChatController::class, 'requestAdmin']
        );
    });
});
