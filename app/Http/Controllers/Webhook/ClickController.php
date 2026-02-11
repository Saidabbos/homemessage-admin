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
 * Click Webhook Controller
 * Handles callbacks from Click payment system
 * 
 * @see https://docs.click.uz/
 */
class ClickController extends Controller
{
    // Click error codes
    private const SUCCESS = 0;
    private const SIGN_CHECK_FAILED = -1;
    private const INVALID_AMOUNT = -2;
    private const ACTION_NOT_FOUND = -3;
    private const ALREADY_PAID = -4;
    private const USER_NOT_FOUND = -5;
    private const TRANSACTION_NOT_FOUND = -6;
    private const FAILED_TO_UPDATE = -7;
    private const ERROR_IN_REQUEST = -8;
    private const TRANSACTION_CANCELLED = -9;

    public function __construct(
        protected TelegramNotificationService $telegramService
    ) {}

    /**
     * Handle Click prepare request
     */
    public function prepare(Request $request): JsonResponse
    {
        Log::info('Click prepare received', $request->all());

        // Verify signature
        if (!$this->verifySignature($request, 'prepare')) {
            return $this->errorResponse(self::SIGN_CHECK_FAILED, 'Invalid signature', $request);
        }

        $orderId = $request->input('merchant_trans_id');
        $amount = (float) $request->input('amount');
        $clickTransId = $request->input('click_trans_id');

        // Find order
        $order = Order::find($orderId);

        if (!$order) {
            return $this->errorResponse(self::USER_NOT_FOUND, 'Order not found', $request);
        }

        if ($order->status === Order::STATUS_CANCELLED) {
            return $this->errorResponse(self::TRANSACTION_CANCELLED, 'Order is cancelled', $request);
        }

        if ($order->payment_status === Order::PAY_PAID) {
            return $this->errorResponse(self::ALREADY_PAID, 'Order already paid', $request);
        }

        // Verify amount
        if (abs($order->total_amount - $amount) > 1) {
            return $this->errorResponse(self::INVALID_AMOUNT, 'Invalid amount', $request);
        }

        // Create or find payment
        $payment = Payment::where('provider', Payment::PROVIDER_CLICK)
            ->where('order_id', $orderId)
            ->where('status', Payment::STATUS_PENDING)
            ->first();

        if (!$payment) {
            $payment = Payment::create([
                'order_id' => $orderId,
                'provider' => Payment::PROVIDER_CLICK,
                'external_id' => $clickTransId,
                'amount' => $amount,
                'currency' => 'UZS',
                'status' => Payment::STATUS_PENDING,
            ]);
        } else {
            $payment->update(['external_id' => $clickTransId]);
        }

        Log::info('Click prepare success', [
            'payment_id' => $payment->id,
            'order_id' => $orderId,
        ]);

        return response()->json([
            'click_trans_id' => $clickTransId,
            'merchant_trans_id' => $orderId,
            'merchant_prepare_id' => $payment->id,
            'error' => self::SUCCESS,
            'error_note' => 'Success',
        ]);
    }

    /**
     * Handle Click complete request
     */
    public function complete(Request $request): JsonResponse
    {
        Log::info('Click complete received', $request->all());

        // Verify signature
        if (!$this->verifySignature($request, 'complete')) {
            return $this->errorResponse(self::SIGN_CHECK_FAILED, 'Invalid signature', $request);
        }

        $orderId = $request->input('merchant_trans_id');
        $prepareId = $request->input('merchant_prepare_id');
        $clickTransId = $request->input('click_trans_id');
        $error = (int) $request->input('error');

        // Find payment
        $payment = Payment::find($prepareId);

        if (!$payment) {
            return $this->errorResponse(self::TRANSACTION_NOT_FOUND, 'Transaction not found', $request);
        }

        if ($payment->status === Payment::STATUS_PAID) {
            return $this->errorResponse(self::ALREADY_PAID, 'Already paid', $request);
        }

        if ($payment->status === Payment::STATUS_CANCELLED) {
            return $this->errorResponse(self::TRANSACTION_CANCELLED, 'Transaction cancelled', $request);
        }

        // Check if Click reports error
        if ($error < 0) {
            $payment->markAsCancelled('Click error: ' . $error);
            
            return $this->errorResponse($error, 'Payment failed', $request);
        }

        // Mark as paid
        $payment->update(['external_id' => $clickTransId]);
        $payment->markAsPaid($clickTransId);

        // Send notification
        try {
            $this->telegramService->notifyPaid($payment->order);
        } catch (\Exception $e) {
            Log::warning('Failed to send payment notification', ['error' => $e->getMessage()]);
        }

        Log::info('Click complete success', [
            'payment_id' => $payment->id,
            'order_id' => $orderId,
        ]);

        return response()->json([
            'click_trans_id' => $clickTransId,
            'merchant_trans_id' => $orderId,
            'merchant_confirm_id' => $payment->id,
            'error' => self::SUCCESS,
            'error_note' => 'Success',
        ]);
    }

    /**
     * Verify Click signature
     */
    protected function verifySignature(Request $request, string $action): bool
    {
        $secretKey = config('services.click.secret_key');
        
        if (empty($secretKey)) {
            Log::warning('Click secret key not configured');
            return false;
        }

        $signString = $request->input('click_trans_id') .
            $request->input('service_id') .
            $secretKey .
            $request->input('merchant_trans_id') .
            ($action === 'complete' ? $request->input('merchant_prepare_id') : '') .
            $request->input('amount') .
            $request->input('action') .
            $request->input('sign_time');

        $expectedSign = md5($signString);
        $actualSign = $request->input('sign_string');

        if ($expectedSign !== $actualSign) {
            Log::warning('Click signature mismatch', [
                'expected' => $expectedSign,
                'actual' => $actualSign,
            ]);
            return false;
        }

        return true;
    }

    /**
     * Return error response
     */
    protected function errorResponse(int $code, string $message, Request $request): JsonResponse
    {
        Log::error('Click webhook error', [
            'code' => $code,
            'message' => $message,
            'request' => $request->all(),
        ]);

        return response()->json([
            'click_trans_id' => $request->input('click_trans_id'),
            'merchant_trans_id' => $request->input('merchant_trans_id'),
            'merchant_prepare_id' => $request->input('merchant_prepare_id'),
            'error' => $code,
            'error_note' => $message,
        ]);
    }
}
