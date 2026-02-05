<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Master;
use App\Models\Slot;
use App\Repositories\OrderRepository;
use App\Repositories\SlotRepository;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected OrderRepository $orderRepository,
        protected SlotRepository $slotRepository,
    ) {}

    /**
     * Display orders list
     */
    public function index(Request $request)
    {
        return Inertia::render('Admin/Orders/Index', [
            'orders' => $this->orderRepository->getFilteredPaginated($request->all()),
            'filters' => $request->only(['search', 'status', 'payment_status', 'master_id', 'date_from', 'date_to']),
            'masters' => Master::where('is_active', true)->get(['id', 'first_name', 'last_name']),
            'statuses' => $this->getStatusOptions(),
            'paymentStatuses' => $this->getPaymentStatusOptions(),
            'statusCounts' => $this->orderRepository->getStatusCounts(),
        ]);
    }

    /**
     * Display new orders only
     */
    public function newOrders(Request $request)
    {
        return Inertia::render('Admin/Orders/Index', [
            'orders' => $this->orderRepository->getFilteredPaginated(
                array_merge($request->all(), ['status' => Order::STATUS_NEW])
            ),
            'filters' => array_merge($request->only(['search', 'master_id']), ['status' => Order::STATUS_NEW]),
            'masters' => Master::where('is_active', true)->get(['id', 'first_name', 'last_name']),
            'statuses' => $this->getStatusOptions(),
            'paymentStatuses' => $this->getPaymentStatusOptions(),
            'statusCounts' => $this->orderRepository->getStatusCounts(),
            'isNewOrdersPage' => true,
        ]);
    }

    /**
     * Display order details
     */
    public function show(Order $order)
    {
        $order = $this->orderRepository->findWithDetails($order->id);

        return Inertia::render('Admin/Orders/Show', [
            'order' => $order,
            'availableStatuses' => $this->orderService->getAvailableStatuses($order),
            'slots' => $this->slotRepository->getActive(),
            'statusOptions' => $this->getStatusOptions(),
        ]);
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', [
                Order::STATUS_NEW,
                Order::STATUS_CONFIRMING,
                Order::STATUS_CONFIRMED,
                Order::STATUS_IN_PROGRESS,
                Order::STATUS_COMPLETED,
                Order::STATUS_CANCELLED,
            ]),
            'comment' => 'nullable|string|max:500',
        ]);

        try {
            $this->orderService->updateStatus($order, $validated['status'], $validated['comment'] ?? null);
            return back()->with('success', 'Status muvaffaqiyatli yangilandi');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update order slot/time
     */
    public function updateSlot(Request $request, Order $order)
    {
        $validated = $request->validate([
            'slot_id' => 'required|exists:slots,id',
            'booking_date' => 'required|date',
            'comment' => 'nullable|string|max:500',
        ]);

        try {
            $this->orderService->updateSlot(
                $order,
                $validated['slot_id'],
                $validated['booking_date'],
                $validated['comment'] ?? null
            );
            return back()->with('success', 'Vaqt muvaffaqiyatli o\'zgartirildi');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Add dispatcher note
     */
    public function addNote(Request $request, Order $order)
    {
        $validated = $request->validate([
            'note' => 'required|string|max:1000',
        ]);

        $this->orderService->addNote($order, $validated['note']);

        return back()->with('success', 'Izoh qo\'shildi');
    }

    /**
     * Cancel order
     */
    public function cancel(Request $request, Order $order)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $this->orderService->cancel($order, $validated['reason']);
            return back()->with('success', 'Buyurtma bekor qilindi');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get status options for select
     */
    protected function getStatusOptions(): array
    {
        return [
            ['value' => Order::STATUS_NEW, 'label' => 'Yangi'],
            ['value' => Order::STATUS_CONFIRMING, 'label' => 'Tasdiqlanmoqda'],
            ['value' => Order::STATUS_CONFIRMED, 'label' => 'Tasdiqlangan'],
            ['value' => Order::STATUS_IN_PROGRESS, 'label' => 'Jarayonda'],
            ['value' => Order::STATUS_COMPLETED, 'label' => 'Yakunlangan'],
            ['value' => Order::STATUS_CANCELLED, 'label' => 'Bekor qilingan'],
        ];
    }

    /**
     * Get payment status options
     */
    protected function getPaymentStatusOptions(): array
    {
        return [
            ['value' => Order::PAY_NOT_PAID, 'label' => "To'lanmagan"],
            ['value' => Order::PAY_PAID, 'label' => "To'langan"],
            ['value' => Order::PAY_REFUNDED, 'label' => 'Qaytarilgan'],
        ];
    }
}
