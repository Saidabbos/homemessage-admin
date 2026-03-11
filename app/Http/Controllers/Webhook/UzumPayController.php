<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\TelegramNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * UzumPay Webhook Controller
 * Handles REST callbacks from Uzum Bank
 *
 * Flow: check → create → confirm
 * Additional: reverse, status
 *
 * @see https://developer.uzumbank.uz/merchant/
 */
class UzumPayController extends Controller
{
    public function __construct(
        protected TelegramNotificationService $telegramService
    ) {}

    /**
     * Check - verify payment feasibility
     */
    public function check(Request $request): JsonResponse
    {
        Log::info('UzumPay check received', $request->all());

        if (!$this->verifyAuth($request)) {
            return $this->unauthorizedResponse();
        }

        $serviceId = $request->input('serviceId');
        $params = $request->input('params', []);
        $orderId = $params['order_id'] ?? null;

        if (!$orderId) {
            return $this->errorResponse($serviceId, 'Order ID not provided');
        }

        $order = Order::find($orderId);

        if (!$order) {
            return $this->errorResponse($serviceId, 'Order not found');
        }

        if ($order->status === Order::STATUS_CANCELLED) {
            return $this->errorResponse($serviceId, 'Order is cancelled');
        }

        if ($order->payment_status === Order::PAY_PAID) {
            return $this->errorResponse($serviceId, 'Order already paid');
        }

        return response()->json([
            'serviceId' => (int) $serviceId,
            'timestamp' => $this->timestamp(),
            'status' => 'OK',
            'data' => [
                'account' => [
                    'value' => (string) $order->id,
                ],
                'order_number' => [
                    'value' => $order->order_number,
                ],
                'amount' => [
                    'value' => (string) ((int) ($order->total_amount * 100)),
                ],
            ],
        ]);
    }

    /**
     * Create - initialize transaction in our system
     */
    public function create(Request $request): JsonResponse
    {
        Log::info('UzumPay create received', $request->all());

        if (!$this->verifyAuth($request)) {
            return $this->unauthorizedResponse();
        }

        $serviceId = $request->input('serviceId');
        $transId = $request->input('transId');
        $params = $request->input('params', []);
        $orderId = $params['order_id'] ?? null;
        $amount = ($request->input('amount', 0)) / 100; // tiyin to som

        if (!$orderId || !$transId) {
            return $this->errorResponse($serviceId, 'Missing required parameters');
        }

        // Check if transaction already exists
        $existingPayment = Payment::where('external_id', $transId)
            ->where('provider', Payment::PROVIDER_UZUM)
            ->first();

        if ($existingPayment) {
            return response()->json([
                'serviceId' => (int) $serviceId,
                'transId' => $transId,
                'status' => 'CREATED',
                'transTime' => $existingPayment->created_at->timestamp * 1000,
                'amount' => (int) ($existingPayment->amount * 100),
            ]);
        }

        $order = Order::find($orderId);

        if (!$order) {
            return $this->errorResponse($serviceId, 'Order not found');
        }

        if ($order->payment_status === Order::PAY_PAID) {
            return $this->errorResponse($serviceId, 'Order already paid');
        }

        if ($order->status === Order::STATUS_CANCELLED) {
            return $this->errorResponse($serviceId, 'Order is cancelled');
        }

        // Validate amount
        if (abs($order->total_amount - $amount) > 1) {
            return $this->errorResponse($serviceId, 'Invalid amount');
        }

        // Create payment record
        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => Payment::PROVIDER_UZUM,
            'external_id' => $transId,
            'amount' => $amount,
            'currency' => 'UZS',
            'status' => Payment::STATUS_PROCESSING,
        ]);

        // Update order status
        $order->update(['payment_status' => Order::PAY_PENDING]);

        Log::info('UzumPay transaction created', [
            'payment_id' => $payment->id,
            'trans_id' => $transId,
            'order_id' => $order->id,
        ]);

        return response()->json([
            'serviceId' => (int) $serviceId,
            'transId' => $transId,
            'status' => 'CREATED',
            'transTime' => $payment->created_at->timestamp * 1000,
            'amount' => (int) ($payment->amount * 100),
        ]);
    }

    /**
     * Confirm - payment completed, deliver service
     */
    public function confirm(Request $request): JsonResponse
    {
        Log::info('UzumPay confirm received', $request->all());

        if (!$this->verifyAuth($request)) {
            return $this->unauthorizedResponse();
        }

        $serviceId = $request->input('serviceId');
        $transId = $request->input('transId');

        $payment = Payment::where('external_id', $transId)
            ->where('provider', Payment::PROVIDER_UZUM)
            ->first();

        if (!$payment) {
            return $this->errorResponse($serviceId, 'Transaction not found');
        }

        if ($payment->status === Payment::STATUS_PAID) {
            return response()->json([
                'serviceId' => (int) $serviceId,
                'transId' => $transId,
                'status' => 'CONFIRMED',
                'confirmTime' => $payment->paid_at->timestamp * 1000,
                'amount' => (int) ($payment->amount * 100),
            ]);
        }

        if ($payment->status === Payment::STATUS_CANCELLED || $payment->status === Payment::STATUS_FAILED) {
            return $this->errorResponse($serviceId, 'Transaction cancelled or failed');
        }

        // Store additional provider data
        $payment->update([
            'provider_response' => array_merge(
                $payment->provider_response ?? [],
                [
                    'payment_source' => $request->input('paymentSource'),
                    'phone' => $request->input('phone'),
                    'tariff' => $request->input('tariff'),
                    'processing_reference' => $request->input('processingReferenceNumber'),
                    'card_type' => $request->input('cardType'),
                ]
            ),
        ]);

        // Mark as paid
        $payment->markAsPaid();

        // Send notification
        try {
            $this->telegramService->notifyPaid($payment->order);
        } catch (\Exception $e) {
            Log::warning('Failed to send UzumPay notification', ['error' => $e->getMessage()]);
        }

        Log::info('UzumPay payment confirmed', [
            'payment_id' => $payment->id,
            'trans_id' => $transId,
        ]);

        return response()->json([
            'serviceId' => (int) $serviceId,
            'transId' => $transId,
            'status' => 'CONFIRMED',
            'confirmTime' => $payment->paid_at->timestamp * 1000,
            'amount' => (int) ($payment->amount * 100),
        ]);
    }

    /**
     * Reverse - cancel transaction
     */
    public function reverse(Request $request): JsonResponse
    {
        Log::info('UzumPay reverse received', $request->all());

        if (!$this->verifyAuth($request)) {
            return $this->unauthorizedResponse();
        }

        $serviceId = $request->input('serviceId');
        $transId = $request->input('transId');

        $payment = Payment::where('external_id', $transId)
            ->where('provider', Payment::PROVIDER_UZUM)
            ->first();

        if (!$payment) {
            return $this->errorResponse($serviceId, 'Transaction not found');
        }

        $wasCompleted = $payment->status === Payment::STATUS_PAID;

        if ($wasCompleted) {
            $payment->markAsRefunded();
        } else {
            $payment->markAsCancelled('Reversed by UzumPay');
        }

        Log::info('UzumPay transaction reversed', [
            'payment_id' => $payment->id,
            'trans_id' => $transId,
            'was_completed' => $wasCompleted,
        ]);

        return response()->json([
            'serviceId' => (int) $serviceId,
            'transId' => $transId,
            'status' => 'REVERSED',
            'reverseTime' => now()->timestamp * 1000,
            'amount' => (int) ($payment->amount * 100),
        ]);
    }

    /**
     * Status - query current transaction state
     */
    public function status(Request $request): JsonResponse
    {
        Log::info('UzumPay status received', $request->all());

        if (!$this->verifyAuth($request)) {
            return $this->unauthorizedResponse();
        }

        $serviceId = $request->input('serviceId');
        $transId = $request->input('transId');

        $payment = Payment::where('external_id', $transId)
            ->where('provider', Payment::PROVIDER_UZUM)
            ->first();

        if (!$payment) {
            return $this->errorResponse($serviceId, 'Transaction not found');
        }

        $uzumStatus = $this->mapToUzumStatus($payment);

        return response()->json([
            'serviceId' => (int) $serviceId,
            'transId' => $transId,
            'status' => $uzumStatus,
            'transTime' => $payment->created_at->timestamp * 1000,
            'confirmTime' => $payment->paid_at?->timestamp * 1000,
            'reverseTime' => ($payment->cancelled_at ?? $payment->refunded_at)?->timestamp * 1000,
            'amount' => (int) ($payment->amount * 100),
        ]);
    }

    /**
     * Verify Basic Auth credentials
     */
    protected function verifyAuth(Request $request): bool
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Basic ')) {
            return false;
        }

        $credentials = base64_decode(substr($authHeader, 6));
        $parts = explode(':', $credentials, 2);

        if (count($parts) !== 2) {
            return false;
        }

        [$login, $password] = $parts;

        $expectedLogin = config('services.uzum.login');
        $expectedPassword = config('services.uzum.password');

        return $login === $expectedLogin && $password === $expectedPassword;
    }

    /**
     * Map internal payment status to UzumPay status
     */
    protected function mapToUzumStatus(Payment $payment): string
    {
        return match ($payment->status) {
            Payment::STATUS_PROCESSING => 'CREATED',
            Payment::STATUS_PAID => 'CONFIRMED',
            Payment::STATUS_CANCELLED, Payment::STATUS_FAILED => 'FAILED',
            Payment::STATUS_REFUNDED => 'REVERSED',
            default => 'CREATED',
        };
    }

    /**
     * Current timestamp in milliseconds
     */
    protected function timestamp(): int
    {
        return (int) (microtime(true) * 1000);
    }

    /**
     * Error response (HTTP 400)
     */
    protected function errorResponse(int|string|null $serviceId, string $message): JsonResponse
    {
        return response()->json([
            'serviceId' => (int) ($serviceId ?? 0),
            'timestamp' => $this->timestamp(),
            'status' => 'FAILED',
            'errorMessage' => $message,
        ], 400);
    }

    /**
     * Unauthorized response (HTTP 401)
     */
    protected function unauthorizedResponse(): JsonResponse
    {
        return response()->json([
            'timestamp' => $this->timestamp(),
            'status' => 'FAILED',
            'errorMessage' => 'Unauthorized',
        ], 401);
    }
}
