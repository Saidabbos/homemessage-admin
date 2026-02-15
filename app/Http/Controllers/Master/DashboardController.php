<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Rating;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $master = $user->master;

        if (!$master) {
            abort(404, 'Master profile not found');
        }

        $locale = app()->getLocale();

        $totalOrders = Order::where('master_id', $master->id)->count();
        $completedOrders = Order::where('master_id', $master->id)
            ->where('status', Order::STATUS_COMPLETED)->count();
        $todaysOrders = Order::where('master_id', $master->id)
            ->whereDate('booking_date', today())
            ->whereNotIn('status', [Order::STATUS_CANCELLED])
            ->count();
        $totalEarnings = Order::where('master_id', $master->id)
            ->where('payment_status', Order::PAY_PAID)
            ->sum('total_amount');

        $ratingSummary = [
            'average' => $master->rating ? round((float) $master->rating, 1) : null,
            'count' => $master->rating_count ?? 0,
        ];

        $recentRatings = Rating::where('master_id', $master->id)
            ->where('type', Rating::TYPE_CLIENT_TO_MASTER)
            ->whereNotNull('rated_at')
            ->with('customer')
            ->latest('rated_at')
            ->take(5)
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'overall_rating' => $r->overall_rating,
                'feedback' => $r->feedback,
                'customer_name' => $r->customer?->name ?? 'Mijoz',
                'rated_at' => $r->rated_at->format('d.m.Y'),
            ]);

        $upcomingOrders = Order::where('master_id', $master->id)
            ->active()
            ->whereDate('booking_date', '>=', today())
            ->with(['customer', 'serviceType'])
            ->orderBy('booking_date')
            ->orderBy('arrival_window_start')
            ->take(5)
            ->get()
            ->map(fn ($o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'booking_date' => $o->booking_date?->format('d.m.Y'),
                'arrival_time' => $o->arrival_window_start ? substr($o->arrival_window_start, 0, 5) : null,
                'service_name' => $o->serviceType?->getTranslation('name', $locale) ?? '-',
                'customer_name' => $o->customer?->name ?? '-',
                'status' => $o->status,
            ]);

        return Inertia::render('Master/Dashboard', [
            'master' => [
                'id' => $master->id,
                'name' => $master->full_name,
                'photo_url' => $master->photo_url,
                'phone' => $master->phone,
            ],
            'stats' => [
                'totalOrders' => $totalOrders,
                'completedOrders' => $completedOrders,
                'todaysOrders' => $todaysOrders,
                'totalEarnings' => (float) $totalEarnings,
            ],
            'ratingSummary' => $ratingSummary,
            'recentRatings' => $recentRatings,
            'upcomingOrders' => $upcomingOrders,
        ]);
    }
}
