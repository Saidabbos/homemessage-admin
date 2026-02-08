<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\BookingSubmitController;
use App\Http\Controllers\Api\MasterController;
use App\Http\Controllers\Api\SlotController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ServiceTypeController;
use App\Http\Controllers\Webhook\PaymeController;
use App\Http\Controllers\Webhook\ClickController;

/*
|--------------------------------------------------------------------------
| Public API Routes
|--------------------------------------------------------------------------
|
| These routes are publicly accessible for the booking Mini App.
| No authentication required.
|
*/

// Masters
Route::prefix('masters')->group(function () {
    Route::get('/', [MasterController::class, 'index']);
    Route::get('/{master}', [MasterController::class, 'show']);
    Route::get('/{master}/slots', [MasterController::class, 'slots']);
});

// Service Types
Route::get('/service-types', [ServiceTypeController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Booking API (v1) - 3-Step Wizard
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () {
    // Step 1: Services
    Route::get('/services', [BookingController::class, 'services']);
    
    // Step 2: Masters & Time
    Route::get('/masters', [BookingController::class, 'masters']);
    Route::get('/dates/availability', [BookingController::class, 'dateAvailability']);
    Route::get('/slots', [BookingController::class, 'slots']);
    Route::get('/slots/masters', [BookingController::class, 'slotMasters']);
    
    // Price calculation
    Route::get('/booking/calculate-price', [BookingController::class, 'calculatePrice']);
    
    // Booking submission
    Route::post('/bookings', [BookingSubmitController::class, 'store']);
});

// Legacy Slots (deprecated)
Route::prefix('slots')->group(function () {
    Route::get('/available', [SlotController::class, 'available']);
    Route::get('/by-date', [SlotController::class, 'byDate']);
});

// Orders
Route::prefix('orders')->group(function () {
    Route::post('/', [OrderController::class, 'store']);
    Route::get('/{order:order_number}', [OrderController::class, 'show']);
    Route::post('/{order:order_number}/cancel', [OrderController::class, 'cancel']);
});

/*
|--------------------------------------------------------------------------
| Payment Webhook Routes
|--------------------------------------------------------------------------
|
| These routes handle payment provider webhooks.
| They have special security measures (IP whitelist, signature verification).
|
*/

Route::prefix('webhook')->group(function () {
    // Payme
    Route::post('/payme', [PaymeController::class, 'handle']);

    // Click
    Route::post('/click/prepare', [ClickController::class, 'prepare']);
    Route::post('/click/complete', [ClickController::class, 'complete']);
});
