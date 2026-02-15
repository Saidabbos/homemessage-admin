<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Rating;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Show customer dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $locale = app()->getLocale();

        // Real stats
        $totalOrders = Order::where('customer_id', $user->id)->count();
        $upcomingOrders = Order::where('customer_id', $user->id)
            ->whereNotIn('status', [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])
            ->whereDate('booking_date', '>=', today())
            ->count();
        $totalSpent = Order::where('customer_id', $user->id)
            ->where('payment_status', Order::PAY_PAID)
            ->sum('total_amount');

        // Rating summary
        $ratingSummary = [
            'average' => $user->rating ? round((float) $user->rating, 1) : null,
            'count' => $user->rating_count ?? 0,
        ];

        // Recent ratings received from masters
        $recentRatings = Rating::where('customer_id', $user->id)
            ->where('type', Rating::TYPE_MASTER_TO_CLIENT)
            ->whereNotNull('rated_at')
            ->with('master')
            ->latest('rated_at')
            ->take(3)
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'overall_rating' => $r->overall_rating,
                'feedback' => $r->feedback,
                'master_name' => $r->master?->full_name,
                'master_photo' => $r->master?->photo_url,
                'rated_at' => $r->rated_at->format('d.m.Y'),
            ]);

        // Completed orders (recent, with rating info)
        $completedOrders = Order::where('customer_id', $user->id)
            ->where('status', Order::STATUS_COMPLETED)
            ->with(['master', 'serviceType'])
            ->latest('booking_date')
            ->take(5)
            ->get()
            ->map(function ($o) use ($locale, $user) {
                $customerRating = Rating::where('order_id', $o->id)
                    ->where('type', Rating::TYPE_CLIENT_TO_MASTER)
                    ->first();

                $canRate = !$customerRating || !$customerRating->isCompleted();

                return [
                    'id' => $o->id,
                    'order_number' => $o->order_number,
                    'booking_date' => $o->booking_date?->format('d.m.Y'),
                    'service_name' => $o->serviceType?->getTranslation('name', $locale) ?? '-',
                    'master_name' => $o->master?->full_name ?? '-',
                    'master_photo' => $o->master?->photo_url,
                    'can_rate' => $canRate,
                    'rating' => $customerRating?->isCompleted() ? $customerRating->overall_rating : null,
                ];
            });

        // Upcoming bookings
        $upcomingBookings = Order::where('customer_id', $user->id)
            ->active()
            ->whereDate('booking_date', '>=', today())
            ->with(['master', 'serviceType'])
            ->orderBy('booking_date')
            ->orderBy('arrival_window_start')
            ->take(3)
            ->get()
            ->map(fn ($o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'booking_date' => $o->booking_date?->format('d.m.Y'),
                'arrival_time' => $o->arrival_window_start ? substr($o->arrival_window_start, 0, 5) : null,
                'service_name' => $o->serviceType?->getTranslation('name', $locale) ?? '-',
                'master_name' => $o->master?->full_name ?? '-',
                'master_photo' => $o->master?->photo_url,
                'status' => $o->status,
            ]);

        return Inertia::render('Customer/Dashboard', [
            'customer' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'locale' => $user->locale,
                'status' => $user->status,
                'created_at' => $user->created_at->toDateString(),
            ],
            'stats' => [
                'totalSessions' => $totalOrders,
                'upcoming' => $upcomingOrders,
                'totalSpent' => (float) $totalSpent,
            ],
            'ratingSummary' => $ratingSummary,
            'recentRatings' => $recentRatings,
            'upcomingBookings' => $upcomingBookings,
            'completedOrders' => $completedOrders,
        ]);
    }
}
