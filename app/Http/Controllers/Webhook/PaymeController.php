<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Payme Merchant API Controller
 *
 * Implements Payme's JSON-RPC 2.0 protocol
 * @see https://developer.help.paycom.uz/metody-merchant-api
 */
class PaymeController extends Controller
{
    // Error codes
    private const ERROR_INVALID_AMOUNT = -31001;
    private const ERROR_ORDER_NOT_FOUND = -31050;
    private const ERROR_CANT_PERFORM = -31008;
    private const ERROR_TRANSACTION_NOT_FOUND = -31003;
    private const ERROR_INVALID_METHOD = -32601;
    private const ERROR_AUTH_FAILED = -32504;

    /**
     * Handle Payme webhook request
     */
    public function handle(Request $request): JsonResponse
    {
        $data = $request->all();

        Log::channel('payme')->info('Payme request', $data);

        // Verify authorization
        if (!$this->verifyAuth($request)) {
            return $this->errorResponse($data['id'] ?? null, self::ERROR_AUTH_FAILED, 'Unauthorized');
        }

        $method = $data['method'] ?? null;
        $params = $data['params'] ?? [];

        $result = match ($method) {
            'CheckPerformTransaction' => $this->checkPerformTransaction($params),
            'CreateTransaction' => $this->createTransaction($params),
            'PerformTransaction' => $this->performTransaction($params),
            'CancelTransaction' => $this->cancelTransaction($params),
            'CheckTransaction' => $this->checkTransaction($params),
            'GetStatement' => $this->getStatement($params),
            default => ['error' => ['code' => self::ERROR_INVALID_METHOD, 'message' => 'Method not found']],
        };

        Log::channel('payme')->info('Payme response', $result);

        return response()->json([
            'jsonrpc' => '2.0',
            'id' => $data['id'] ?? null,
            ...$result,
        ]);
    }

    /**
     * Verify Basic Auth
     */
    protected function verifyAuth(Request $request): bool
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Basic ')) {
            return false;
        }

        $credentials = base64_decode(substr($authHeader, 6));
        [$login, $password] = explode(':', $credentials, 2) + [null, null];

        return $login === config('services.payme.merchant_id')
            && $password === config('services.payme.key');
    }

    /**
     * Check if transaction can be performed
     */
    protected function checkPerformTransaction(array $params): array
    {
        $order = $this->findOrder($params['account'] ?? []);

        if (!$order) {
            return $this->error(self::ERROR_ORDER_NOT_FOUND, 'Order not found');
        }

        $amount = ($params['amount'] ?? 0) / 100; // Payme sends in tiyin

        if ((float) $order->total_amount !== (float) $amount) {
            return $this->error(self::ERROR_INVALID_AMOUNT, 'Invalid amount');
        }

        if ($order->isPaid()) {
            return $this->error(self::ERROR_CANT_PERFORM, 'Order already paid');
        }

        if ($order->isCancelled()) {
            return $this->error(self::ERROR_CANT_PERFORM, 'Order cancelled');
        }

        return ['result' => ['allow' => true]];
    }

    /**
     * Create transaction
     */
    protected function createTransaction(array $params): array
    {
        $order = $this->findOrder($params['account'] ?? []);

        if (!$order) {
            return $this->error(self::ERROR_ORDER_NOT_FOUND, 'Order not found');
        }

        $externalId = $params['id'] ?? null;
        $amount = ($params['amount'] ?? 0) / 100;

        // Check for existing transaction
        $payment = Payment::where('external_id', $externalId)->first();

        if ($payment) {
            return ['result' => [
                'create_time' => $payment->created_at->timestamp * 1000,
                'transaction' => $payment->transaction_id,
                'state' => $this->getPaymentState($payment),
            ]];
        }

        // Create new payment
        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => Payment::PROVIDER_PAYME,
            'external_id' => $externalId,
            'amount' => $amount,
            'currency' => 'UZS',
            'status' => Payment::STATUS_PROCESSING,
            'provider_response' => $params,
        ]);

        return ['result' => [
            'create_time' => $payment->created_at->timestamp * 1000,
            'transaction' => $payment->transaction_id,
            'state' => 1,
        ]];
    }

    /**
     * Perform (complete) transaction
     */
    protected function performTransaction(array $params): array
    {
        $payment = Payment::where('external_id', $params['id'] ?? null)->first();

        if (!$payment) {
            return $this->error(self::ERROR_TRANSACTION_NOT_FOUND, 'Transaction not found');
        }

        if ($payment->isPaid()) {
            return ['result' => [
                'perform_time' => $payment->paid_at->timestamp * 1000,
                'transaction' => $payment->transaction_id,
                'state' => 2,
            ]];
        }

        if ($payment->isCancelled()) {
            return $this->error(self::ERROR_CANT_PERFORM, 'Transaction cancelled');
        }

        // Mark payment as paid
        $payment->markAsPaid($params['id']);

        return ['result' => [
            'perform_time' => $payment->paid_at->timestamp * 1000,
            'transaction' => $payment->transaction_id,
            'state' => 2,
        ]];
    }

    /**
     * Cancel transaction
     */
    protected function cancelTransaction(array $params): array
    {
        $payment = Payment::where('external_id', $params['id'] ?? null)->first();

        if (!$payment) {
            return $this->error(self::ERROR_TRANSACTION_NOT_FOUND, 'Transaction not found');
        }

        $reason = $params['reason'] ?? null;

        if ($payment->isPaid()) {
            // Refund
            $payment->markAsRefunded();
            $state = -2;
        } else {
            // Cancel
            $payment->markAsCancelled($reason);
            $state = -1;
        }

        return ['result' => [
            'cancel_time' => now()->timestamp * 1000,
            'transaction' => $payment->transaction_id,
            'state' => $state,
        ]];
    }

    /**
     * Check transaction status
     */
    protected function checkTransaction(array $params): array
    {
        $payment = Payment::where('external_id', $params['id'] ?? null)->first();

        if (!$payment) {
            return $this->error(self::ERROR_TRANSACTION_NOT_FOUND, 'Transaction not found');
        }

        return ['result' => [
            'create_time' => $payment->created_at->timestamp * 1000,
            'perform_time' => $payment->paid_at?->timestamp * 1000 ?? 0,
            'cancel_time' => $payment->cancelled_at?->timestamp * 1000 ?? 0,
            'transaction' => $payment->transaction_id,
            'state' => $this->getPaymentState($payment),
            'reason' => null,
        ]];
    }

    /**
     * Get statement (transactions list)
     */
    protected function getStatement(array $params): array
    {
        $from = ($params['from'] ?? 0) / 1000;
        $to = ($params['to'] ?? time()) / 1000;

        $payments = Payment::where('provider', Payment::PROVIDER_PAYME)
            ->whereBetween('created_at', [
                date('Y-m-d H:i:s', $from),
                date('Y-m-d H:i:s', $to),
            ])
            ->get();

        $transactions = $payments->map(fn($p) => [
            'id' => $p->external_id,
            'time' => $p->created_at->timestamp * 1000,
            'amount' => (int) ($p->amount * 100),
            'account' => ['order_id' => $p->order_id],
            'create_time' => $p->created_at->timestamp * 1000,
            'perform_time' => $p->paid_at?->timestamp * 1000 ?? 0,
            'cancel_time' => $p->cancelled_at?->timestamp * 1000 ?? 0,
            'transaction' => $p->transaction_id,
            'state' => $this->getPaymentState($p),
            'reason' => null,
        ]);

        return ['result' => ['transactions' => $transactions]];
    }

    /**
     * Find order by account params
     */
    protected function findOrder(array $account): ?Order
    {
        $orderId = $account['order_id'] ?? null;

        if (!$orderId) {
            return null;
        }

        return Order::find($orderId);
    }

    /**
     * Get payment state for Payme
     */
    protected function getPaymentState(Payment $payment): int
    {
        return match ($payment->status) {
            Payment::STATUS_PENDING, Payment::STATUS_PROCESSING => 1,
            Payment::STATUS_PAID => 2,
            Payment::STATUS_CANCELLED => -1,
            Payment::STATUS_REFUNDED => -2,
            default => 0,
        };
    }

    /**
     * Create error response
     */
    protected function error(int $code, string $message): array
    {
        return ['error' => ['code' => $code, 'message' => $message]];
    }

    /**
     * Create JSON-RPC error response
     */
    protected function errorResponse(?int $id, int $code, string $message): JsonResponse
    {
        return response()->json([
            'jsonrpc' => '2.0',
            'id' => $id,
            'error' => ['code' => $code, 'message' => $message],
        ]);
    }
}
