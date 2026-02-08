<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Master;
use App\Models\Order;
use App\Models\ServiceType;
use App\Models\User;
use App\Services\SlotCalculationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class BookingSubmitController extends Controller
{
    public function __construct(
        protected SlotCalculationService $slotService
    ) {}

    /**
     * POST /api/v1/bookings
     * Yangi buyurtma yaratish
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'service_type_id' => 'required|exists:service_types,id',
            'master_id' => 'nullable|exists:masters,id',
            'date' => 'required|date|after_or_equal:today',
            'arrival_window_start' => 'required|date_format:H:i',
            'arrival_window_end' => 'required|date_format:H:i',
            'people_count' => 'integer|min:1|max:4',
            'pressure_level' => 'in:soft,medium,hard,any',
            'oil_id' => 'nullable|exists:oils,id',
            'address' => 'required|string|max:500',
            'entrance' => 'nullable|string|max:10',
            'floor' => 'nullable|string|max:10',
            'apartment' => 'nullable|string|max:10',
            'landmark' => 'nullable|string|max:255',
            'contact_phone' => 'required|string|max:20',
            'contact_name' => 'nullable|string|max:100',
            'comment' => 'nullable|string|max:1000',
        ]);

        $date = Carbon::parse($validated['date']);
        $windowStart = Carbon::parse($validated['date'] . ' ' . $validated['arrival_window_start']);
        $windowEnd = Carbon::parse($validated['date'] . ' ' . $validated['arrival_window_end']);
        $peopleCount = $validated['people_count'] ?? 1;
        $pressureLevel = $validated['pressure_level'] ?? 'medium';

        // Get service type
        $serviceType = ServiceType::findOrFail($validated['service_type_id']);
        $duration = $serviceType->duration;

        // Get or auto-select master
        $master = null;
        if (!empty($validated['master_id'])) {
            $master = Master::findOrFail($validated['master_id']);
        } else {
            // Auto-select available master
            $master = $this->autoSelectMaster($date, $windowStart, $windowEnd, $duration, $peopleCount, $serviceType);
        }

        if (!$master) {
            return response()->json([
                'success' => false,
                'message' => 'Bu vaqtga mos master topilmadi',
                'error_code' => 'NO_MASTER_AVAILABLE',
            ], 422);
        }

        // Verify slot is still available
        $availability = $this->slotService->checkSlotAvailability(
            $master,
            $date,
            $windowStart,
            $windowEnd,
            $duration,
            $peopleCount
        );

        if (!$availability['available']) {
            return response()->json([
                'success' => false,
                'message' => 'Bu slot band bo\'lib qoldi. Boshqa vaqt tanlang.',
                'error_code' => 'SLOT_NOT_AVAILABLE',
                'reason' => $availability['reason'] ?? null,
            ], 422);
        }

        // Calculate price
        $basePrice = (int) $serviceType->price;
        $totalAmount = $basePrice * $peopleCount;

        // Add oil price if selected
        if (!empty($validated['oil_id'])) {
            $oil = \App\Models\Oil::find($validated['oil_id']);
            if ($oil && $oil->price) {
                $totalAmount += (int) $oil->price * $peopleCount;
            }
        }

        try {
            $order = DB::transaction(function () use ($validated, $master, $serviceType, $date, $totalAmount, $peopleCount, $pressureLevel) {
                // Find or create customer
                $customer = $this->findOrCreateCustomer($validated['contact_phone'], $validated['contact_name'] ?? null);

                // Create order
                $order = Order::create([
                    'order_number' => Order::generateOrderNumber(),
                    'customer_id' => $customer?->id,
                    'master_id' => $master->id,
                    'service_type_id' => $serviceType->id,
                    'oil_id' => $validated['oil_id'] ?? null,
                    'people_count' => $peopleCount,
                    'pressure_level' => $pressureLevel,
                    'booking_date' => $date,
                    'arrival_window_start' => $validated['arrival_window_start'],
                    'arrival_window_end' => $validated['arrival_window_end'],
                    'status' => Order::STATUS_NEW,
                    'payment_status' => Order::PAY_NOT_PAID,
                    'total_amount' => $totalAmount,
                    'address' => $validated['address'],
                    'entrance' => $validated['entrance'] ?? null,
                    'floor' => $validated['floor'] ?? null,
                    'apartment' => $validated['apartment'] ?? null,
                    'landmark' => $validated['landmark'] ?? null,
                    'contact_phone' => $validated['contact_phone'],
                    'dispatcher_notes' => $validated['comment'] ?? null,
                ]);

                return $order;
            });

            // Send Telegram notification (async)
            $this->sendTelegramNotification($order);

            return response()->json([
                'success' => true,
                'message' => 'Buyurtma muvaffaqiyatli yaratildi',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'total_amount' => $order->total_amount,
                    'total_formatted' => number_format($order->total_amount, 0, '', ' ') . ' so\'m',
                    'master' => [
                        'id' => $master->id,
                        'name' => $master->full_name,
                    ],
                    'booking_date' => $order->booking_date->format('Y-m-d'),
                    'arrival_window' => $order->arrival_window_display,
                ],
            ], 201);

        } catch (\Exception $e) {
            Log::error('Booking creation failed', [
                'error' => $e->getMessage(),
                'request' => $validated,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Buyurtma yaratishda xatolik yuz berdi',
                'error_code' => 'BOOKING_FAILED',
            ], 500);
        }
    }

    /**
     * Auto-select available master for the slot
     */
    protected function autoSelectMaster(
        Carbon $date,
        Carbon $windowStart,
        Carbon $windowEnd,
        int $duration,
        int $peopleCount,
        ServiceType $serviceType
    ): ?Master {
        $masters = Master::where('status', true)
            ->whereHas('serviceTypes', fn ($q) => $q->where('service_types.id', $serviceType->id))
            ->get();

        foreach ($masters as $master) {
            $availability = $this->slotService->checkSlotAvailability(
                $master,
                $date,
                $windowStart,
                $windowEnd,
                $duration,
                $peopleCount
            );

            if ($availability['available']) {
                return $master;
            }
        }

        return null;
    }

    /**
     * Find or create customer by phone
     */
    protected function findOrCreateCustomer(string $phone, ?string $name): ?User
    {
        // Normalize phone
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        $customer = User::where('phone', $phone)->first();

        if (!$customer && $name) {
            // Create basic customer record
            $customer = User::create([
                'name' => $name,
                'phone' => $phone,
                'email' => null,
                'password' => bcrypt(str()->random(16)),
            ]);
            $customer->assignRole('customer');
        }

        return $customer;
    }

    /**
     * Send Telegram notification for new order
     */
    protected function sendTelegramNotification(Order $order): void
    {
        try {
            $order->load(['master', 'serviceType', 'oil']);

            $message = $this->formatTelegramMessage($order);
            
            // Get Telegram bot token and chat ID from config
            $botToken = config('services.telegram.ops_bot_token');
            $chatId = config('services.telegram.ops_group_id');

            if (!$botToken || !$chatId) {
                Log::warning('Telegram config not set, skipping notification');
                return;
            }

            $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

            $response = \Http::post($url, [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);

            if (!$response->successful()) {
                Log::error('Telegram notification failed', [
                    'order_id' => $order->id,
                    'response' => $response->body(),
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Telegram notification error', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Format Telegram message for new order
     */
    protected function formatTelegramMessage(Order $order): string
    {
        $serviceName = $order->serviceType?->getTranslation('name', 'uz') ?? 'N/A';
        $masterName = $order->master?->full_name ?? 'N/A';
        $oilName = $order->oil?->getTranslation('name', 'uz') ?? '';
        $dateFormatted = $order->booking_date->format('d.m.Y');
        $priceFormatted = number_format($order->total_amount, 0, '', ' ');

        $message = "🆕 <b>YANGI BUYURTMA</b>\n\n";
        $message .= "📋 <b>{$order->order_number}</b>\n";
        $message .= "👤 {$order->contact_phone}\n";
        $message .= "💆 {$serviceName}";
        if ($oilName) {
            $message .= " ({$oilName})";
        }
        $message .= "\n";
        $message .= "🧑‍⚕️ Master: {$masterName}\n";
        $message .= "📅 {$dateFormatted}, {$order->arrival_window_display}\n";
        $message .= "👥 {$order->people_count} kishi\n";
        $message .= "📍 {$order->address}\n\n";
        $message .= "💰 <b>{$priceFormatted} so'm</b>\n\n";
        $message .= "🔗 <a href=\"" . url("/admin/orders/{$order->id}") . "\">Admin panel</a>";

        return $message;
    }
}
