<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Rating;
use App\Services\RatingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class RatingController extends Controller
{
    public function __construct(
        protected RatingService $ratingService,
    ) {}

    /**
     * Display customer ratings with tabs (received/given).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $tab = $request->input('tab', 'received');
        $locale = app()->getLocale();

        if ($tab === 'given') {
            $ratings = Rating::where('customer_id', $user->id)
                ->where('type', Rating::TYPE_CLIENT_TO_MASTER)
                ->whereNotNull('rated_at')
                ->with(['master', 'order.serviceType'])
                ->latest('rated_at')
                ->paginate(10)
                ->withQueryString();
        } else {
            $ratings = Rating::where('customer_id', $user->id)
                ->where('type', Rating::TYPE_MASTER_TO_CLIENT)
                ->whereNotNull('rated_at')
                ->with(['master', 'order.serviceType'])
                ->latest('rated_at')
                ->paginate(10)
                ->withQueryString();
        }

        $ratings->through(fn ($r) => [
            'id' => $r->id,
            'type' => $r->type,
            'overall_rating' => $r->overall_rating,
            'punctuality_rating' => $r->punctuality_rating,
            'professionalism_rating' => $r->professionalism_rating,
            'cleanliness_rating' => $r->cleanliness_rating,
            'feedback' => $r->feedback,
            'rated_at' => $r->rated_at->format('d.m.Y'),
            'master_name' => $r->master?->full_name,
            'master_photo' => $r->master?->photo_url,
            'order_number' => $r->order?->order_number,
            'service_name' => $r->order?->serviceType?->getTranslation('name', $locale),
        ]);

        $summary = $this->ratingService->getCustomerRatingSummary($user->id);

        // Count given ratings
        $givenCount = Rating::where('customer_id', $user->id)
            ->where('type', Rating::TYPE_CLIENT_TO_MASTER)
            ->whereNotNull('rated_at')
            ->count();

        $receivedCount = Rating::where('customer_id', $user->id)
            ->where('type', Rating::TYPE_MASTER_TO_CLIENT)
            ->whereNotNull('rated_at')
            ->count();

        return Inertia::render('Customer/Ratings/Index', [
            'ratings' => $ratings,
            'tab' => $tab,
            'summary' => $summary,
            'counts' => [
                'received' => $receivedCount,
                'given' => $givenCount,
            ],
            'customer' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
            ],
        ]);
    }

    /**
     * Create a rating record for a completed order and redirect to rating page.
     */
    public function createAndRedirect(Order $order)
    {
        $user = Auth::user();

        if ($order->customer_id !== $user->id) {
            abort(403);
        }

        if (!$order->isCompleted()) {
            return back()->with('error', 'Faqat yakunlangan buyurtmalarni baholash mumkin');
        }

        $rating = $this->ratingService->getOrCreateForOrder($order, $user->id);

        return redirect()->route('rating.show', $rating->token);
    }
}
