<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicPaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Create payment for an order from booking flow.
     */
    public function create(Request $request): JsonResponse
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'order_id' => 'required|integer',
            'provider' => 'required|in:payme,click',
        ]);

        $order = Order::where('id', $validated['order_id'])
            ->where('customer_id', auth()->id())
            ->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Buyurtma topilmadi'], 404);
        }

        if (!$order->canBePaid()) {
            return response()->json(['success' => false, 'message' => "Bu buyurtmani to'lab bo'lmaydi"], 400);
        }

        $payment = $this->paymentService->createPayment($order, $validated['provider']);

        return response()->json([
            'success' => true,
            'data' => $this->paymentService->getPaymentStatus($payment),
        ]);
    }

    /**
     * Get payment status for an order.
     */
    public function status(int $orderId): JsonResponse
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $order = Order::where('id', $orderId)
            ->where('customer_id', auth()->id())
            ->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Buyurtma topilmadi'], 404);
        }

        $latestPayment = $order->payments()->latest()->first();

        return response()->json([
            'success' => true,
            'order' => [
                'id' => $order->id,
                'payment_status' => $order->payment_status,
            ],
            'payment' => $latestPayment ? $this->paymentService->getPaymentStatus($latestPayment) : null,
        ]);
    }
}
