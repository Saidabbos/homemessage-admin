<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Mock Click Controller
 * 
 * Simulates Click payment flow for testing purposes.
 * Only works in non-production environments.
 */
class MockClickController extends Controller
{
    /**
     * Simulate full payment flow
     */
    public function simulatePayment(Request $request): JsonResponse
    {
        if (app()->environment('production') && !config('services.click.mock_enabled')) {
            return response()->json([
                'success' => false,
                'message' => 'Mock payments disabled in production',
            ], 403);
        }

        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'scenario' => 'in:success,cancel,error',
        ]);

        $order = Order::findOrFail($validated['order_id']);
        $scenario = $validated['scenario'] ?? 'success';

        Log::info('Mock Click: Starting simulation', [
            'order_id' => $order->id,
            'scenario' => $scenario,
        ]);

        try {
            // Generate mock Click transaction ID
            $clickTransId = 'mock_click_' . time() . '_' . rand(1000, 9999);

            // Step 1: Prepare
            $prepareResult = $this->sendClickRequest('prepare', [
                'click_trans_id' => $clickTransId,
                'service_id' => config('services.click.service_id', '12345'),
                'merchant_trans_id' => $order->id,
                'amount' => $order->total_amount,
                'action' => 0,
                'sign_time' => now()->format('Y-m-d H:i:s'),
            ]);

            if (($prepareResult['error'] ?? -1) !== 0) {
                return response()->json([
                    'success' => false,
                    'step' => 'prepare',
                    'error' => $prepareResult,
                ]);
            }

            $prepareId = $prepareResult['merchant_prepare_id'] ?? null;

            // Handle cancel scenario
            if ($scenario === 'cancel') {
                // Send complete with error
                $completeResult = $this->sendClickRequest('complete', [
                    'click_trans_id' => $clickTransId,
                    'service_id' => config('services.click.service_id', '12345'),
                    'merchant_trans_id' => $order->id,
                    'merchant_prepare_id' => $prepareId,
                    'amount' => $order->total_amount,
                    'action' => 1,
                    'sign_time' => now()->format('Y-m-d H:i:s'),
                    'error' => -9, // Transaction cancelled
                ]);

                return response()->json([
                    'success' => true,
                    'scenario' => 'cancel',
                    'message' => "To'lov bekor qilindi (test)",
                    'result' => $completeResult,
                ]);
            }

            // Handle error scenario
            if ($scenario === 'error') {
                $completeResult = $this->sendClickRequest('complete', [
                    'click_trans_id' => $clickTransId,
                    'service_id' => config('services.click.service_id', '12345'),
                    'merchant_trans_id' => $order->id,
                    'merchant_prepare_id' => $prepareId,
                    'amount' => $order->total_amount,
                    'action' => 1,
                    'sign_time' => now()->format('Y-m-d H:i:s'),
                    'error' => -8, // Error in request
                ]);

                return response()->json([
                    'success' => true,
                    'scenario' => 'error',
                    'message' => "To'lov xatolik bilan tugadi (test)",
                    'result' => $completeResult,
                ]);
            }

            // Step 2: Complete (success scenario)
            $completeResult = $this->sendClickRequest('complete', [
                'click_trans_id' => $clickTransId,
                'service_id' => config('services.click.service_id', '12345'),
                'merchant_trans_id' => $order->id,
                'merchant_prepare_id' => $prepareId,
                'amount' => $order->total_amount,
                'action' => 1,
                'sign_time' => now()->format('Y-m-d H:i:s'),
                'error' => 0,
            ]);

            if (($completeResult['error'] ?? -1) !== 0) {
                return response()->json([
                    'success' => false,
                    'step' => 'complete',
                    'error' => $completeResult,
                ]);
            }

            return response()->json([
                'success' => true,
                'scenario' => 'success',
                'message' => "To'lov muvaffaqiyatli o'tkazildi (test)",
                'result' => $completeResult,
            ]);

        } catch (\Exception $e) {
            Log::error('Mock Click: Simulation failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get payment status for order
     */
    public function getStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = Order::with('payments')->findOrFail($validated['order_id']);
        $latestPayment = $order->payments()
            ->where('provider', Payment::PROVIDER_CLICK)
            ->latest()
            ->first();

        return response()->json([
            'success' => true,
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'total_amount' => $order->total_amount,
                'payment_status' => $order->payment_status,
            ],
            'payment' => $latestPayment ? [
                'id' => $latestPayment->id,
                'transaction_id' => $latestPayment->transaction_id,
                'external_id' => $latestPayment->external_id,
                'status' => $latestPayment->status,
                'amount' => $latestPayment->amount,
                'provider' => $latestPayment->provider,
                'paid_at' => $latestPayment->paid_at,
            ] : null,
        ]);
    }

    /**
     * Manually trigger a specific Click endpoint (for debugging)
     */
    public function triggerMethod(Request $request): JsonResponse
    {
        if (app()->environment('production') && !config('services.click.mock_enabled')) {
            return response()->json([
                'success' => false,
                'message' => 'Mock payments disabled in production',
            ], 403);
        }

        $validated = $request->validate([
            'method' => 'required|in:prepare,complete',
            'params' => 'required|array',
        ]);

        $result = $this->sendClickRequest($validated['method'], $validated['params']);

        return response()->json([
            'success' => ($result['error'] ?? -1) === 0,
            'method' => $validated['method'],
            'result' => $result,
        ]);
    }

    /**
     * Reset payment status for order (for re-testing)
     */
    public function resetOrder(Request $request): JsonResponse
    {
        if (app()->environment('production')) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot reset payments in production',
            ], 403);
        }

        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = Order::findOrFail($validated['order_id']);

        // Delete Click payments for this order
        $order->payments()->where('provider', Payment::PROVIDER_CLICK)->delete();

        // Reset payment status if no other paid payments
        if (!$order->payments()->where('status', Payment::STATUS_PAID)->exists()) {
            $order->update(['payment_status' => Order::PAY_NOT_PAID]);
        }

        Log::info('Mock Click: Order reset', ['order_id' => $order->id]);

        return response()->json([
            'success' => true,
            'message' => "Buyurtma #{$order->order_number} Click to'lov holati tozalandi",
        ]);
    }

    /**
     * Send request to our own Click webhook endpoint
     */
    protected function sendClickRequest(string $action, array $params): array
    {
        $endpoint = $action === 'prepare' ? 'prepare' : 'complete';
        $webhookUrl = (config('app.internal_url') ?: config('app.url')) . '/api/webhook/click/' . $endpoint;

        // Generate signature
        $secretKey = config('services.click.secret_key', 'test_secret');
        $signString = $params['click_trans_id'] .
            $params['service_id'] .
            $secretKey .
            $params['merchant_trans_id'] .
            ($action === 'complete' ? ($params['merchant_prepare_id'] ?? '') : '') .
            $params['amount'] .
            ($action === 'prepare' ? 0 : 1) .
            $params['sign_time'];

        $params['sign_string'] = md5($signString);
        $params['action'] = ($action === 'prepare') ? 0 : 1;

        Log::info('Mock Click: Sending request', [
            'url' => $webhookUrl,
            'action' => $action,
        ]);

        $response = Http::asForm()->post($webhookUrl, $params);

        $result = $response->json();

        Log::info('Mock Click: Response received', [
            'action' => $action,
            'status' => $response->status(),
            'result' => $result,
        ]);

        return $result ?? ['error' => -99, 'error_note' => 'Invalid response'];
    }
}
