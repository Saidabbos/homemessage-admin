<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\BatchOrderRequest;
use App\Http\Requests\Api\CreatePublicOrderRequest;
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
    public function store(CreatePublicOrderRequest $request)
    {
        Log::info('PublicOrderController@store: Creating public order', [
            'user_id' => auth()->id(),
            'request' => $request->all(),
        ]);

        $validated = $request->validated();

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
    public function storeBatch(BatchOrderRequest $request)
    {
        $validated = $request->validated();

        try {
            return DB::transaction(function () use ($validated) {
                $customer = auth()->user();
                $groupId = 'GRP-' . strtoupper(Str::random(12));
                $createdOrders = [];

                // Address data (shared across all orders in batch)
                $addressData = [
                    'address' => $validated['address'] ?? null,
                    'entrance' => $validated['entrance'] ?? null,
                    'floor' => $validated['floor'] ?? null,
                    'apartment' => $validated['apartment'] ?? null,
                    'landmark' => $validated['landmark'] ?? null,
                ];

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
                        // Address fields
                        ...$addressData,
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
                    'address' => $addressData['address'] ?? 'not provided',
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
