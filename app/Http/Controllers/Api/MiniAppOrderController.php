<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateMiniAppOrderRequest;
use App\Models\Master;
use App\Models\Order;
use App\Models\ServiceTypeDuration;
use App\Services\SlotCalculationService;
use App\Services\TelegramNotificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MiniAppOrderController extends Controller
{
    public function __construct(
        protected SlotCalculationService $slotService,
        protected TelegramNotificationService $telegramService
    ) {}

    /**
     * Create order(s) from Mini App
     * Supports multi-master booking - creates linked orders for each master
     */
    public function store(CreateMiniAppOrderRequest $request): JsonResponse
    {
        Log::info('MiniApp: Order creation attempt', $request->all());

        $validated = $request->validated();

        // Get current user
        $user = Auth::user();

        // Determine master IDs
        $hasMasterIds = $request->has('master_ids') && is_array($request->master_ids) && count($request->master_ids) > 0;
        $masterIds = $hasMasterIds 
            ? $validated['master_ids'] 
            : [$validated['master_id']];

        $peopleCount = $validated['people_count'] ?? 1;

        // Master count can be 1 (same master for all) or equal to people count
        if (count($masterIds) > $peopleCount) {
            return response()->json([
                'success' => false,
                'message' => 'Usta soni kishi sonidan ko\'p bo\'lishi mumkin emas',
            ], 422);
        }

        try {
            $orders = DB::transaction(function () use ($validated, $user, $masterIds, $peopleCount) {
                $duration = ServiceTypeDuration::findOrFail($validated['duration_id']);
                $durationMinutes = $duration->duration;
                $pricePerPerson = $duration->price;

                $arrivalStart = $validated['arrival_window_start'];
                $arrivalEnd = Carbon::createFromFormat('H:i', $arrivalStart)->addMinutes(30)->format('H:i');
                $date = Carbon::parse($validated['date']);

                $createdOrders = [];
                $masterCount = count($masterIds);

                // Case 1: Single master for all people
                if ($masterCount === 1) {
                    $master = Master::findOrFail($masterIds[0]);
                    
                    if (!$master->status) {
                        throw ValidationException::withMessages([
                            'master_ids' => ["{$master->name} hozirda mavjud emas."],
                        ]);
                    }

                    // Validate slot availability
                    $availableSlots = $this->slotService->getSlotsForMaster(
                        $master,
                        $date,
                        $durationMinutes,
                        $peopleCount
                    );

                    $slotAvailable = collect($availableSlots)->contains(function ($slot) use ($arrivalStart) {
                        return $slot['start'] === $arrivalStart || $slot['start'] === $arrivalStart . ':00';
                    });

                    if (!$slotAvailable) {
                        throw ValidationException::withMessages([
                            'arrival_window_start' => ['Bu vaqt band. Iltimos, boshqa vaqt tanlang.'],
                        ]);
                    }

                    // Create single order
                    $order = Order::create([
                        'order_number' => Order::generateOrderNumber(),
                        'booking_group_id' => null,
                        'customer_id' => $user->id,
                        'master_id' => $masterIds[0],
                        'service_type_id' => $validated['service_type_id'],
                        'duration_id' => $validated['duration_id'],
                        'people_count' => $peopleCount,
                        'pressure_level' => $validated['pressure_level'] ?? 'medium',
                        'booking_date' => $validated['date'],
                        'arrival_window_start' => $arrivalStart,
                        'arrival_window_end' => $arrivalEnd,
                        'status' => Order::STATUS_NEW,
                        'payment_status' => Order::PAY_NOT_PAID,
                        'total_amount' => $pricePerPerson * $peopleCount,
                        'dispatcher_notes' => $validated['notes'] ?? null,
                        'contact_phone' => $user->phone,
                    ]);

                    $createdOrders[] = $order;

                    Log::info('MiniApp: Single-master order created', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'master_id' => $masterIds[0],
                        'people_count' => $peopleCount,
                    ]);
                }
                // Case 2: Multiple masters (one per person)
                else {
                    $bookingGroupId = (string) Str::uuid();

                    foreach ($masterIds as $index => $masterId) {
                        $master = Master::findOrFail($masterId);
                        
                        if (!$master->status) {
                            throw ValidationException::withMessages([
                                'master_ids' => ["{$master->name} hozirda mavjud emas."],
                            ]);
                        }

                        // Validate slot availability for this master
                        $availableSlots = $this->slotService->getSlotsForMaster(
                            $master,
                            $date,
                            $durationMinutes,
                            1
                        );

                        $slotAvailable = collect($availableSlots)->contains(function ($slot) use ($arrivalStart) {
                            return $slot['start'] === $arrivalStart || $slot['start'] === $arrivalStart . ':00';
                        });

                        if (!$slotAvailable) {
                            throw ValidationException::withMessages([
                                'arrival_window_start' => ["{$master->name} uchun bu vaqt band."],
                            ]);
                        }

                        // Create order for this master
                        $order = Order::create([
                            'order_number' => Order::generateOrderNumber(),
                            'booking_group_id' => $bookingGroupId,
                            'customer_id' => $user->id,
                            'master_id' => $masterId,
                            'service_type_id' => $validated['service_type_id'],
                            'duration_id' => $validated['duration_id'],
                            'people_count' => 1,
                            'pressure_level' => $validated['pressure_level'] ?? 'medium',
                            'booking_date' => $validated['date'],
                            'arrival_window_start' => $arrivalStart,
                            'arrival_window_end' => $arrivalEnd,
                            'status' => Order::STATUS_NEW,
                            'payment_status' => Order::PAY_NOT_PAID,
                            'total_amount' => $pricePerPerson,
                            'dispatcher_notes' => $validated['notes'] ?? null,
                            'contact_phone' => $user->phone,
                        ]);

                        $createdOrders[] = $order;

                        Log::info('MiniApp: Multi-master order created', [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'master_id' => $masterId,
                            'group_id' => $bookingGroupId,
                        ]);
                    }
                }

                return $createdOrders;
            });

            // Return first order number for redirect
            $primaryOrder = $orders[0];
            $totalAmount = array_sum(array_map(fn($o) => $o->total_amount, $orders));

            // Send Telegram notification to dispatchers
            try {
                if (count($orders) > 1) {
                    $this->telegramService->notifyGroupedOrders($orders, $primaryOrder->booking_group_id);
                } else {
                    $this->telegramService->notifyNewOrder($primaryOrder);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to send Telegram notification', ['error' => $e->getMessage()]);
            }

            return response()->json([
                'success' => true,
                'message' => count($orders) > 1 
                    ? count($orders) . ' ta buyurtma muvaffaqiyatli yaratildi'
                    : 'Buyurtma muvaffaqiyatli yaratildi',
                'data' => [
                    'order_number' => $primaryOrder->order_number,
                    'booking_group_id' => $primaryOrder->booking_group_id,
                    'order_count' => count($orders),
                    'total_amount' => $totalAmount,
                    'status' => $primaryOrder->status,
                ],
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('MiniApp: Order creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Buyurtma yaratishda xatolik yuz berdi',
            ], 500);
        }
    }
}
