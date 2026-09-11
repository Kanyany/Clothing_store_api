<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\GlobalSearchController;
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
| Admin Panel
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get('/dashboard', [
            DashboardController::class,
            'index'
        ])->name('admin.dashboard');


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


        // Save New Product
        Route::post('/products', [
            ProductController::class,
            'store'
        ])->name('admin.products.store');


        // Edit Product Page
        Route::get('/products/{product}/edit', [
            ProductController::class,
            'edit'
        ])->name('admin.products.edit');


        // Update Product
        Route::put('/products/{product}', [
            ProductController::class,
            'update'
        ])->name('admin.products.update');


        // Toggle Product Active / Inactive
        Route::post('/products/{product}/toggle-status', [
            ProductController::class,
            'toggleStatus'
        ])->name('admin.products.toggle-status');


        // Delete Product
        Route::delete('/products/{product}', [
            ProductController::class,
            'destroy'
        ])->name('admin.products.destroy');


        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */

        // Category List
        Route::get('/categories', [
            CategoryController::class,
            'index'
        ])->name('admin.categories.index');


        // Add Category Page
        Route::get('/categories/create', [
            CategoryController::class,
            'create'
        ])->name('admin.categories.create');


        // Save New Category
        Route::post('/categories', [
            CategoryController::class,
            'store'
        ])->name('admin.categories.store');


        // Edit Category Page
        Route::get('/categories/{category}/edit', [
            CategoryController::class,
            'edit'
        ])->name('admin.categories.edit');


        // Update Category
        Route::put('/categories/{category}', [
            CategoryController::class,
            'update'
        ])->name('admin.categories.update');


        // Toggle Category Active / Inactive
        Route::post('/categories/{category}/toggle-status', [
            CategoryController::class,
            'toggleStatus'
        ])->name('admin.categories.toggle-status');


        // Delete Category
        Route::delete('/categories/{category}', [
            CategoryController::class,
            'destroy'
        ])->name('admin.categories.destroy');


        /*
        |--------------------------------------------------------------------------
        | Inventory
        |--------------------------------------------------------------------------
        */

        // Inventory List
        Route::get('/inventory', [
            \App\Http\Controllers\Admin\InventoryController::class,
            'index'
        ])->name('admin.inventory.index');


        // Set Exact Stock
        Route::put('/inventory/{productVariant}', [
            \App\Http\Controllers\Admin\InventoryController::class,
            'update'
        ])->name('admin.inventory.update');


        // Add / Remove Stock
        Route::post('/inventory/{productVariant}/adjust', [
            \App\Http\Controllers\Admin\InventoryController::class,
            'adjust'
        ])->name('admin.inventory.adjust');



        Route::get('/purchases', [
            PurchaseController::class,
            'index'
        ])->name('admin.purchases.index');

        Route::get('/purchases/create', [
            PurchaseController::class,
            'create'
        ])->name('admin.purchases.create');

        Route::post('/purchases', [
            PurchaseController::class,
            'store'
        ])->name('admin.purchases.store');

        Route::get('/purchases/{purchase}', [
            PurchaseController::class,
            'show'
        ])->name('admin.purchases.show');

        Route::post('/purchases/{purchase}/receive', [
            PurchaseController::class,
            'receive'
        ])->name('admin.purchases.receive');


        // =====================================================
        // TRANSACTIONS
        // =====================================================

        Route::get('/transactions', [
            \App\Http\Controllers\Admin\SaleController::class,
            'index'
        ])->name('admin.transactions.index');

        Route::post('/transactions', [
            \App\Http\Controllers\Admin\SaleController::class,
            'store'
        ])->name('admin.transactions.store');

        Route::put('/transactions/{sale}', [
            \App\Http\Controllers\Admin\SaleController::class,
            'update'
        ])->name('admin.transactions.update');

        Route::delete('/transactions/{sale}', [
            \App\Http\Controllers\Admin\SaleController::class,
            'destroy'
        ])->name('admin.transactions.destroy');



        Route::get('/reports', [ReportController::class, 'index'])
            ->name('admin.reports.index');

        Route::get('/reports/export/csv', [ReportController::class, 'exportCsv'])
            ->name('admin.reports.export.csv');

        Route::get('/reports/export/excel', [ReportController::class, 'exportExcel'])
            ->name('admin.reports.export.excel');

        Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])
            ->name('admin.reports.export.pdf');



          /*
        |--------------------------------------------------------------------------
        | Users & Roles
        |--------------------------------------------------------------------------
        */

        // User List
        Route::get('/users', [
            UserController::class,
            'index'
        ])->name('admin.users.index');


        // Create User
        Route::post('/users', [
            UserController::class,
            'store'
        ])->name('admin.users.store');


        // Update User
        Route::put('/users/{user}', [
            UserController::class,
            'update'
        ])->name('admin.users.update');


        // Activate / Deactivate User
        Route::patch('/users/{user}/status', [
            UserController::class,
            'toggleStatus'
        ])->name('admin.users.toggle-status');


        // Delete User
        Route::delete('/users/{user}', [
            UserController::class,
            'destroy'
        ])->name('admin.users.destroy');




        Route::get('/global-search', [
            GlobalSearchController::class,
            'index'
        ])->name('admin.global-search');

            

    });