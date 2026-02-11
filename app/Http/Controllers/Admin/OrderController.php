<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Master;
use App\Repositories\OrderRepository;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected OrderRepository $orderRepository,
        protected PaymentService $paymentService,
    ) {}

    /**
     * Display orders list
     */
    public function index(Request $request)
    {
        return Inertia::render('Admin/Orders/Index', [
            'orders' => $this->orderRepository->getFilteredPaginated($request->all()),
            'filters' => $request->only(['search', 'status', 'payment_status', 'master_id', 'date_from', 'date_to']),
            'masters' => Master::where('status', true)->get(['id', 'first_name', 'last_name']),
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
            'masters' => Master::where('status', true)->get(['id', 'first_name', 'last_name']),
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
            'statusOptions' => $this->getStatusOptions(),
            'callOutcomeOptions' => $this->orderService->getCallOutcomeOptions(),
            'paymentProviders' => $this->paymentService->getProvidersConfig(),
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
     * Reschedule order (change date/time)
     */
    public function reschedule(Request $request, Order $order)
    {
        $validated = $request->validate([
            'booking_date' => 'required|date',
            'arrival_window_start' => 'required|date_format:H:i',
            'arrival_window_end' => 'required|date_format:H:i|after:arrival_window_start',
            'comment' => 'nullable|string|max:500',
        ]);

        try {
            $this->orderService->reschedule(
                $order,
                $validated['booking_date'],
                $validated['arrival_window_start'],
                $validated['arrival_window_end'],
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
     * Save confirmation form (Block C - Анкета подтверждения)
     */
    public function saveConfirmation(Request $request, Order $order)
    {
        $validated = $request->validate([
            'call_outcome' => 'required|in:pending,confirmed,reschedule,no_answer,cancelled',
            'conf_entrance' => 'nullable|string|max:50',
            'conf_floor' => 'nullable|string|max:20',
            'conf_elevator' => 'boolean',
            'conf_parking' => 'nullable|string|max:200',
            'conf_landmark' => 'nullable|string|max:200',
            'conf_onsite_phone' => 'nullable|string|max:20',
            'conf_constraints' => 'nullable|string|max:500',
            'conf_space_ok' => 'boolean',
            'conf_pets' => 'boolean',
            'conf_note_to_master' => 'nullable|string|max:1000',
        ]);

        try {
            $this->orderService->saveConfirmation($order, $validated);
            return back()->with('success', 'Anketa muvaffaqiyatli saqlandi');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Generate payment link for order
     */
    public function generatePaymentLink(Request $request, Order $order)
    {
        $validated = $request->validate([
            'provider' => 'required|in:payme,click',
        ]);

        // Check if order can be paid
        if (!$order->canBePaid()) {
            return response()->json([
                'success' => false,
                'message' => 'Bu buyurtma uchun to\'lov havolasi yaratib bo\'lmaydi',
            ], 400);
        }

        $link = $this->paymentService->generateDirectPaymentLink($order, $validated['provider']);

        if (!$link) {
            return response()->json([
                'success' => false,
                'message' => 'To\'lov tizimi sozlanmagan',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'link' => $link,
            'provider' => $validated['provider'],
            'amount' => number_format($order->total_amount, 0, '', ' ') . ' so\'m',
        ]);
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

    /**
     * Send work order to master via Telegram
     */
    public function sendWorkOrder(Order $order)
    {
        $order->load(['customer', 'master', 'serviceType', 'duration', 'oil']);
        
        if (!$order->master || !$order->master->telegram_chat_id) {
            return response()->json([
                'success' => false,
                'message' => 'Master Telegram chat ID mavjud emas',
            ], 400);
        }

        // Build work order text
        $text = "📋 *НАРЯД #{$order->order_number}*\n";
        $text .= "━━━━━━━━━━━━━━━━━━━━\n\n";
        
        $text .= "👤 *Mijoz:* {$order->customer?->name}\n";
        $text .= "📞 *Tel:* " . ($order->conf_onsite_phone ?: $order->contact_phone ?: $order->customer?->phone) . "\n\n";
        
        $text .= "📍 *Manzil:* {$order->address}\n";
        if ($order->conf_entrance || $order->conf_floor) {
            $text .= "   Kirish: " . ($order->conf_entrance ?: '-') . ", Qavat: " . ($order->conf_floor ?: '-') . "\n";
        }
        if ($order->conf_elevator) {
            $text .= "   ✓ Lift bor\n";
        }
        if ($order->conf_landmark) {
            $text .= "🗺 *Mo'ljal:* {$order->conf_landmark}\n";
        }
        if ($order->conf_parking) {
            $text .= "🅿️ *Parking:* {$order->conf_parking}\n";
        }
        
        $text .= "\n📅 *Sana:* " . \Carbon\Carbon::parse($order->booking_date)->format('d.m.Y') . "\n";
        $text .= "⏰ *Kelish oynasi:* " . substr($order->arrival_window_start, 0, 5) . " – " . substr($order->arrival_window_end, 0, 5) . "\n\n";
        
        $serviceName = is_array($order->serviceType?->name) ? ($order->serviceType->name['uz'] ?? '') : $order->serviceType?->name;
        $text .= "💆 *Xizmat:* {$serviceName}\n";
        $text .= "⏱ *Davomiylik:* " . ($order->duration?->duration ?? '-') . " daqiqa\n";
        
        if ($order->oil) {
            $oilName = is_array($order->oil->name) ? ($order->oil->name['uz'] ?? '') : $order->oil->name;
            $text .= "🧴 *Yog':* {$oilName}\n";
        }
        
        if ($order->people_count > 1) {
            $text .= "👥 *Odamlar:* {$order->people_count} kishi\n";
        }
        
        if ($order->conf_constraints) {
            $text .= "\n⚠️ *Cheklovlar:* {$order->conf_constraints}\n";
        }
        if ($order->conf_pets) {
            $text .= "🐾 Hayvonlar bor!\n";
        }
        if (!$order->conf_space_ok) {
            $text .= "📐 2×2 joy yo'q - tekshiring!\n";
        }
        
        if ($order->conf_note_to_master) {
            $text .= "\n📝 *Izoh:* {$order->conf_note_to_master}\n";
        }
        
        $text .= "\n━━━━━━━━━━━━━━━━━━━━\n";
        $text .= "💰 *Summa:* " . number_format($order->total_amount, 0, '', ' ') . " so'm\n";
        $text .= "💳 *To'lov:* " . ($order->payment_status === 'PAID' ? '✅ To\'langan' : '⏳ Kutilmoqda');

        // Send via Telegram
        try {
            $botToken = config('services.telegram.bot_token');
            $chatId = $order->master->telegram_chat_id;
            
            $response = \Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'Markdown',
            ]);

            if ($response->successful() && $response->json('ok')) {
                $order->update(['work_order_sent_at' => now()]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Наряд masterga yuborildi',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Telegram xatosi: ' . ($response->json('description') ?? 'Unknown'),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Xatolik: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save QA ratings
     */
    public function saveQa(Request $request, Order $order)
    {
        $validated = $request->validate([
            'qa_overall_rating' => 'required|integer|min:1|max:5',
            'qa_punctuality_rating' => 'required|integer|min:1|max:5',
            'qa_professionalism_rating' => 'required|integer|min:1|max:5',
            'qa_feedback' => 'nullable|string|max:1000',
        ]);

        $order->update([
            'qa_overall_rating' => $validated['qa_overall_rating'],
            'qa_punctuality_rating' => $validated['qa_punctuality_rating'],
            'qa_professionalism_rating' => $validated['qa_professionalism_rating'],
            'qa_feedback' => $validated['qa_feedback'] ?? null,
            'qa_completed' => true,
            'qa_completed_at' => now(),
            'qa_completed_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Baho saqlandi');
    }
}
