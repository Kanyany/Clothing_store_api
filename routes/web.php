<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ProductController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Admin Authentication
|--------------------------------------------------------------------------
*/

Route::get('/admin/login', [
    AuthController::class,
    'showLogin'
])->name('admin.login');

Route::post('/admin/login', [
    AuthController::class,
    'login'
])->name('admin.login.submit');


/*
|--------------------------------------------------------------------------
| Admin Dashboard
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->prefix('admin')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');


    /*
    |--------------------------------------------------------------------------
    | Admin Logout
    |--------------------------------------------------------------------------
    */

    Route::post('/logout', [
        AuthController::class,
        'logout'
    ])->name('admin.logout');


    /*
    |--------------------------------------------------------------------------
    | Products
    |--------------------------------------------------------------------------
    */

    // Product List
    Route::get('/products', [
        ProductController::class,
        'index'
    ])->name('admin.products.index');


    // Add Product Page
    Route::get('/products/create', [
        ProductController::class,
        'create'
    ])->name('admin.products.create');


    // Save Product
    Route::post('/products', [
        ProductController::class,
        'store'
    ])->name('admin.products.store');

});