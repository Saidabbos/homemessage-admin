<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Rating;
use App\Repositories\RatingRepository;

class RatingService
{
    public function __construct(
        protected RatingRepository $ratingRepository,
    ) {}

    /**
     * Get or create a client_to_master rating for a completed order.
     */
    public function getOrCreateForOrder(Order $order, int $customerId): Rating
    {
        $rating = Rating::where('order_id', $order->id)
            ->where('type', Rating::TYPE_CLIENT_TO_MASTER)
            ->first();

        if (!$rating) {
            $rating = Rating::create([
                'type' => Rating::TYPE_CLIENT_TO_MASTER,
                'order_id' => $order->id,
                'master_id' => $order->master_id,
                'customer_id' => $customerId,
            ]);
        }

        return $rating;
    }

    /**
     * Get or create a master_to_client rating for a completed order.
     */
    public function getOrCreateForMaster(Order $order, int $masterId): Rating
    {
        $rating = Rating::where('order_id', $order->id)
            ->where('type', Rating::TYPE_MASTER_TO_CLIENT)
            ->first();

        if (!$rating) {
            $rating = Rating::create([
                'type' => Rating::TYPE_MASTER_TO_CLIENT,
                'order_id' => $order->id,
                'master_id' => $masterId,
                'customer_id' => $order->customer_id,
            ]);
        }

        return $rating;
    }

    /**
     * Get rating summary for a customer (ratings received from masters).
     */
    public function getCustomerRatingSummary(int $customerId): array
    {
        $avg = Rating::getCustomerAverage($customerId);
        $count = Rating::getCustomerRatingCount($customerId);

        return [
            'average' => $avg ? round($avg, 1) : null,
            'count' => $count,
        ];
    }

    /**
     * Get rating summary for a master (ratings received from customers).
     */
    public function getMasterRatingSummary(int $masterId): array
    {
        $avg = Rating::getMasterAverage($masterId);
        $count = Rating::getMasterRatingCount($masterId);

        return [
            'average' => $avg ? round($avg, 1) : null,
            'count' => $count,
        ];
    }
}
