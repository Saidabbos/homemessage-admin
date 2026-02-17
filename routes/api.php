<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\BookingSubmitController;
use App\Http\Controllers\Api\MasterController;
use App\Http\Controllers\Api\SlotController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ServiceTypeController;
use App\Http\Controllers\Api\MiniAppOrderController;
use App\Http\Controllers\Api\PublicOrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Webhook\PaymeController;
use App\Http\Controllers\Webhook\ClickController;
use App\Http\Controllers\Api\MockPaymeController;
use App\Http\Controllers\Api\MockClickController;
use App\Http\Controllers\Api\PublicPaymentController;

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

// Slots
Route::prefix('slots')->group(function () {
    Route::get('/available', [SlotController::class, 'available']);
    Route::get('/by-date', [SlotController::class, 'byDate']);
    Route::get('/multi-master', [SlotController::class, 'multiMaster']);
});

// Orders
Route::prefix('orders')->group(function () {
    Route::post('/', [OrderController::class, 'store']);
    Route::get('/{order:order_number}', [OrderController::class, 'show']);
    Route::post('/{order:order_number}/cancel', [OrderController::class, 'cancel']);
});

// Mini App Orders (authenticated via session)
Route::middleware('web')->group(function () {
    Route::post('/miniapp/orders', [MiniAppOrderController::class, 'store']);
    Route::post('/public/orders', [PublicOrderController::class, 'store']);
    Route::post('/public/orders/batch', [PublicOrderController::class, 'storeBatch']);

    // User's saved addresses
    Route::get('/user/addresses', function () {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['addresses' => []]);
        }
        
        $addresses = $user->addresses()
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'name' => $a->name,
                'address' => $a->address,
                'entrance' => $a->entrance,
                'floor' => $a->floor,
                'apartment' => $a->apartment,
                'landmark' => $a->landmark,
                'is_default' => $a->is_default,
                'full_address' => $a->address . ($a->entrance ? ", kirish: {$a->entrance}" : '') . ($a->floor ? ", qavat: {$a->floor}" : '') . ($a->apartment ? ", xonadon: {$a->apartment}" : ''),
            ]);
        
        return response()->json(['addresses' => $addresses]);
    });

    // Public payment endpoints (booking flow)
    Route::post('/public/payment/create', [PublicPaymentController::class, 'create']);
    Route::get('/public/payment/status/{orderId}', [PublicPaymentController::class, 'status']);
});

// Payment API
Route::prefix('payment')->group(function () {
    Route::get('/config', [PaymentController::class, 'config']);
    
    Route::middleware('web')->group(function () {
        Route::post('/create', [PaymentController::class, 'create']);
        Route::get('/status/{transactionId}', [PaymentController::class, 'status']);
        Route::post('/cancel', [PaymentController::class, 'cancel']);
    });
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

    // Telegram
    Route::post('/telegram', [\App\Http\Controllers\Webhook\TelegramController::class, 'webhook']);
});

/*
|--------------------------------------------------------------------------
| Mock Payment Routes (Testing Only)
|--------------------------------------------------------------------------
|
| These routes simulate payment provider behavior for testing.
| Disabled in production unless PAYME_MOCK_ENABLED=true
|
*/

Route::prefix('mock/payme')->middleware('web')->group(function () {
    Route::post('/simulate', [MockPaymeController::class, 'simulatePayment']);
    Route::get('/status', [MockPaymeController::class, 'getStatus']);
    Route::post('/trigger', [MockPaymeController::class, 'triggerMethod']);
    Route::post('/reset', [MockPaymeController::class, 'resetOrder']);
});

Route::prefix('mock/click')->middleware('web')->group(function () {
    Route::post('/simulate', [MockClickController::class, 'simulatePayment']);
    Route::get('/status', [MockClickController::class, 'getStatus']);
    Route::post('/trigger', [MockClickController::class, 'triggerMethod']);
    Route::post('/reset', [MockClickController::class, 'resetOrder']);
});
