<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
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

        $locale = app()->getLocale();

        $orders->getCollection()->transform(function ($order) use ($locale) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'booking_date' => $order->booking_date?->format('Y-m-d'),
                'booking_date_formatted' => $order->booking_date?->translatedFormat('d M, Y'),
                'arrival_time' => $order->arrival_window_start ? substr($order->arrival_window_start, 0, 5) : null,
                'service_name' => $order->serviceType?->getTranslation('name', $locale) ?? '-',
                'customer_name' => $order->customer?->name ?? '-',
                'customer_phone' => $order->customer?->phone ?? '-',
                'duration_minutes' => $order->duration?->duration ?? 60,
                'total_amount' => (float) $order->total_amount,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
            ];
        });

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
        $locale = app()->getLocale();

        $customerRating = Rating::where('order_id', $order->id)
            ->where('type', Rating::TYPE_CLIENT_TO_MASTER)
            ->whereNotNull('rated_at')
            ->first();

        $masterRating = Rating::where('order_id', $order->id)
            ->where('type', Rating::TYPE_MASTER_TO_CLIENT)
            ->first();

        $canRate = $order->isCompleted() && (!$masterRating || !$masterRating->isCompleted());

        return Inertia::render('Master/Orders/Show', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'booking_date_display' => $order->booking_date?->translatedFormat('d M, Y'),
                'arrival_window' => $order->arrival_window_display,
                'service_type' => $order->serviceType ? ['name' => $order->serviceType->getTranslation('name', $locale)] : null,
                'duration' => $order->duration ? ['minutes' => $order->duration->duration] : null,
                'oil' => $order->oil ? ['name' => $order->oil->getTranslation('name', $locale)] : null,
                'people_count' => $order->people_count,
                'pressure_level' => $order->pressure_level,
                'total_amount' => (float) $order->total_amount,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'address' => $order->address,
                'customer' => $order->customer ? [
                    'name' => $order->customer->name,
                    'phone' => $order->customer->phone,
                ] : null,
                'customer_rating' => $customerRating ? [
                    'overall_rating' => $customerRating->overall_rating,
                    'punctuality_rating' => $customerRating->punctuality_rating,
                    'professionalism_rating' => $customerRating->professionalism_rating,
                    'cleanliness_rating' => $customerRating->cleanliness_rating,
                    'feedback' => $customerRating->feedback,
                    'rated_at' => $customerRating->rated_at->format('d.m.Y'),
                ] : null,
                'logs' => $order->logs->map(fn ($log) => [
                    'id' => $log->id,
                    'action' => $log->action,
                    'created_at' => $log->created_at->format('d.m.Y H:i'),
                ]),
                'created_at' => $order->created_at->format('d.m.Y H:i'),
                'can_rate' => $canRate,
                'master_rating' => $masterRating?->isCompleted() ? [
                    'overall_rating' => $masterRating->overall_rating,
                    'feedback' => $masterRating->feedback,
                    'rated_at' => $masterRating->rated_at->format('d.m.Y'),
                ] : null,
            ],
            'master' => [
                'id' => $master->id,
                'name' => $master->full_name,
                'phone' => $master->phone,
                'photo_url' => $master->photo_url,
            ],
        ]);
    }
}
