<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Models\Master;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RatingController extends Controller
{
    public function index(Request $request)
    {
        $query = Rating::with(['master', 'customer', 'order'])
            ->orderBy('created_at', 'desc');

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status (rated or pending)
        if ($request->filled('status')) {
            if ($request->status === 'rated') {
                $query->whereNotNull('rated_at');
            } else {
                $query->whereNull('rated_at');
            }
        }

        // Filter by master
        if ($request->filled('master_id')) {
            $query->where('master_id', $request->master_id);
        }

        // Filter by rating value
        if ($request->filled('rating')) {
            $query->where('overall_rating', $request->rating);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('master', function ($mq) use ($search) {
                    $mq->where('first_name', 'like', "%{$search}%")
                       ->orWhere('last_name', 'like', "%{$search}%");
                })
                ->orWhereHas('customer', function ($cq) use ($search) {
                    $cq->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('order', function ($oq) use ($search) {
                    $oq->where('order_number', 'like', "%{$search}%");
                });
            });
        }

        $ratings = $query->paginate(20)->through(fn ($r) => [
            'id' => $r->id,
            'type' => $r->type,
            'order_number' => $r->order?->order_number,
            'order_id' => $r->order_id,
            'master_id' => $r->master_id,
            'master_name' => $r->master?->full_name,
            'master_photo' => $r->master?->photo,
            'customer_id' => $r->customer_id,
            'customer_name' => $r->customer?->name,
            'overall_rating' => $r->overall_rating,
            'punctuality_rating' => $r->punctuality_rating,
            'professionalism_rating' => $r->professionalism_rating,
            'cleanliness_rating' => $r->cleanliness_rating,
            'feedback' => $r->feedback,
            'rated_at' => $r->rated_at?->format('d.m.Y H:i'),
            'created_at' => $r->created_at->format('d.m.Y H:i'),
            'is_rated' => $r->rated_at !== null,
        ]);

        // Stats
        $stats = [
            'total' => Rating::count(),
            'rated' => Rating::whereNotNull('rated_at')->count(),
            'pending' => Rating::whereNull('rated_at')->count(),
            'avg_master' => Rating::where('type', 'client_to_master')->whereNotNull('rated_at')->avg('overall_rating'),
            'avg_client' => Rating::where('type', 'master_to_client')->whereNotNull('rated_at')->avg('overall_rating'),
        ];

        // Masters for filter
        $masters = Master::where('status', true)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name'])
            ->map(fn ($m) => ['id' => $m->id, 'name' => $m->full_name]);

        return Inertia::render('Admin/Ratings/Index', [
            'ratings' => $ratings,
            'stats' => $stats,
            'masters' => $masters,
            'filters' => $request->only(['type', 'status', 'master_id', 'rating', 'search']),
        ]);
    }

    public function show(Rating $rating)
    {
        $rating->load(['master', 'customer', 'order.serviceType']);

        return Inertia::render('Admin/Ratings/Show', [
            'rating' => [
                'id' => $rating->id,
                'type' => $rating->type,
                'order' => $rating->order ? [
                    'id' => $rating->order->id,
                    'order_number' => $rating->order->order_number,
                    'service' => $rating->order->serviceType?->name,
                    'booking_date' => $rating->order->booking_date?->format('d.m.Y'),
                ] : null,
                'master' => $rating->master ? [
                    'id' => $rating->master->id,
                    'name' => $rating->master->full_name,
                    'photo' => $rating->master->photo,
                    'rating' => $rating->master->rating,
                    'rating_count' => $rating->master->rating_count,
                ] : null,
                'customer' => $rating->customer ? [
                    'id' => $rating->customer->id,
                    'name' => $rating->customer->name,
                    'phone' => $rating->customer->phone,
                ] : null,
                'overall_rating' => $rating->overall_rating,
                'punctuality_rating' => $rating->punctuality_rating,
                'professionalism_rating' => $rating->professionalism_rating,
                'cleanliness_rating' => $rating->cleanliness_rating,
                'feedback' => $rating->feedback,
                'is_public' => $rating->is_public,
                'rated_at' => $rating->rated_at?->format('d.m.Y H:i'),
                'created_at' => $rating->created_at->format('d.m.Y H:i'),
            ],
        ]);
    }

    public function destroy(Rating $rating)
    {
        // Recalculate master/customer rating after delete
        $masterId = $rating->master_id;
        $customerId = $rating->customer_id;
        $type = $rating->type;

        $rating->delete();

        // Update cached ratings
        if ($type === 'client_to_master') {
            $avg = Rating::getMasterAverage($masterId);
            $count = Rating::getMasterRatingCount($masterId);
            Master::where('id', $masterId)->update([
                'rating' => $avg,
                'rating_count' => $count,
            ]);
        }

        return redirect()->route('admin.ratings.index')
            ->with('success', 'Baho o\'chirildi');
    }
}
