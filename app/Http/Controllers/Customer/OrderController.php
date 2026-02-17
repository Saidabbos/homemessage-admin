<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Mappers\OrderMapper;
use App\Models\Order;
use App\Models\Rating;
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

        $orders->getCollection()->transform(fn($order) => OrderMapper::toListItem($order));

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

    /**
     * Display a single order with rating info.
     */
    public function show(Order $order)
    {
        $user = Auth::user();

        if ($order->customer_id !== $user->id) {
            abort(403);
        }

        $order->load(['master', 'serviceType', 'duration', 'oil', 'logs']);

        // Check if rating exists for this order
        $customerRating = Rating::where('order_id', $order->id)
            ->where('type', Rating::TYPE_CLIENT_TO_MASTER)
            ->first();

        $canRate = $order->isCompleted() && (!$customerRating || !$customerRating->isCompleted());

        $ratingData = $customerRating?->isCompleted() ? [
            'overall_rating' => $customerRating->overall_rating,
            'punctuality_rating' => $customerRating->punctuality_rating,
            'professionalism_rating' => $customerRating->professionalism_rating,
            'cleanliness_rating' => $customerRating->cleanliness_rating,
            'feedback' => $customerRating->feedback,
            'rated_at' => $customerRating->rated_at->format('d.m.Y'),
        ] : null;

        return Inertia::render('Customer/Orders/Show', [
            'order' => OrderMapper::toCustomerDetail($order, $ratingData, $canRate),
            'customer' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
            ],
            'payment' => [
                'enabled' => config('services.payment.enabled', false),
                'payme_enabled' => config('services.payme.enabled', false),
                'click_enabled' => config('services.click.enabled', false),
            ],
        ]);
    }

    /**
     * Cancel order by customer
     */
    public function cancel(Order $order)
    {
        $user = Auth::user();

        if ($order->customer_id !== $user->id) {
            abort(403);
        }

        $cancellableStatuses = ['NEW', 'CONFIRMING', 'CONFIRMED', 'WAITING_PAYMENT'];
        if (!in_array($order->status, $cancellableStatuses)) {
            return back()->with('error', 'Bu buyurtmani bekor qilib bo\'lmaydi');
        }

        $order->update([
            'status' => 'CANCELLED',
            'cancelled_at' => now(),
            'cancelled_by' => 'customer',
        ]);

        // Log the action
        $order->logs()->create([
            'action' => 'cancelled_by_customer',
            'user_id' => $user->id,
            'user_type' => 'customer',
            'notes' => 'Mijoz tomonidan bekor qilindi',
        ]);

        return redirect()->route('customer.orders')->with('success', 'Buyurtma bekor qilindi');
    }
}
