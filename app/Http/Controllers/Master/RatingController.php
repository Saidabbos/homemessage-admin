<?php

namespace App\Http\Controllers\Master;

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

    public function index(Request $request)
    {
        $user = Auth::user();
        $master = $user->master;

        if (!$master) {
            abort(404);
        }

        $locale = app()->getLocale();

        $ratings = Rating::where('master_id', $master->id)
            ->where('type', Rating::TYPE_CLIENT_TO_MASTER)
            ->whereNotNull('rated_at')
            ->with(['customer', 'order.serviceType'])
            ->latest('rated_at')
            ->paginate(10)
            ->withQueryString();

        $ratings->through(fn ($r) => [
            'id' => $r->id,
            'overall_rating' => $r->overall_rating,
            'punctuality_rating' => $r->punctuality_rating,
            'professionalism_rating' => $r->professionalism_rating,
            'cleanliness_rating' => $r->cleanliness_rating,
            'feedback' => $r->feedback,
            'rated_at' => $r->rated_at->format('d.m.Y'),
            'customer_name' => $r->customer?->name ?? 'Mijoz',
            'order_number' => $r->order?->order_number,
            'service_name' => $r->order?->serviceType?->getTranslation('name', $locale),
        ]);

        $summary = $this->ratingService->getMasterRatingSummary($master->id);

        return Inertia::render('Master/Ratings/Index', [
            'ratings' => $ratings,
            'summary' => $summary,
            'master' => [
                'id' => $master->id,
                'name' => $master->full_name,
                'phone' => $master->phone,
                'photo_url' => $master->photo_url,
            ],
        ]);
    }

    /**
     * Create a master_to_client rating and redirect to rating page.
     */
    public function createAndRedirect(Order $order)
    {
        $user = Auth::user();
        $master = $user->master;

        if (!$master || $order->master_id !== $master->id) {
            abort(403);
        }

        if (!$order->isCompleted()) {
            return back()->with('error', 'Faqat yakunlangan buyurtmalarni baholash mumkin');
        }

        $rating = $this->ratingService->getOrCreateForMaster($order, $master->id);

        return redirect()->route('rating.show', $rating->token);
    }
}
