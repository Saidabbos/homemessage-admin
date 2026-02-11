<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceType;
use App\Models\User;
use App\Models\Order;
use App\Models\Master;
use App\Models\Rating;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Show admin dashboard
     */
    public function index()
    {
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        // Order stats by status
        $orderStats = [
            'new' => Order::where('status', Order::STATUS_NEW)->count(),
            'confirming' => Order::where('status', Order::STATUS_CONFIRMING)->count(),
            'confirmed' => Order::where('status', Order::STATUS_CONFIRMED)->count(),
            'in_progress' => Order::where('status', Order::STATUS_IN_PROGRESS)->count(),
            'completed_today' => Order::where('status', Order::STATUS_COMPLETED)
                ->whereDate('updated_at', $today)->count(),
        ];

        // Today's orders
        $todayOrders = Order::whereDate('booking_date', $today)
            ->with(['master', 'customer', 'serviceType'])
            ->orderBy('arrival_window_start')
            ->get()
            ->map(fn($o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'time' => substr($o->arrival_window_start, 0, 5) . '-' . substr($o->arrival_window_end, 0, 5),
                'master' => $o->master?->full_name,
                'customer' => $o->customer?->name,
                'service' => $o->serviceType?->name,
                'status' => $o->status,
                'payment_status' => $o->payment_status,
            ]);

        // Upcoming orders (next 3 days)
        $upcomingOrders = Order::whereBetween('booking_date', [$tomorrow, $today->copy()->addDays(3)])
            ->whereNotIn('status', [Order::STATUS_CANCELLED, Order::STATUS_COMPLETED])
            ->with(['master', 'customer', 'serviceType'])
            ->orderBy('booking_date')
            ->orderBy('arrival_window_start')
            ->limit(10)
            ->get()
            ->map(fn($o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'date' => Carbon::parse($o->booking_date)->format('d.m'),
                'time' => substr($o->arrival_window_start, 0, 5),
                'master' => $o->master?->full_name,
                'customer' => $o->customer?->name,
                'status' => $o->status,
            ]);

        // Recent orders (for activity feed)
        $recentOrders = Order::with(['master', 'customer'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'customer' => $o->customer?->name,
                'status' => $o->status,
                'created_at' => $o->created_at->diffForHumans(),
            ]);

        // General stats
        $stats = [
            'total_orders' => Order::count(),
            'total_masters' => Master::where('status', true)->count(),
            'total_customers' => Order::distinct('customer_id')->count('customer_id'),
            'total_service_types' => ServiceType::where('status', true)->count(),
        ];

        // Rating stats
        $ratingStats = [
            'total_ratings' => Rating::whereNotNull('rated_at')->count(),
            'avg_master_rating' => Rating::where('type', 'client_to_master')
                ->whereNotNull('rated_at')
                ->avg('overall_rating'),
            'avg_client_rating' => Rating::where('type', 'master_to_client')
                ->whereNotNull('rated_at')
                ->avg('overall_rating'),
            'pending_ratings' => Rating::whereNull('rated_at')->count(),
        ];

        // Recent ratings
        $recentRatings = Rating::with(['master', 'customer', 'order'])
            ->whereNotNull('rated_at')
            ->orderBy('rated_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'type' => $r->type,
                'overall_rating' => $r->overall_rating,
                'feedback' => $r->feedback,
                'master_name' => $r->master?->full_name,
                'customer_name' => $r->customer?->name,
                'order_number' => $r->order?->order_number,
                'rated_at' => $r->rated_at?->diffForHumans(),
            ]);

        // Top rated masters
        $topMasters = Master::where('status', true)
            ->whereNotNull('rating')
            ->where('rating_count', '>', 0)
            ->orderByDesc('rating')
            ->limit(5)
            ->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'name' => $m->full_name,
                'photo' => $m->photo,
                'rating' => round($m->rating, 1),
                'rating_count' => $m->rating_count,
            ]);

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'orderStats' => $orderStats,
            'todayOrders' => $todayOrders,
            'upcomingOrders' => $upcomingOrders,
            'recentOrders' => $recentOrders,
            'ratingStats' => $ratingStats,
            'recentRatings' => $recentRatings,
            'topMasters' => $topMasters,
        ]);
    }
}
