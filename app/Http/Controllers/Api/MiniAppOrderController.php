<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Master;
use App\Models\ServiceTypeDuration;
use App\Services\SlotCalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class MiniAppOrderController extends Controller
{
    public function __construct(
        protected SlotCalculationService $slotService
    ) {}

    /**
     * Create a new order from Mini App
     */
    public function store(Request $request): JsonResponse
    {
        Log::info('MiniApp: Order creation attempt', $request->all());

        $validated = $request->validate([
            'master_id' => 'required|exists:masters,id',
            'service_type_id' => 'required|exists:service_types,id',
            'duration_id' => 'required|exists:service_type_durations,id',
            'date' => 'required|date|after_or_equal:today',
            'arrival_window_start' => 'required|date_format:H:i',
            'people_count' => 'integer|min:1|max:5',
            'pressure_level' => 'in:light,medium,strong',
            'notes' => 'nullable|string|max:500',
        ]);

        // Get current user
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated',
            ], 401);
        }

        try {
            $order = DB::transaction(function () use ($validated, $user) {
                // Get master
                $master = Master::findOrFail($validated['master_id']);
                if (!$master->status) {
                    throw ValidationException::withMessages([
                        'master_id' => ['Bu master hozirda mavjud emas.'],
                    ]);
                }

                // Get duration
                $duration = ServiceTypeDuration::findOrFail($validated['duration_id']);
                $durationMinutes = $duration->duration;
                $price = $duration->price;

                // Calculate arrival window end (30 min after start)
                $arrivalStart = $validated['arrival_window_start'];
                $arrivalEnd = Carbon::createFromFormat('H:i', $arrivalStart)->addMinutes(30)->format('H:i');

                // Validate slot availability
                $date = Carbon::parse($validated['date']);
                $peopleCount = $validated['people_count'] ?? 1;
                
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

                // Calculate total amount
                $totalAmount = $price * $peopleCount;

                // Create order
                $order = Order::create([
                    'order_number' => Order::generateOrderNumber(),
                    'customer_id' => $user->id,
                    'master_id' => $validated['master_id'],
                    'service_type_id' => $validated['service_type_id'],
                    'duration_id' => $validated['duration_id'],
                    'people_count' => $peopleCount,
                    'pressure_level' => $validated['pressure_level'] ?? 'medium',
                    'booking_date' => $validated['date'],
                    'arrival_window_start' => $arrivalStart,
                    'arrival_window_end' => $arrivalEnd,
                    'status' => Order::STATUS_NEW,
                    'payment_status' => Order::PAY_NOT_PAID,
                    'total_amount' => $totalAmount,
                    'dispatcher_notes' => $validated['notes'] ?? null,
                    'contact_phone' => $user->phone,
                ]);

                Log::info('MiniApp: Order created', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'user_id' => $user->id,
                ]);

                return $order;
            });

            return response()->json([
                'success' => true,
                'message' => 'Buyurtma muvaffaqiyatli yaratildi',
                'data' => [
                    'order_number' => $order->order_number,
                    'status' => $order->status,
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
