<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class OrderController extends Controller
{
    /**
     * Display customer's orders with filters and pagination.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Order::query()
            ->where('customer_id', $user->id)
            ->with(['master', 'serviceType', 'duration', 'oil']);

        // Status filter
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Search filter (order number or service name)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('serviceType', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('master', function ($q) use ($search) {
                        $q->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        $orders = $query->latest('booking_date')
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        $locale = app()->getLocale();

        $orders->getCollection()->transform(function ($order) use ($locale) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'booking_date' => $order->booking_date?->format('Y-m-d'),
                'booking_date_formatted' => $order->booking_date?->translatedFormat('d M, Y'),
                'arrival_time' => $order->arrival_window_start ? substr($order->arrival_window_start, 0, 5) : null,
                'service_name' => $order->serviceType?->getTranslation('name', $locale) ?? '-',
                'master_name' => $order->master?->full_name ?? '-',
                'duration_minutes' => $order->duration?->duration ?? 60,
                'total_amount' => (float) $order->total_amount,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
            ];
        });

        // Status counts for tabs
        $statusCounts = Order::where('customer_id', $user->id)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return Inertia::render('Customer/Orders/Index', [
            'orders' => $orders,
            'filters' => $request->only(['status', 'search']),
            'statusCounts' => $statusCounts,
            'customer' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
            ],
        ]);
    }
}
