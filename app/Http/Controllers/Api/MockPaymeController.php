<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Webhook\PaymeController;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Mock Payme Controller
 *
 * Simulates Payme payment flow for testing purposes.
 * Calls webhook handler directly (no HTTP self-request).
 */
class MockPaymeController extends Controller
{
    /**
     * Simulate full payment flow
     */
    public function simulatePayment(Request $request): JsonResponse
    {
        if (app()->environment('production') && !config('services.payme.mock_enabled')) {
            return response()->json([
                'success' => false,
                'message' => 'Mock payments disabled in production',
            ], 403);
        }

        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'scenario' => 'in:success,cancel,timeout',
        ]);

        $order = Order::findOrFail($validated['order_id']);
        $scenario = $validated['scenario'] ?? 'success';

        Log::info('Mock Payme: Starting simulation', [
            'order_id' => $order->id,
            'scenario' => $scenario,
        ]);

        try {
            // Step 1: Check if transaction can be performed
            $checkResult = $this->callPaymeWebhook('CheckPerformTransaction', [
                'amount' => (int) ($order->total_amount * 100),
                'account' => ['order_id' => $order->id],
            ]);

            if (isset($checkResult['error'])) {
                return response()->json([
                    'success' => false,
                    'step' => 'CheckPerformTransaction',
                    'error' => $checkResult['error'],
                ]);
            }

            // Step 2: Create transaction
            $paymeId = 'mock_' . Str::uuid();
            $createResult = $this->callPaymeWebhook('CreateTransaction', [
                'id' => $paymeId,
                'time' => now()->timestamp * 1000,
                'amount' => (int) ($order->total_amount * 100),
                'account' => ['order_id' => $order->id],
            ]);

            if (isset($createResult['error'])) {
                return response()->json([
                    'success' => false,
                    'step' => 'CreateTransaction',
                    'error' => $createResult['error'],
                ]);
            }

            // Step 3: Handle scenario
            if ($scenario === 'cancel') {
                $cancelResult = $this->callPaymeWebhook('CancelTransaction', [
                    'id' => $paymeId,
                    'reason' => 1,
                ]);

                return response()->json([
                    'success' => true,
                    'scenario' => 'cancel',
                    'message' => "To'lov bekor qilindi (test)",
                    'result' => $cancelResult,
                ]);
            }

            if ($scenario === 'timeout') {
                return response()->json([
                    'success' => true,
                    'scenario' => 'timeout',
                    'message' => "Tranzaksiya yaratildi, lekin to'lov qilinmadi (timeout simulation)",
                    'transaction_id' => $createResult['result']['transaction'] ?? null,
                ]);
            }

            // Step 4: Perform transaction (success scenario)
            $performResult = $this->callPaymeWebhook('PerformTransaction', [
                'id' => $paymeId,
            ]);

            if (isset($performResult['error'])) {
                return response()->json([
                    'success' => false,
                    'step' => 'PerformTransaction',
                    'error' => $performResult['error'],
                ]);
            }

            return response()->json([
                'success' => true,
                'scenario' => 'success',
                'message' => "To'lov muvaffaqiyatli o'tkazildi (test)",
                'result' => $performResult,
            ]);

        } catch (\Exception $e) {
            Log::error('Mock Payme: Simulation failed', [
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
        $latestPayment = $order->payments()->latest()->first();

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
     * Manually trigger a specific Payme method (for debugging)
     */
    public function triggerMethod(Request $request): JsonResponse
    {
        if (app()->environment('production') && !config('services.payme.mock_enabled')) {
            return response()->json([
                'success' => false,
                'message' => 'Mock payments disabled in production',
            ], 403);
        }

        $validated = $request->validate([
            'method' => 'required|in:CheckPerformTransaction,CreateTransaction,PerformTransaction,CancelTransaction,CheckTransaction',
            'params' => 'required|array',
        ]);

        $result = $this->callPaymeWebhook($validated['method'], $validated['params']);

        return response()->json([
            'success' => !isset($result['error']),
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

        // Delete all payments for this order
        $order->payments()->delete();

        // Reset payment status
        $order->update(['payment_status' => Order::PAY_NOT_PAID]);

        Log::info('Mock Payme: Order reset', ['order_id' => $order->id]);

        return response()->json([
            'success' => true,
            'message' => "Buyurtma #{$order->order_number} to'lov holati tozalandi",
        ]);
    }

    /**
     * Call Payme webhook handler directly (no HTTP self-request).
     */
    protected function callPaymeWebhook(string $method, array $params): array
    {
        $mockKey = config('services.payme.key', 'test_key');
        $auth = base64_encode("Paycom:{$mockKey}");

        $payload = [
            'jsonrpc' => '2.0',
            'id' => Str::uuid()->toString(),
            'method' => $method,
            'params' => $params,
        ];

        Log::info('Mock Payme: Calling webhook directly', ['method' => $method]);

        // Create request with JSON body so $request->input() reads correctly
        $fakeRequest = Request::create('/api/webhook/payme', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Basic {$auth}",
        ], json_encode($payload));

        $controller = app(PaymeController::class);
        $response = $controller->handle($fakeRequest);

        $result = json_decode($response->getContent(), true);

        Log::info('Mock Payme: Direct call result', [
            'method' => $method,
            'result' => $result,
        ]);

        return $result;
    }
}
