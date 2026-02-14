<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\ServiceTypeDuration;
use App\Repositories\MasterRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PublicOrderController extends Controller
{
    public function __construct(
        private MasterRepository $masterRepository
    ) {}

    /**
     * Create a new order from public booking.
     */
    public function store(Request $request)
    {
        // Require authentication
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Iltimos, avval tizimga kiring',
            ], 401);
        }

        Log::info('PublicOrderController@store: Creating public order', [
            'user_id' => auth()->id(),
            'request' => $request->all(),
        ]);

        $validated = $request->validate([
            'service_type_id' => 'required|exists:service_types,id',
            'duration_id' => 'required|exists:service_type_durations,id',
            'master_id' => 'required|exists:masters,id',
            'date' => 'required|date',
            'arrival_window_start' => 'required|string',
            'people_count' => 'required|integer|min:1|max:5',
            'total_duration' => 'required|integer|min:30',
            'pressure_level' => 'required|in:soft,medium,hard,any',
            'notes' => 'nullable|string|max:1000',
            'services' => 'nullable|array',
            'services.*.service_type_id' => 'required_with:services|exists:service_types,id',
            'services.*.duration_id' => 'required_with:services|exists:service_type_durations,id',
        ]);

        try {
            return DB::transaction(function () use ($validated, $request) {
                // Get authenticated customer
                $customer = auth()->user();

                Log::info('PublicOrderController@store: Using authenticated customer', [
                    'customer_id' => $customer->id,
                    'phone' => $customer->phone,
                ]);

                // Get duration info
                $duration = ServiceTypeDuration::findOrFail($validated['duration_id']);

                // Calculate arrival window end (30 min window)
                $arrivalStart = $validated['arrival_window_start'];
                $arrivalEnd = date('H:i', strtotime($arrivalStart) + 30 * 60);

                // Calculate total price from services
                $totalPrice = 0;
                $services = $validated['services'] ?? [[
                    'service_type_id' => $validated['service_type_id'],
                    'duration_id' => $validated['duration_id'],
                ]];

                foreach ($services as $service) {
                    $dur = ServiceTypeDuration::find($service['duration_id']);
                    if ($dur) {
                        $totalPrice += $dur->price;
                    }
                }
                $totalPrice *= $validated['people_count'];

                // Create order
                $order = Order::create([
                    'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                    'customer_id' => $customer->id,
                    'service_type_id' => $validated['service_type_id'],
                    'master_id' => $validated['master_id'],
                    'date' => $validated['date'],
                    'arrival_window_start' => $arrivalStart,
                    'arrival_window_end' => $arrivalEnd,
                    'people_count' => $validated['people_count'],
                    'duration' => $validated['total_duration'],
                    'price' => $totalPrice,
                    'pressure_level' => $validated['pressure_level'],
                    'notes' => $validated['notes'],
                    'status' => 'pending',
                    'source' => 'web',
                ]);

                Log::info('PublicOrderController@store: Order created', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_id' => $customer->id,
                    'total_price' => $totalPrice,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Buyurtma muvaffaqiyatli yaratildi',
                    'order' => [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                    ],
                ]);
            });
        } catch (\Exception $e) {
            Log::error('PublicOrderController@store: Failed to create order', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Buyurtma yaratishda xatolik yuz berdi',
            ], 500);
        }
    }

    /**
     * Create multiple orders from cart (batch submission).
     */
    public function storeBatch(Request $request)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Iltimos, avval tizimga kiring',
            ], 401);
        }

        $validated = $request->validate([
            'orders' => 'required|array|min:1|max:10',
            'orders.*.service_type_id' => 'required|exists:service_types,id',
            'orders.*.duration_id' => 'required|exists:service_type_durations,id',
            'orders.*.master_id' => 'required|exists:masters,id',
            'orders.*.date' => 'required|date',
            'orders.*.arrival_window_start' => 'required|string',
            'orders.*.total_duration' => 'required|integer|min:30',
            'orders.*.pressure_level' => 'required|in:soft,medium,hard,any',
            'orders.*.notes' => 'nullable|string|max:1000',
        ]);

        try {
            return DB::transaction(function () use ($validated) {
                $customer = auth()->user();
                $groupId = 'GRP-' . strtoupper(Str::random(12));
                $createdOrders = [];

                foreach ($validated['orders'] as $orderData) {
                    $duration = ServiceTypeDuration::findOrFail($orderData['duration_id']);

                    $arrivalStart = $orderData['arrival_window_start'];
                    $arrivalEnd = date('H:i', strtotime($arrivalStart) + 30 * 60);

                    $order = Order::create([
                        'order_number' => Order::generateOrderNumber(),
                        'booking_group_id' => $groupId,
                        'customer_id' => $customer->id,
                        'service_type_id' => $orderData['service_type_id'],
                        'duration_id' => $orderData['duration_id'],
                        'master_id' => $orderData['master_id'],
                        'booking_date' => $orderData['date'],
                        'arrival_window_start' => $arrivalStart,
                        'arrival_window_end' => $arrivalEnd,
                        'people_count' => 1,
                        'total_amount' => $duration->price,
                        'pressure_level' => $orderData['pressure_level'],
                        'dispatcher_notes' => $orderData['notes'],
                        'status' => Order::STATUS_NEW,
                        'payment_status' => Order::PAY_NOT_PAID,
                    ]);

                    $createdOrders[] = [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                    ];
                }

                Log::info('PublicOrderController@storeBatch: Batch orders created', [
                    'group_id' => $groupId,
                    'count' => count($createdOrders),
                    'customer_id' => $customer->id,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Buyurtmalar muvaffaqiyatli yaratildi',
                    'orders' => $createdOrders,
                    'group_id' => $groupId,
                ]);
            });
        } catch (\Exception $e) {
            Log::error('PublicOrderController@storeBatch: Failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Buyurtmalar yaratishda xatolik yuz berdi',
            ], 500);
        }
    }
}
