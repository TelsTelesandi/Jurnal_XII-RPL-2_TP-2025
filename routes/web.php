<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\VehicleController;
// RoutePlan removed from UI
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminUserController;

Route::pattern('product', '[0-9]+');

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('products.index')
        : redirect()->route('login');
});

// Auth routes
Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('login', [AuthController::class, 'login'])->name('login.attempt');
Route::get('register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('register', [AuthController::class, 'register'])->name('register.attempt');
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Public product routes (dapat diakses tanpa login)
Route::resource('products', ProductController::class)->only(['index', 'show']);

// Cart routes (dapat diakses tanpa login untuk guest users)
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/update/{cartItem}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{cartItem}', [CartController::class, 'remove'])->name('cart.remove');
Route::delete('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');

// Checkout routes (Customer only)
Route::middleware(['auth','simple_role:Customer'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
});

// Protected app routes
Route::middleware('auth')->group(function () {
    // Product management (Admin only)
    Route::resource('products', ProductController::class)->except(['index', 'show'])->middleware('simple_role:Admin');
    Route::post('products/{product}/stock-in', [ProductController::class, 'stockIn'])
        ->middleware('simple_role:Admin')
        ->name('products.stockIn');
    
    // Orders tersedia untuk semua user login; pembatasan kepemilikan di controller
    Route::resource('orders', OrderController::class);
    
    // Admin order management routes
    Route::middleware('simple_role:Admin')->group(function () {
        Route::patch('orders/{order}/validate', [OrderController::class, 'validate'])->name('orders.validate');
        Route::patch('orders/{order}/assign', [OrderController::class, 'assign'])->name('orders.assign');
        Route::patch('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    });

    // Admin only
    Route::middleware('simple_role:Admin')->group(function () {
        Route::resource('vehicles', VehicleController::class);
        Route::post('assignments', [AssignmentController::class, 'store'])->name('assignments.store');
        Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

        // Admin reports
        Route::get('admin/reports/completed', [\App\Http\Controllers\ReportController::class, 'completed'])
            ->name('reports.completed');

        // Admin confirm driver decline reason
        Route::patch('orders/{order}/driver-decline/confirm', [OrderController::class, 'confirmDriverDecline'])
            ->name('orders.driver.decline.confirm');

        // Admin user monitoring and creation
        Route::get('admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
        Route::get('admin/users/create', [AdminUserController::class, 'create'])->name('admin.users.create');
        Route::post('admin/users', [AdminUserController::class, 'store'])->name('admin.users.store');
    });

    // Tracking routes removed

    // Driver actions on assigned orders
    Route::middleware('simple_role:Driver')->group(function () {
        Route::patch('orders/{order}/driver/accept', [OrderController::class, 'driverAccept'])
            ->name('orders.driver.accept');
        Route::patch('orders/{order}/driver/decline', [OrderController::class, 'driverDecline'])
            ->name('orders.driver.decline');
        // Driver tracking routes removed
    });

    // Live location endpoints removed

    // Delivery confirmation (Driver dan Admin)
    Route::get('orders/{order}/delivery', [DeliveryController::class, 'create'])
        ->middleware('simple_role:Admin,Driver')
        ->name('deliveries.create');
    Route::post('orders/{order}/delivery', [DeliveryController::class, 'store'])
        ->middleware('simple_role:Admin,Driver')
        ->name('deliveries.store');

    // Customer confirmation of delivery
    Route::get('orders/{order}/delivery/confirm', [DeliveryController::class, 'confirmForm'])
        ->name('deliveries.confirm.form');
    Route::post('orders/{order}/delivery/confirm', [DeliveryController::class, 'confirm'])
        ->name('deliveries.confirm');

    // Account pages
    Route::get('account', [ProfileController::class, 'show'])->name('account.show');
    Route::post('account', [ProfileController::class, 'update'])->name('account.update');
});
