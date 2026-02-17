<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Mappers\OrderMapper;
use App\Models\Order;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $master = $user->master;

        if (!$master) {
            abort(404);
        }

        $query = Order::query()
            ->where('master_id', $master->id)
            ->with(['customer', 'serviceType', 'duration']);

        if ($status = $request->input('status')) {
            if ($status === 'ACTIVE') {
                $query->whereNotIn('status', [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED]);
            } else {
                $query->where('status', $status);
            }
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        $orders = $query->latest('booking_date')
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        $orders->getCollection()->transform(fn($order) => OrderMapper::toMasterListItem($order));

        $statusCounts = Order::where('master_id', $master->id)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return Inertia::render('Master/Orders/Index', [
            'orders' => $orders,
            'filters' => $request->only(['status', 'search']),
            'statusCounts' => $statusCounts,
            'master' => [
                'id' => $master->id,
                'name' => $master->full_name,
                'phone' => $master->phone,
                'photo_url' => $master->photo_url,
            ],
        ]);
    }

    public function show(Order $order)
    {
        $user = Auth::user();
        $master = $user->master;

        if (!$master || $order->master_id !== $master->id) {
            abort(403);
        }

        $order->load(['customer', 'serviceType', 'duration', 'oil', 'logs']);

        $customerRating = Rating::where('order_id', $order->id)
            ->where('type', Rating::TYPE_CLIENT_TO_MASTER)
            ->whereNotNull('rated_at')
            ->first();

        $masterRating = Rating::where('order_id', $order->id)
            ->where('type', Rating::TYPE_MASTER_TO_CLIENT)
            ->first();

        $canRate = $order->isCompleted() && (!$masterRating || !$masterRating->isCompleted());

        $customerRatingData = $customerRating ? [
            'overall_rating' => $customerRating->overall_rating,
            'punctuality_rating' => $customerRating->punctuality_rating,
            'professionalism_rating' => $customerRating->professionalism_rating,
            'cleanliness_rating' => $customerRating->cleanliness_rating,
            'feedback' => $customerRating->feedback,
            'rated_at' => $customerRating->rated_at->format('d.m.Y'),
        ] : null;

        $masterRatingData = $masterRating?->isCompleted() ? [
            'overall_rating' => $masterRating->overall_rating,
            'feedback' => $masterRating->feedback,
            'rated_at' => $masterRating->rated_at->format('d.m.Y'),
        ] : null;

        return Inertia::render('Master/Orders/Show', [
            'order' => OrderMapper::toMasterDetail($order, $customerRatingData, $masterRatingData, $canRate),
            'master' => [
                'id' => $master->id,
                'name' => $master->full_name,
                'phone' => $master->phone,
                'photo_url' => $master->photo_url,
            ],
        ]);
    }
}
