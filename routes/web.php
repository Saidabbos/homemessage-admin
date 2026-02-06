<?php

use Illuminate\Support\Facades\Route;
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
use App\Http\Controllers\Admin\SlotController;
use App\Http\Controllers\Admin\MasterScheduleController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Public\LandingController;
use App\Http\Controllers\Public\MasterController as PublicMasterController;

// Public routes
Route::get('/', LandingController::class)->name('public.landing');
Route::get('/masters', [PublicMasterController::class, 'index'])->name('public.masters.index');
Route::get('/masters/{master}', [PublicMasterController::class, 'show'])->name('public.masters.show');

// Default login redirect
Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

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

    // Slots CRUD (time templates)
    Route::resource('slots', SlotController::class, [
        'names' => [
            'index' => 'admin.slots.index',
            'create' => 'admin.slots.create',
            'store' => 'admin.slots.store',
            'show' => 'admin.slots.show',
            'edit' => 'admin.slots.edit',
            'update' => 'admin.slots.update',
            'destroy' => 'admin.slots.destroy',
        ]
    ]);
    Route::post('slots/generate-defaults', [SlotController::class, 'generateDefaults'])
        ->name('admin.slots.generate-defaults');

    // Master Schedule (booking management)
    Route::get('schedule', [MasterScheduleController::class, 'index'])->name('admin.schedule.index');
    Route::get('schedule/{master}', [MasterScheduleController::class, 'show'])->name('admin.schedule.show');
    Route::post('schedule/{master}/block-slot', [MasterScheduleController::class, 'blockSlot'])->name('admin.schedule.block-slot');
    Route::post('schedule/{master}/unblock-slot', [MasterScheduleController::class, 'unblockSlot'])->name('admin.schedule.unblock-slot');
    Route::post('schedule/{master}/block-day', [MasterScheduleController::class, 'blockDay'])->name('admin.schedule.block-day');
    Route::post('schedule/{master}/unblock-day', [MasterScheduleController::class, 'unblockDay'])->name('admin.schedule.unblock-day');

    // Orders (view and manage only, no create)
    Route::get('orders', [OrderController::class, 'index'])->name('admin.orders.index');
    Route::get('orders/new', [OrderController::class, 'newOrders'])->name('admin.orders.new');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('admin.orders.show');
    Route::post('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('admin.orders.update-status');
    Route::post('orders/{order}/slot', [OrderController::class, 'updateSlot'])->name('admin.orders.update-slot');
    Route::post('orders/{order}/note', [OrderController::class, 'addNote'])->name('admin.orders.add-note');
    Route::post('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('admin.orders.cancel');
});
