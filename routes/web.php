<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ServiceTypeController;
use App\Http\Controllers\Admin\OilController;
use App\Http\Controllers\Admin\MasterController;
use App\Http\Controllers\Admin\StandardItemController;
use App\Http\Controllers\Admin\DispatcherController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PressureLevelController;
use App\Http\Controllers\Public\LandingController;
use App\Http\Controllers\Public\MasterController as PublicMasterController;
use App\Http\Controllers\Public\CustomerAuthController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\MiniApp\HomeController as MiniAppHomeController;

// Public routes
Route::get('/', LandingController::class)->name('public.landing');
Route::get('/masters', [PublicMasterController::class, 'index'])->name('public.masters.index');
Route::get('/masters/{master}', [PublicMasterController::class, 'show'])->name('public.masters.show');

// Telegram Mini App routes
Route::prefix('app')->group(function () {
    Route::get('/', [MiniAppHomeController::class, 'index'])->name('miniapp.home');
    Route::get('/login', [MiniAppHomeController::class, 'login'])->name('miniapp.login');
});

// Default login redirect
Route::get('/login', function () {
    return redirect()->route('customer.login');
})->name('login');

// Customer Auth Routes (Public)
Route::prefix('auth')->group(function () {
    Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('customer.login');
    Route::post('/otp/send', [CustomerAuthController::class, 'sendOtp'])->name('customer.otp.send');
    Route::post('/otp/verify', [CustomerAuthController::class, 'verifyOtp'])->name('customer.otp.verify');
});

// Customer Protected Routes
Route::prefix('customer')->middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('customer.dashboard');
    Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('customer.logout');
});

// Admin Auth Routes
Route::prefix('admin')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');
});

// Admin Protected Routes
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Service Types CRUD
    Route::resource('service-types', ServiceTypeController::class, [
        'names' => [
            'index' => 'admin.service-types.index',
            'create' => 'admin.service-types.create',
            'store' => 'admin.service-types.store',
            'show' => 'admin.service-types.show',
            'edit' => 'admin.service-types.edit',
            'update' => 'admin.service-types.update',
            'destroy' => 'admin.service-types.destroy',
        ]
    ]);

    // Oils CRUD
    Route::resource('oils', OilController::class, [
        'names' => [
            'index' => 'admin.oils.index',
            'create' => 'admin.oils.create',
            'store' => 'admin.oils.store',
            'show' => 'admin.oils.show',
            'edit' => 'admin.oils.edit',
            'update' => 'admin.oils.update',
            'destroy' => 'admin.oils.destroy',
        ]
    ]);

    // Masters CRUD
    Route::resource('masters', MasterController::class, [
        'names' => [
            'index' => 'admin.masters.index',
            'create' => 'admin.masters.create',
            'store' => 'admin.masters.store',
            'show' => 'admin.masters.show',
            'edit' => 'admin.masters.edit',
            'update' => 'admin.masters.update',
            'destroy' => 'admin.masters.destroy',
        ]
    ]);

    // Standard Items CRUD
    Route::resource('standard-items', StandardItemController::class, [
        'names' => [
            'index' => 'admin.standard-items.index',
            'create' => 'admin.standard-items.create',
            'store' => 'admin.standard-items.store',
            'show' => 'admin.standard-items.show',
            'edit' => 'admin.standard-items.edit',
            'update' => 'admin.standard-items.update',
            'destroy' => 'admin.standard-items.destroy',
        ]
    ]);

    // Pressure Levels CRUD
    Route::resource('pressure-levels', PressureLevelController::class, [
        'names' => [
            'index' => 'admin.pressure-levels.index',
            'create' => 'admin.pressure-levels.create',
            'store' => 'admin.pressure-levels.store',
            'show' => 'admin.pressure-levels.show',
            'edit' => 'admin.pressure-levels.edit',
            'update' => 'admin.pressure-levels.update',
            'destroy' => 'admin.pressure-levels.destroy',
        ]
    ]);

    // Dispatchers CRUD
    Route::resource('dispatchers', DispatcherController::class, [
        'names' => [
            'index' => 'admin.dispatchers.index',
            'create' => 'admin.dispatchers.create',
            'store' => 'admin.dispatchers.store',
            'show' => 'admin.dispatchers.show',
            'edit' => 'admin.dispatchers.edit',
            'update' => 'admin.dispatchers.update',
            'destroy' => 'admin.dispatchers.destroy',
        ]
    ]);

    // Customers (without create/store - customers register via app)
    Route::resource('customers', CustomerController::class, [
        'except' => ['create', 'store'],
        'names' => [
            'index' => 'admin.customers.index',
            'show' => 'admin.customers.show',
            'edit' => 'admin.customers.edit',
            'update' => 'admin.customers.update',
            'destroy' => 'admin.customers.destroy',
        ]
    ]);

    // Settings
    Route::get('settings', [SettingController::class, 'index'])->name('admin.settings.index');
    Route::put('settings', [SettingController::class, 'update'])->name('admin.settings.update');

    // Profile
    Route::get('profile', [ProfileController::class, 'index'])->name('admin.profile.index');
    Route::put('profile', [ProfileController::class, 'update'])->name('admin.profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('admin.profile.password');

    // Orders (view and manage only, no create)
    Route::get('orders', [OrderController::class, 'index'])->name('admin.orders.index');
    Route::get('orders/new', [OrderController::class, 'newOrders'])->name('admin.orders.new');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('admin.orders.show');
    Route::post('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('admin.orders.update-status');
    Route::post('orders/{order}/reschedule', [OrderController::class, 'reschedule'])->name('admin.orders.reschedule');
    Route::post('orders/{order}/note', [OrderController::class, 'addNote'])->name('admin.orders.add-note');
    Route::post('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('admin.orders.cancel');
});
