<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Master;
use App\Models\Slot;
use App\Models\MasterSlotBooking;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Create a new order
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'master_id' => 'required|exists:masters,id',
            'slot_id' => 'required|exists:slots,id',
            'service_type_id' => 'required|exists:service_types,id',
            'oil_id' => 'nullable|exists:oils,id',
            'booking_date' => 'required|date|after_or_equal:today',

            // Customer info
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'telegram_id' => 'nullable|string|max:50',

            // Address
            'address' => 'required|string|max:500',
            'entrance' => 'nullable|string|max:10',
            'floor' => 'nullable|string|max:10',
            'apartment' => 'nullable|string|max:10',
            'landmark' => 'nullable|string|max:255',
        ]);

        // Check slot availability with lock to prevent double booking
        try {
            $order = DB::transaction(function () use ($validated) {
                // Lock the slot booking record
                $booking = MasterSlotBooking::where('master_id', $validated['master_id'])
                    ->where('slot_id', $validated['slot_id'])
                    ->where('date', $validated['booking_date'])
                    ->lockForUpdate()
                    ->first();

                // Check if slot is available
                if ($booking && $booking->status !== MasterSlotBooking::STATUS_FREE) {
                    throw ValidationException::withMessages([
                        'slot_id' => ['Bu vaqt band. Iltimos, boshqa vaqt tanlang.'],
                    ]);
                }

                // Get master and service type for pricing
                $master = Master::with('serviceTypes')->findOrFail($validated['master_id']);
                $serviceType = $master->serviceTypes->find($validated['service_type_id']);

                if (!$serviceType) {
                    throw ValidationException::withMessages([
                        'service_type_id' => ['Bu master ushbu xizmatni ko\'rsatmaydi.'],
                    ]);
                }

                // Create or find customer
                $customer = $this->findOrCreateCustomer($validated);

                // Create order
                $order = Order::create([
                    'order_number' => Order::generateOrderNumber(),
                    'customer_id' => $customer->id,
                    'master_id' => $validated['master_id'],
                    'slot_id' => $validated['slot_id'],
                    'service_type_id' => $validated['service_type_id'],
                    'oil_id' => $validated['oil_id'] ?? null,
                    'booking_date' => $validated['booking_date'],
                    'status' => Order::STATUS_NEW,
                    'payment_status' => Order::PAY_NOT_PAID,
                    'total_amount' => $serviceType->price,
                    'address' => $validated['address'],
                    'entrance' => $validated['entrance'] ?? null,
                    'floor' => $validated['floor'] ?? null,
                    'apartment' => $validated['apartment'] ?? null,
                    'landmark' => $validated['landmark'] ?? null,
                    'contact_phone' => $validated['customer_phone'],
                ]);

                // Reserve the slot
                if ($booking) {
                    $booking->update([
                        'status' => MasterSlotBooking::STATUS_PENDING,
                        'order_id' => $order->id,
                    ]);
                } else {
                    MasterSlotBooking::create([
                        'master_id' => $validated['master_id'],
                        'slot_id' => $validated['slot_id'],
                        'date' => $validated['booking_date'],
                        'status' => MasterSlotBooking::STATUS_PENDING,
                        'order_id' => $order->id,
                    ]);
                }

                return $order;
            });

            $order->load(['master', 'slot', 'serviceType', 'oil']);

            return response()->json([
                'success' => true,
                'message' => 'Buyurtma muvaffaqiyatli yaratildi',
                'data' => $this->formatOrder($order),
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Buyurtma yaratishda xatolik yuz berdi',
            ], 500);
        }
    }

    /**
     * Get order details by order number
     */
    public function show(Order $order): JsonResponse
    {
        $order->load(['master', 'slot', 'serviceType', 'oil', 'payments']);

        return response()->json([
            'success' => true,
            'data' => $this->formatOrder($order, true),
        ]);
    }

    /**
     * Cancel an order
     */
    public function cancel(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        // Check if order can be cancelled
        if (!$order->canChangeStatus()) {
            return response()->json([
                'success' => false,
                'message' => 'Bu buyurtmani bekor qilib bo\'lmaydi',
            ], 400);
        }

        // Check cancellation time (e.g., 2 hours before)
        $bookingDateTime = $order->booking_date->setTimeFromTimeString($order->slot->start_time);
        $hoursUntilBooking = now()->diffInHours($bookingDateTime, false);

        if ($hoursUntilBooking < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Buyurtmani 2 soatdan kam vaqt qolganda bekor qilib bo\'lmaydi',
            ], 400);
        }

        try {
            $this->orderService->cancel($order, $validated['reason'] ?? 'Mijoz tomonidan bekor qilindi');

            return response()->json([
                'success' => true,
                'message' => 'Buyurtma bekor qilindi',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Find or create customer from validated data
     */
    protected function findOrCreateCustomer(array $data)
    {
        return \App\Models\User::firstOrCreate(
            ['phone' => $data['customer_phone']],
            [
                'name' => $data['customer_name'],
                'email' => null,
                'password' => bcrypt(\Illuminate\Support\Str::random(16)),
                'telegram_id' => $data['telegram_id'] ?? null,
            ]
        );
    }

    /**
     * Format order for API response
     */
    protected function formatOrder(Order $order, bool $detailed = false): array
    {
        $locale = app()->getLocale();

        $data = [
            'order_number' => $order->order_number,
            'status' => $order->status,
            'status_label' => $order->status_label,
            'payment_status' => $order->payment_status,
            'payment_status_label' => $order->payment_status_label,
            'booking_date' => $order->booking_date->format('Y-m-d'),
            'booking_time' => $order->booking_time,
            'total_amount' => (float) $order->total_amount,
            'master' => [
                'id' => $order->master->id,
                'name' => $order->master->full_name,
                'photo' => $order->master->photo ? asset('storage/' . $order->master->photo) : null,
            ],
            'service_type' => [
                'id' => $order->serviceType->id,
                'name' => $order->serviceType->getTranslation('name', $locale),
            ],
            'created_at' => $order->created_at->toIso8601String(),
        ];

        if ($detailed) {
            $data['address'] = $order->full_address;
            $data['contact_phone'] = $order->contact_phone;
            $data['oil'] = $order->oil ? [
                'id' => $order->oil->id,
                'name' => $order->oil->getTranslation('name', $locale),
            ] : null;
            $data['payments'] = $order->payments->map(fn($p) => [
                'provider' => $p->provider,
                'status' => $p->status,
                'amount' => (float) $p->amount,
                'payment_url' => $p->payment_url,
            ]);
        }

        return $data;
    }
}
