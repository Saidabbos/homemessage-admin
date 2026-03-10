<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Webhook\UzumPayController;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Mock UzumPay Controller
 *
 * Simulates UzumPay payment flow for testing purposes.
 * Calls webhook handler directly (no HTTP self-request).
 */
class MockUzumController extends Controller
{
    /**
     * Simulate full payment flow
     */
    public function simulatePayment(Request $request): JsonResponse
    {
        if (app()->environment('production') && !config('services.uzum.mock_enabled')) {
            return response()->json([
                'success' => false,
                'message' => 'Mock payments disabled in production',
            ], 403);
        }

        $orderId = $request->input('order_id');
        $scenario = $request->input('scenario', 'success'); // success, cancel, timeout

        $order = Order::find($orderId);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        $serviceId = config('services.uzum.service_id', 100001);
        $transId = (string) Str::uuid();
        $amountTiyin = (int) ($order->total_amount * 100);

        // Build auth header
        $login = config('services.uzum.login', 'test_login');
        $password = config('services.uzum.password', 'test_password');
        $authHeader = 'Basic ' . base64_encode("{$login}:{$password}");

        $controller = app(UzumPayController::class);

        // Step 1: Check
        $checkRequest = Request::create('/api/webhook/uzum/check', 'POST', [
            'serviceId' => $serviceId,
            'timestamp' => now()->timestamp * 1000,
            'params' => ['account' => $order->id],
        ]);
        $checkRequest->headers->set('Authorization', $authHeader);
        $checkRequest->headers->set('Content-Type', 'application/json');

        $checkResponse = $controller->check($checkRequest);
        $checkData = json_decode($checkResponse->getContent(), true);

        if (($checkData['status'] ?? '') !== 'OK') {
            return response()->json([
                'success' => false,
                'message' => 'Check failed: ' . ($checkData['errorMessage'] ?? 'Unknown error'),
                'step' => 'check',
                'response' => $checkData,
            ]);
        }

        // Step 2: Create
        $createRequest = Request::create('/api/webhook/uzum/create', 'POST', [
            'serviceId' => $serviceId,
            'timestamp' => now()->timestamp * 1000,
            'transId' => $transId,
            'params' => ['account' => $order->id],
            'amount' => $amountTiyin,
        ]);
        $createRequest->headers->set('Authorization', $authHeader);
        $createRequest->headers->set('Content-Type', 'application/json');

        $createResponse = $controller->create($createRequest);
        $createData = json_decode($createResponse->getContent(), true);

        if (($createData['status'] ?? '') !== 'CREATED') {
            return response()->json([
                'success' => false,
                'message' => 'Create failed: ' . ($createData['errorMessage'] ?? 'Unknown error'),
                'step' => 'create',
                'response' => $createData,
            ]);
        }

        // Step 3: Based on scenario
        if ($scenario === 'cancel') {
            $reverseRequest = Request::create('/api/webhook/uzum/reverse', 'POST', [
                'serviceId' => $serviceId,
                'timestamp' => now()->timestamp * 1000,
                'transId' => $transId,
            ]);
            $reverseRequest->headers->set('Authorization', $authHeader);
            $reverseRequest->headers->set('Content-Type', 'application/json');

            $controller->reverse($reverseRequest);

            return response()->json([
                'success' => true,
                'message' => 'Payment cancelled (mock)',
                'scenario' => 'cancel',
            ]);
        }

        if ($scenario === 'timeout') {
            return response()->json([
                'success' => true,
                'message' => 'Payment created but not confirmed (timeout simulation)',
                'scenario' => 'timeout',
            ]);
        }

        // Success scenario: Confirm
        $confirmRequest = Request::create('/api/webhook/uzum/confirm', 'POST', [
            'serviceId' => $serviceId,
            'timestamp' => now()->timestamp * 1000,
            'transId' => $transId,
            'paymentSource' => 'UZCARD',
            'phone' => '998901234567',
        ]);
        $confirmRequest->headers->set('Authorization', $authHeader);
        $confirmRequest->headers->set('Content-Type', 'application/json');

        $confirmResponse = $controller->confirm($confirmRequest);
        $confirmData = json_decode($confirmResponse->getContent(), true);

        if (($confirmData['status'] ?? '') !== 'CONFIRMED') {
            return response()->json([
                'success' => false,
                'message' => 'Confirm failed: ' . ($confirmData['errorMessage'] ?? 'Unknown error'),
                'step' => 'confirm',
                'response' => $confirmData,
            ]);
        }

        Log::info('UzumPay mock payment completed', [
            'order_id' => $order->id,
            'trans_id' => $transId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment completed (mock)',
            'scenario' => 'success',
            'trans_id' => $transId,
        ]);
    }

    /**
     * Get payment status for order
     */
    public function getStatus(Request $request): JsonResponse
    {
        $orderId = $request->input('order_id');
        $order = Order::find($orderId);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        $payment = $order->payments()
            ->where('provider', Payment::PROVIDER_UZUM)
            ->latest()
            ->first();

        return response()->json([
            'success' => true,
            'order' => [
                'id' => $order->id,
                'payment_status' => $order->payment_status,
            ],
            'payment' => $payment ? [
                'id' => $payment->id,
                'status' => $payment->status,
                'external_id' => $payment->external_id,
                'amount' => $payment->amount,
                'paid_at' => $payment->paid_at?->toIso8601String(),
            ] : null,
        ]);
    }

    /**
     * Reset payment status for re-testing
     */
    public function resetOrder(Request $request): JsonResponse
    {
        if (app()->environment('production') && !config('services.uzum.mock_enabled')) {
            return response()->json(['success' => false, 'message' => 'Mock disabled'], 403);
        }

        $orderId = $request->input('order_id');
        $order = Order::find($orderId);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        // Delete uzum payment records
        $order->payments()->where('provider', Payment::PROVIDER_UZUM)->delete();

        // Reset order payment status
        $order->update([
            'payment_status' => Order::PAY_NOT_PAID,
            'paid_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'UzumPay payment records reset',
        ]);
    }
}
