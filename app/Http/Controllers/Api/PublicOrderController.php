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
            'pressure_level' => 'required|in:light,medium,strong',
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
}
