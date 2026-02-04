<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ServiceTypeController;

// Public routes
Route::get('/', function () {
    return view('welcome');
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
});
