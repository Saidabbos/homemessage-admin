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
 * Payme Webhook Controller
 * Handles JSON-RPC callbacks from Payme
 * 
 * @see https://developer.help.paycom.uz/
 */
class PaymeController extends Controller
{
    // Payme error codes
    private const ERROR_INVALID_AMOUNT = -31001;
    private const ERROR_ORDER_NOT_FOUND = -31050;
    private const ERROR_ALREADY_PAID = -31051;
    private const ERROR_ORDER_CANCELLED = -31052;
    private const ERROR_TRANSACTION_NOT_FOUND = -31003;
    private const ERROR_UNABLE_TO_PERFORM = -31008;
    private const ERROR_INVALID_ACCOUNT = -31099;

    public function __construct(
        protected TelegramNotificationService $telegramService
    ) {}

    /**
     * Handle Payme webhook
     */
    public function handle(Request $request): JsonResponse
    {
        Log::info('Payme webhook received', [
            'method' => $request->input('method'),
            'params' => $request->input('params'),
        ]);

        // Verify authorization
        if (!$this->verifyAuth($request)) {
            return $this->errorResponse(-32504, 'Unauthorized', $request->input('id'));
        }

        $method = $request->input('method');
        $params = $request->input('params', []);
        $id = $request->input('id');

        try {
            $result = match($method) {
                'CheckPerformTransaction' => $this->checkPerformTransaction($params),
                'CreateTransaction' => $this->createTransaction($params),
                'PerformTransaction' => $this->performTransaction($params),
                'CancelTransaction' => $this->cancelTransaction($params),
                'CheckTransaction' => $this->checkTransaction($params),
                'GetStatement' => $this->getStatement($params),
                default => throw new \Exception('Method not found', -32601),
            };

            Log::info('Payme webhook success', ['method' => $method, 'result' => $result]);

            return response()->json([
                'jsonrpc' => '2.0',
                'id' => $id,
                'result' => $result,
            ]);

        } catch (\Exception $e) {
            Log::error('Payme webhook error', [
                'method' => $method,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return $this->errorResponse($e->getCode() ?: -31099, $e->getMessage(), $id);
        }
    }

    /**
     * Verify Payme authorization header
     */
    protected function verifyAuth(Request $request): bool
    {
        $authHeader = $request->header('Authorization');
        
        if (!$authHeader || !str_starts_with($authHeader, 'Basic ')) {
            return false;
        }

        $credentials = base64_decode(substr($authHeader, 6));
        [$login, $key] = explode(':', $credentials, 2);

        $expectedLogin = 'Paycom';
        $expectedKey = config('services.payme.key');

        return $login === $expectedLogin && $key === $expectedKey;
    }

    /**
     * Check if transaction can be performed
     */
    protected function checkPerformTransaction(array $params): array
    {
        $orderId = $params['account']['order_id'] ?? null;
        $amount = ($params['amount'] ?? 0) / 100; // tiyin to som

        if (!$orderId) {
            throw new \Exception('Order ID not provided', self::ERROR_INVALID_ACCOUNT);
        }

        $order = Order::find($orderId);

        if (!$order) {
            throw new \Exception('Order not found', self::ERROR_ORDER_NOT_FOUND);
        }

        if ($order->status === Order::STATUS_CANCELLED) {
            throw new \Exception('Order is cancelled', self::ERROR_ORDER_CANCELLED);
        }

        if ($order->payment_status === Order::PAY_PAID) {
            throw new \Exception('Order already paid', self::ERROR_ALREADY_PAID);
        }

        if (abs($order->total_amount - $amount) > 1) {
            throw new \Exception('Invalid amount', self::ERROR_INVALID_AMOUNT);
        }

        return ['allow' => true];
    }

    /**
     * Create transaction
     */
    protected function createTransaction(array $params): array
    {
        $paymeId = $params['id'];
        $orderId = $params['account']['order_id'] ?? null;
        $amount = ($params['amount'] ?? 0) / 100;
        $time = $params['time'];

        // Check if transaction already exists
        $existingPayment = Payment::where('external_id', $paymeId)->first();
        
        if ($existingPayment) {
            return [
                'create_time' => $existingPayment->created_at->timestamp * 1000,
                'transaction' => $existingPayment->transaction_id,
                'state' => $this->getPaymeState($existingPayment),
            ];
        }

        // Verify order
        $order = Order::find($orderId);
        
        if (!$order) {
            throw new \Exception('Order not found', self::ERROR_ORDER_NOT_FOUND);
        }

        if ($order->payment_status === Order::PAY_PAID) {
            throw new \Exception('Order already paid', self::ERROR_ALREADY_PAID);
        }

        // Create payment record
        $payment = Payment::create([
            'order_id' => $orderId,
            'provider' => Payment::PROVIDER_PAYME,
            'external_id' => $paymeId,
            'amount' => $amount,
            'currency' => 'UZS',
            'status' => Payment::STATUS_PROCESSING,
        ]);

        // Update order status
        $order->update(['payment_status' => Order::PAY_PENDING]);

        return [
            'create_time' => $payment->created_at->timestamp * 1000,
            'transaction' => $payment->transaction_id,
            'state' => 1, // Created
        ];
    }

    /**
     * Perform (complete) transaction
     */
    protected function performTransaction(array $params): array
    {
        $paymeId = $params['id'];

        $payment = Payment::where('external_id', $paymeId)->first();

        if (!$payment) {
            throw new \Exception('Transaction not found', self::ERROR_TRANSACTION_NOT_FOUND);
        }

        if ($payment->status === Payment::STATUS_PAID) {
            return [
                'transaction' => $payment->transaction_id,
                'perform_time' => $payment->paid_at->timestamp * 1000,
                'state' => 2, // Completed
            ];
        }

        if ($payment->status === Payment::STATUS_CANCELLED) {
            throw new \Exception('Transaction cancelled', self::ERROR_UNABLE_TO_PERFORM);
        }

        // Mark as paid
        $payment->markAsPaid();

        // Send notification
        try {
            $this->telegramService->notifyPaid($payment->order);
        } catch (\Exception $e) {
            Log::warning('Failed to send payment notification', ['error' => $e->getMessage()]);
        }

        return [
            'transaction' => $payment->transaction_id,
            'perform_time' => $payment->paid_at->timestamp * 1000,
            'state' => 2, // Completed
        ];
    }

    /**
     * Cancel transaction
     */
    protected function cancelTransaction(array $params): array
    {
        $paymeId = $params['id'];
        $reason = $params['reason'] ?? 1;

        $payment = Payment::where('external_id', $paymeId)->first();

        if (!$payment) {
            throw new \Exception('Transaction not found', self::ERROR_TRANSACTION_NOT_FOUND);
        }

        $wasCompleted = $payment->status === Payment::STATUS_PAID;

        if ($wasCompleted) {
            $payment->markAsRefunded();
            $state = -2; // Cancelled after completion
        } else {
            $payment->markAsCancelled('Cancelled by Payme: ' . $reason);
            $state = -1; // Cancelled before completion
        }

        return [
            'transaction' => $payment->transaction_id,
            'cancel_time' => now()->timestamp * 1000,
            'state' => $state,
        ];
    }

    /**
     * Check transaction status
     */
    protected function checkTransaction(array $params): array
    {
        $paymeId = $params['id'];

        $payment = Payment::where('external_id', $paymeId)->first();

        if (!$payment) {
            throw new \Exception('Transaction not found', self::ERROR_TRANSACTION_NOT_FOUND);
        }

        return [
            'create_time' => $payment->created_at->timestamp * 1000,
            'perform_time' => $payment->paid_at?->timestamp * 1000 ?? 0,
            'cancel_time' => $payment->cancelled_at?->timestamp * 1000 ?? 0,
            'transaction' => $payment->transaction_id,
            'state' => $this->getPaymeState($payment),
            'reason' => $payment->isCancelled() || $payment->isRefunded() ? 1 : null,
        ];
    }

    /**
     * Get statement (transaction list)
     */
    protected function getStatement(array $params): array
    {
        $from = $params['from'] / 1000;
        $to = $params['to'] / 1000;

        $payments = Payment::where('provider', Payment::PROVIDER_PAYME)
            ->whereBetween('created_at', [
                date('Y-m-d H:i:s', $from),
                date('Y-m-d H:i:s', $to),
            ])
            ->get();

        return [
            'transactions' => $payments->map(fn($p) => [
                'id' => $p->external_id,
                'time' => $p->created_at->timestamp * 1000,
                'amount' => (int) ($p->amount * 100),
                'account' => ['order_id' => $p->order_id],
                'create_time' => $p->created_at->timestamp * 1000,
                'perform_time' => $p->paid_at?->timestamp * 1000 ?? 0,
                'cancel_time' => $p->cancelled_at?->timestamp * 1000 ?? 0,
                'transaction' => $p->transaction_id,
                'state' => $this->getPaymeState($p),
                'reason' => $p->isCancelled() || $p->isRefunded() ? 1 : null,
            ])->toArray(),
        ];
    }

    /**
     * Get Payme state code from payment status
     */
    protected function getPaymeState(Payment $payment): int
    {
        return match($payment->status) {
            Payment::STATUS_PROCESSING => 1,  // Created
            Payment::STATUS_PAID => 2,        // Completed
            Payment::STATUS_CANCELLED => -1,  // Cancelled before completion
            Payment::STATUS_REFUNDED => -2,   // Cancelled after completion
            default => 1,
        };
    }

    /**
     * Return JSON-RPC error response
     */
    protected function errorResponse(int $code, string $message, $id): JsonResponse
    {
        return response()->json([
            'jsonrpc' => '2.0',
            'id' => $id,
            'error' => [
                'code' => $code,
                'message' => [
                    'ru' => $message,
                    'uz' => $message,
                    'en' => $message,
                ],
            ],
        ]);
    }
}
