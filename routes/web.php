<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\GlobalSearchController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\AdminChatController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\CollectionController;
use App\Http\Controllers\Admin\BannerController;


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

        Route::get('/products', [
            ProductController::class,
            'index'
        ])->name('admin.products.index');

        Route::get('/products/create', [
            ProductController::class,
            'create'
        ])->name('admin.products.create');

        Route::post('/products', [
            ProductController::class,
            'store'
        ])->name('admin.products.store');

        Route::get('/products/{product}/edit', [
            ProductController::class,
            'edit'
        ])->name('admin.products.edit');

        Route::put('/products/{product}', [
            ProductController::class,
            'update'
        ])->name('admin.products.update');

        Route::post('/products/{product}/toggle-status', [
            ProductController::class,
            'toggleStatus'
        ])->name('admin.products.toggle-status');

        Route::delete('/products/{product}', [
            ProductController::class,
            'destroy'
        ])->name('admin.products.destroy');

        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */

        Route::get('/categories', [
            CategoryController::class,
            'index'
        ])->name('admin.categories.index');

        Route::get('/categories/create', [
            CategoryController::class,
            'create'
        ])->name('admin.categories.create');

        Route::post('/categories', [
            CategoryController::class,
            'store'
        ])->name('admin.categories.store');

        Route::get('/categories/{category}/edit', [
            CategoryController::class,
            'edit'
        ])->name('admin.categories.edit');

        Route::put('/categories/{category}', [
            CategoryController::class,
            'update'
        ])->name('admin.categories.update');

        Route::post('/categories/{category}/toggle-status', [
            CategoryController::class,
            'toggleStatus'
        ])->name('admin.categories.toggle-status');

        Route::delete('/categories/{category}', [
            CategoryController::class,
            'destroy'
        ])->name('admin.categories.destroy');

        /*
        |--------------------------------------------------------------------------
        | Inventory
        |--------------------------------------------------------------------------
        */

        Route::get('/inventory', [
            \App\Http\Controllers\Admin\InventoryController::class,
            'index'
        ])->name('admin.inventory.index');

        Route::put('/inventory/{productVariant}', [
            \App\Http\Controllers\Admin\InventoryController::class,
            'update'
        ])->name('admin.inventory.update');

        Route::post('/inventory/{productVariant}/adjust', [
            \App\Http\Controllers\Admin\InventoryController::class,
            'adjust'
        ])->name('admin.inventory.adjust');

        /*
        |--------------------------------------------------------------------------
        | Purchases
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Sales
        |--------------------------------------------------------------------------
        */

        Route::get('/sale', [
            SaleController::class,
            'index'
        ])->name('admin.sale.index');

        Route::post('/sale', [
            SaleController::class,
            'store'
        ])->name('admin.sale.store');

        Route::put('/sale/{sale}', [
            SaleController::class,
            'update'
        ])->name('admin.sale.update');

        Route::delete('/sale/{sale}', [
            SaleController::class,
            'destroy'
        ])->name('admin.sale.destroy');

        Route::post('/sale/order/{order}/status', [
            SaleController::class,
            'updateOrderStatus'
        ])->name('admin.sale.order.status');

        Route::post('/sale/order/timeline-settings', [
            SaleController::class,
            'updateOrderTimelineSettings'
        ])->name('admin.sale.order.timeline.settings');

        /*
        |--------------------------------------------------------------------------
        | Reports
        |--------------------------------------------------------------------------
        */

        Route::get('/reports', [
            ReportController::class,
            'index'
        ])->name('admin.reports.index');

        Route::get('/reports/export/csv', [
            ReportController::class,
            'exportCsv'
        ])->name('admin.reports.export.csv');

        Route::get('/reports/export/excel', [
            ReportController::class,
            'exportExcel'
        ])->name('admin.reports.export.excel');

        Route::get('/reports/export/pdf', [
            ReportController::class,
            'exportPdf'
        ])->name('admin.reports.export.pdf');

        /*
        |--------------------------------------------------------------------------
        | Users & Roles
        |--------------------------------------------------------------------------
        */

        Route::get('/users', [
            UserController::class,
            'index'
        ])->name('admin.users.index');

        Route::post('/users', [
            UserController::class,
            'store'
        ])->name('admin.users.store');

        Route::put('/users/{user}', [
            UserController::class,
            'update'
        ])->name('admin.users.update');

        Route::patch('/users/{user}/status', [
            UserController::class,
            'toggleStatus'
        ])->name('admin.users.toggle-status');

        Route::delete('/users/{user}', [
            UserController::class,
            'destroy'
        ])->name('admin.users.destroy');

        /*
        |--------------------------------------------------------------------------
        | Global Search
        |--------------------------------------------------------------------------
        */

        Route::get('/global-search', [
            GlobalSearchController::class,
            'index'
        ])->name('admin.global-search');

        /*
        |--------------------------------------------------------------------------
        | Customer Orders
        |--------------------------------------------------------------------------
        */

        Route::get('/orders', [
            OrderController::class,
            'index'
        ])->name('admin.orders.index');

        Route::get('/orders/{order}', [
            OrderController::class,
            'show'
        ])->name('admin.orders.show');

        Route::patch('/orders/{order}/status', [
            OrderController::class,
            'updateStatus'
        ])->name('admin.orders.update-status');

        

        /*
        |--------------------------------------------------------------------------
        | Admin Customer Chat API
        |--------------------------------------------------------------------------
        */

        Route::get('/chats', [
            AdminChatController::class,
            'index'
        ])->name('admin.chats.index');

        Route::get('/chats/{conversation}', [
            AdminChatController::class,
            'show'
        ])->name('admin.chats.show');

        Route::post('/chats/{conversation}/take-over', [
            AdminChatController::class,
            'takeOver'
        ])->name('admin.chats.take-over');

        Route::post('/chats/{conversation}/reply', [
            AdminChatController::class,
            'reply'
        ])->name('admin.chats.reply');

        Route::post('/chats/{conversation}/return-to-bot', [
            AdminChatController::class,
            'returnToBot'
        ])->name('admin.chats.return-to-bot');

        Route::post('/chats/{conversation}/close', [
            AdminChatController::class,
            'close'
        ])->name('admin.chats.close');

        /*
        |--------------------------------------------------------------------------
        | Admin Chat Inbox Page
        |--------------------------------------------------------------------------
        | Reuse existing AdminChatController API.
        | Do not create a new MessageController.
        */

        Route::get('/chat-inbox', function () {
            return view('admin.messages.index');
        })->name('admin.chat-inbox');

        /*
        |--------------------------------------------------------------------------
        | Notifications
        |--------------------------------------------------------------------------
        */

        Route::get('/notifications', [
            NotificationController::class,
            'index'
        ])->name('admin.notifications.index');

        Route::post('/notifications/read-all', [
            NotificationController::class,
            'readAll'
        ])->name('admin.notifications.read-all');

        Route::post('/notifications/{id}/read', [
            NotificationController::class,
            'read'
        ])->name('admin.notifications.read');


        
        /*
        |--------------------------------------------------------------------------
        | Collections Management
        |--------------------------------------------------------------------------
        */

        Route::get('/collections', [
            CollectionController::class,
            'index',
        ])->name('admin.collections.index');

        Route::get('/collections/create', [
            CollectionController::class,
            'create',
        ])->name('admin.collections.create');

        Route::post('/collections', [
            CollectionController::class,
            'store',
        ])->name('admin.collections.store');

        Route::get('/collections/{collection}/edit', [
            CollectionController::class,
            'edit',
        ])->name('admin.collections.edit');

        Route::put('/collections/{collection}', [
            CollectionController::class,
            'update',
        ])->name('admin.collections.update');

        Route::delete('/collections/{collection}', [
            CollectionController::class,
            'destroy',
        ])->name('admin.collections.destroy');

        /*
        |--------------------------------------------------------------------------
        | Banners Management
        |--------------------------------------------------------------------------
        */

        Route::get('/banners', [
            BannerController::class,
            'index',
        ])->name('admin.banners.index');

        Route::get('/banners/create', [
            BannerController::class,
            'create',
        ])->name('admin.banners.create');

        Route::post('/banners', [
            BannerController::class,
            'store',
        ])->name('admin.banners.store');

        Route::get('/banners/{banner}/edit', [
            BannerController::class,
            'edit',
        ])->name('admin.banners.edit');

        Route::put('/banners/{banner}', [
            BannerController::class,
            'update',
        ])->name('admin.banners.update');

        Route::patch('/banners/{banner}/toggle-status', [
            BannerController::class,
            'toggleStatus',
        ])->name('admin.banners.toggle-status');

        Route::delete('/banners/{banner}', [
            BannerController::class,
            'destroy',
        ])->name('admin.banners.destroy');



       



    });
