<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CancelPaymentRequest;
use App\Http\Requests\Api\CreatePaymentRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Get payment configuration
     */
    public function config(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'enabled' => $this->paymentService->isEnabled(),
                'providers' => $this->paymentService->getAvailableProviders(),
            ],
        ]);
    }

    /**
     * Create payment for order
     */
    public function create(CreatePaymentRequest $request): JsonResponse
    {
        if (!$this->paymentService->isEnabled()) {
            return response()->json([
                'success' => false,
                'message' => "To'lov tizimi hozircha ishlamaydi",
            ], 503);
        }

        $order = Order::where('order_number', $request->order_number)
            ->where('customer_id', Auth::id())
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Buyurtma topilmadi',
            ], 404);
        }

        if ($order->isPaid()) {
            return response()->json([
                'success' => false,
                'message' => "Bu buyurtma allaqachon to'langan",
            ], 400);
        }

        if ($order->isCancelled()) {
            return response()->json([
                'success' => false,
                'message' => 'Bu buyurtma bekor qilingan',
            ], 400);
        }

        $payment = $this->paymentService->createPayment($order, $request->provider);

        return response()->json([
            'success' => true,
            'data' => $this->paymentService->getPaymentStatus($payment),
        ]);
    }

    /**
     * Get payment status
     */
    public function status(string $transactionId): JsonResponse
    {
        $payment = Payment::where('transaction_id', $transactionId)
            ->whereHas('order', fn($q) => $q->where('customer_id', Auth::id()))
            ->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => "To'lov topilmadi",
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->paymentService->getPaymentStatus($payment),
        ]);
    }

    /**
     * Cancel payment
     */
    public function cancel(CancelPaymentRequest $request): JsonResponse
    {
        $payment = Payment::where('transaction_id', $request->transaction_id)
            ->whereHas('order', fn($q) => $q->where('customer_id', Auth::id()))
            ->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => "To'lov topilmadi",
            ], 404);
        }

        if (!$payment->canBeCancelled()) {
            return response()->json([
                'success' => false,
                'message' => "Bu to'lovni bekor qilib bo'lmaydi",
            ], 400);
        }

        $this->paymentService->cancelPayment($payment, 'Cancelled by user');

        return response()->json([
            'success' => true,
            'message' => "To'lov bekor qilindi",
        ]);
    }
}
