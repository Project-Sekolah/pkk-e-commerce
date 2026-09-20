<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderManagementController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

// Public Pages
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/faq', [PageController::class, 'faq'])->name('faq');

Route::post('/payments/midtrans/notification', [OrderController::class, 'paymentNotification'])
    ->middleware('throttle:60,1')
    ->withoutMiddleware(ValidateCsrfToken::class)
    ->name('payments.midtrans.notification');

// Products (Public)
Route::get('/product', [ProductController::class, 'index'])->name('products.index');
Route::get('/store/{sellerId}', [ProductController::class, 'storeFront'])->name('products.storefront');
Route::middleware(['auth', 'role:seller,admin'])
    ->get('/product/seller', [ProductController::class, 'seller'])
    ->name('products.seller');
Route::middleware(['auth', 'role:seller,admin'])
    ->get('/product/seller/purchase-history', [ProductController::class, 'purchaseHistory'])
    ->name('products.purchase-history');
Route::middleware(['auth', 'role:seller,admin'])
    ->get('/product/seller/sales-report', [ProductController::class, 'salesReport'])
    ->name('products.sales-report');
Route::middleware(['auth', 'role:seller,admin'])
    ->get('/product/add', [ProductController::class, 'create'])
    ->name('products.create');
Route::get('/product/{id}', [ProductController::class, 'show'])->name('products.show');

// Auth Handlers (Native compatibility: /user/login, /user/register, /user/logout)
Route::post('/user/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login');
Route::post('/user/register', [AuthController::class, 'register'])->name('register');
Route::match(['get', 'post'], '/user/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Routes
Route::middleware('auth')->group(function () {
    // User Profile & Addresses
    Route::get('/user/profile', [UserController::class, 'profile'])->name('user.profile');
    Route::post('/user/profile/update', [UserController::class, 'updateProfile'])->name('user.profile.update');
    Route::post('/user/profile/password', [UserController::class, 'changePassword'])->name('user.password.update');
    Route::post('/user/become-seller', [UserController::class, 'becomeSeller'])->name('user.become-seller');
    Route::post('/user/address/add', [UserController::class, 'addAddress'])->name('user.address.add');
    Route::post('/user/address/{id}/default', [UserController::class, 'setDefaultAddress'])->name('user.address.default');
    Route::delete('/user/address/{id}', [UserController::class, 'deleteAddress'])->name('user.address.delete');

    // Cart Endpoints
    foreach (['cart', 'Cart'] as $prefix) {
        Route::prefix($prefix)->group(function () use ($prefix) {
            Route::get('/getCart', [CartController::class, 'getCart'])->name($prefix . '.get');
            Route::post('/addItem', [CartController::class, 'addItem'])->name($prefix . '.add');
            Route::post('/increaseItem', [CartController::class, 'increaseItem'])->name($prefix . '.increase');
            Route::post('/decreaseItem', [CartController::class, 'decreaseItem'])->name($prefix . '.decrease');
            Route::post('/deleteItem', [CartController::class, 'deleteItem'])->name($prefix . '.delete');
            Route::post('/clearCart', [CartController::class, 'clearCart'])->name($prefix . '.clear');
            Route::post('/validateDiscount', [CartController::class, 'validateDiscount'])->name($prefix . '.discount');
        });
    }

    // Ratings
    Route::post('/product/addRating', [ProductController::class, 'addRating'])->name('product.rating');
    Route::delete('/product/rating/{id}', [ProductController::class, 'deleteRating'])->name('product.rating.delete');

    // Orders
    Route::get('/order', [OrderController::class, 'history']);
    Route::get('/order/history', [OrderController::class, 'history'])->name('order.history');
    Route::get('/order/detail/{orderId}', [OrderController::class, 'detail'])->name('order.detail');
    Route::post('/order/checkout', [OrderController::class, 'checkout'])->name('order.checkout');
    Route::get('/order/success', [OrderController::class, 'success'])->name('order.success');

    // Seller & Admin Routes
    Route::middleware('role:seller,admin')->group(function () {
        Route::post('/product/store', [ProductController::class, 'store'])->name('products.store');
        Route::get('/product/edit/{id}', [ProductController::class, 'edit'])->name('products.edit');
        Route::post('/product/update/{id}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/product/delete/{id}', [ProductController::class, 'destroy'])->name('products.destroy');
        Route::delete('/product/image/{id}', [ProductController::class, 'deleteImage'])->name('products.image.destroy');
        Route::post('/product/image/{id}/primary', [ProductController::class, 'setPrimaryImage'])->name('products.image.primary');

        // Discounts
        Route::get('/discount', [DiscountController::class, 'index'])->name('discounts.index');
        Route::get('/discount/add', [DiscountController::class, 'create'])->name('discounts.create');
        Route::post('/discount/store', [DiscountController::class, 'store'])->name('discounts.store');
        Route::get('/discount/edit/{id}', [DiscountController::class, 'edit'])->name('discounts.edit');
        Route::post('/discount/update/{id}', [DiscountController::class, 'update'])->name('discounts.update');
        Route::match(['get', 'delete'], '/discount/delete/{id}', [DiscountController::class, 'destroy'])->name('discounts.destroy');
    });

    // Admin Only Routes
    Route::middleware('role:admin')->group(function () {
        Route::get('/adminDashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/admin/orders', [OrderManagementController::class, 'index'])->name('admin.orders');
        Route::post('/admin/orders/{id}/status', [OrderManagementController::class, 'updateStatus'])->name('admin.orders.status');
        Route::get('/user/manajemen', [UserManagementController::class, 'index'])->name('admin.users');
        Route::post('/user/toggleBlock/{id}', [UserManagementController::class, 'toggleBlock'])->name('admin.user.toggleBlock');
        Route::post('/user/updateRole/{id}', [UserManagementController::class, 'updateRole'])->name('admin.user.updateRole');
        Route::delete('/user/softDelete/{id}', [UserManagementController::class, 'softDelete'])->name('admin.user.softDelete');
    });
});