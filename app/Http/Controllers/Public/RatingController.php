<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreRatingRequest;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class RatingController extends Controller
{
    /**
     * Show rating form (works for both types)
     */
    public function show(string $token)
    {
        $rating = Rating::where('token', $token)
            ->with(['order.serviceType', 'master', 'customer'])
            ->firstOrFail();

        // Already rated
        if ($rating->isCompleted()) {
            return $this->complete($token);
        }

        // Client rating master
        if ($rating->isClientToMaster()) {
            return Inertia::render('Public/Rating', [
                'rating' => $rating,
                'order' => $rating->order,
                'master' => $rating->master,
                'type' => 'client_to_master',
            ]);
        }

        // Master rating client
        return Inertia::render('Public/RatingClient', [
            'rating' => $rating,
            'order' => $rating->order,
            'customer' => $rating->customer,
            'type' => 'master_to_client',
        ]);
    }

    /**
     * Submit rating
     */
    public function store(StoreRatingRequest $request, string $token)
    {
        $rating = Rating::where('token', $token)->firstOrFail();

        // Already rated
        if ($rating->isCompleted()) {
            return back()->with('error', 'Bu buyurtma allaqachon baholangan');
        }

        $rating->update([
            ...$request->validated(),
            'rated_at' => Carbon::now(),
        ]);

        // Update ratings based on type
        if ($rating->isClientToMaster()) {
            $this->updateMasterRating($rating->master_id);
        } else {
            $this->updateCustomerRating($rating->customer_id);
        }

        return redirect()->route('rating.complete', $token);
    }

    /**
     * Show completion page
     */
    public function complete(string $token)
    {
        $rating = Rating::where('token', $token)
            ->with(['master', 'customer', 'order.serviceType'])
            ->firstOrFail();

        $locale = app()->getLocale();

        $orderData = $rating->order ? [
            'order_number' => $rating->order->order_number,
            'service_name' => $rating->order->serviceType?->getTranslation('name', $locale) ?? '-',
            'booking_date' => $rating->order->booking_date?->format('d.m.Y'),
        ] : null;

        if ($rating->isClientToMaster()) {
            return Inertia::render('Public/RatingComplete', [
                'rating' => $rating,
                'master' => $rating->master,
                'order' => $orderData,
                'type' => 'client_to_master',
            ]);
        }

        return Inertia::render('Public/RatingClientComplete', [
            'rating' => $rating,
            'customer' => $rating->customer,
            'order' => $orderData,
            'type' => 'master_to_client',
        ]);
    }

    /**
     * Update master's cached average rating
     */
    protected function updateMasterRating(int $masterId): void
    {
        $average = Rating::getMasterAverage($masterId);
        $count = Rating::getMasterRatingCount($masterId);

        \App\Models\Master::where('id', $masterId)->update([
            'rating' => $average,
            'rating_count' => $count,
        ]);
    }

    /**
     * Update customer's cached average rating
     */
    protected function updateCustomerRating(int $customerId): void
    {
        $average = Rating::getCustomerAverage($customerId);
        $count = Rating::getCustomerRatingCount($customerId);

        User::where('id', $customerId)->update([
            'rating' => $average,
            'rating_count' => $count,
        ]);
    }
}
