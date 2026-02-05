<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Click Merchant API Controller
 *
 * Implements Click's SHOP-API protocol
 * @see https://docs.click.uz/en/click-api-request/
 */
class ClickController extends Controller
{
    // Error codes
    private const SUCCESS = 0;
    private const SIGN_CHECK_FAILED = -1;
    private const INVALID_AMOUNT = -2;
    private const ACTION_NOT_FOUND = -3;
    private const ALREADY_PAID = -4;
    private const ORDER_NOT_FOUND = -5;
    private const TRANSACTION_ERROR = -6;
    private const INVALID_ACTION = -7;
    private const TRANSACTION_CANCELLED = -9;

    /**
     * Handle Click Prepare request
     */
    public function prepare(Request $request): JsonResponse
    {
        $data = $request->all();

        Log::channel('click')->info('Click prepare request', $data);

        // Verify signature
        if (!$this->verifySign($data, 'prepare')) {
            return $this->response(self::SIGN_CHECK_FAILED, 'Sign check failed', $data);
        }

        // Find order
        $order = Order::find($data['merchant_trans_id'] ?? null);

        if (!$order) {
            return $this->response(self::ORDER_NOT_FOUND, 'Order not found', $data);
        }

        // Check amount
        $amount = (float) ($data['amount'] ?? 0);
        if ((float) $order->total_amount !== $amount) {
            return $this->response(self::INVALID_AMOUNT, 'Invalid amount', $data);
        }

        // Check if already paid
        if ($order->isPaid()) {
            return $this->response(self::ALREADY_PAID, 'Already paid', $data);
        }

        // Check if cancelled
        if ($order->isCancelled()) {
            return $this->response(self::TRANSACTION_CANCELLED, 'Order cancelled', $data);
        }

        // Create payment record
        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => Payment::PROVIDER_CLICK,
            'external_id' => $data['click_trans_id'] ?? null,
            'amount' => $amount,
            'currency' => 'UZS',
            'status' => Payment::STATUS_PENDING,
            'provider_response' => $data,
        ]);

        Log::channel('click')->info('Click prepare response', [
            'click_trans_id' => $data['click_trans_id'] ?? null,
            'merchant_trans_id' => $order->id,
            'merchant_prepare_id' => $payment->id,
        ]);

        return response()->json([
            'click_trans_id' => $data['click_trans_id'] ?? null,
            'merchant_trans_id' => (string) $order->id,
            'merchant_prepare_id' => $payment->id,
            'error' => self::SUCCESS,
            'error_note' => 'Success',
        ]);
    }

    /**
     * Handle Click Complete request
     */
    public function complete(Request $request): JsonResponse
    {
        $data = $request->all();

        Log::channel('click')->info('Click complete request', $data);

        // Verify signature
        if (!$this->verifySign($data, 'complete')) {
            return $this->response(self::SIGN_CHECK_FAILED, 'Sign check failed', $data);
        }

        // Find payment
        $payment = Payment::find($data['merchant_prepare_id'] ?? null);

        if (!$payment) {
            return $this->response(self::TRANSACTION_ERROR, 'Transaction not found', $data);
        }

        // Check if already completed
        if ($payment->isPaid()) {
            return response()->json([
                'click_trans_id' => $data['click_trans_id'] ?? null,
                'merchant_trans_id' => (string) $payment->order_id,
                'merchant_confirm_id' => $payment->id,
                'error' => self::SUCCESS,
                'error_note' => 'Success',
            ]);
        }

        // Check error from Click
        $error = (int) ($data['error'] ?? 0);

        if ($error < 0) {
            $payment->markAsCancelled('Click error: ' . ($data['error_note'] ?? 'Unknown'));

            return response()->json([
                'click_trans_id' => $data['click_trans_id'] ?? null,
                'merchant_trans_id' => (string) $payment->order_id,
                'merchant_confirm_id' => $payment->id,
                'error' => $error,
                'error_note' => $data['error_note'] ?? 'Transaction cancelled',
            ]);
        }

        // Mark as paid
        $payment->markAsPaid($data['click_trans_id'] ?? null);

        Log::channel('click')->info('Click complete response', [
            'click_trans_id' => $data['click_trans_id'] ?? null,
            'merchant_trans_id' => $payment->order_id,
            'merchant_confirm_id' => $payment->id,
        ]);

        return response()->json([
            'click_trans_id' => $data['click_trans_id'] ?? null,
            'merchant_trans_id' => (string) $payment->order_id,
            'merchant_confirm_id' => $payment->id,
            'error' => self::SUCCESS,
            'error_note' => 'Success',
        ]);
    }

    /**
     * Verify Click signature
     */
    protected function verifySign(array $data, string $action): bool
    {
        $secretKey = config('services.click.secret_key');

        if ($action === 'prepare') {
            $signString = $data['click_trans_id']
                . $data['service_id']
                . $secretKey
                . $data['merchant_trans_id']
                . $data['amount']
                . $data['action']
                . $data['sign_time'];
        } else {
            $signString = $data['click_trans_id']
                . $data['service_id']
                . $secretKey
                . $data['merchant_trans_id']
                . $data['merchant_prepare_id']
                . $data['amount']
                . $data['action']
                . $data['sign_time'];
        }

        $expectedSign = md5($signString);

        return $expectedSign === ($data['sign_string'] ?? '');
    }

    /**
     * Create response
     */
    protected function response(int $error, string $message, array $data): JsonResponse
    {
        return response()->json([
            'click_trans_id' => $data['click_trans_id'] ?? null,
            'merchant_trans_id' => $data['merchant_trans_id'] ?? '',
            'error' => $error,
            'error_note' => $message,
        ]);
    }
}
