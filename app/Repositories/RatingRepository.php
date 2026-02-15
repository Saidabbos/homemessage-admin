<?php

namespace App\Repositories;

use App\Models\Rating;
use Illuminate\Database\Eloquent\Builder;

class RatingRepository extends BaseRepository
{
    protected function getModelClass(): string
    {
        return Rating::class;
    }

    /**
     * Get ratings received by a customer (master_to_client)
     */
    public function getReceivedByCustomer(int $customerId, array $filters = [], int $perPage = 10)
    {
        $query = $this->query()
            ->where('customer_id', $customerId)
            ->where('type', Rating::TYPE_MASTER_TO_CLIENT)
            ->whereNotNull('rated_at')
            ->with(['master', 'order.serviceType'])
            ->latest('rated_at');

        $this->applyRatingFilters($query, $filters);

        return $this->paginate($query, $perPage);
    }

    /**
     * Get ratings given by a customer (client_to_master)
     */
    public function getGivenByCustomer(int $customerId, array $filters = [], int $perPage = 10)
    {
        $query = $this->query()
            ->where('customer_id', $customerId)
            ->where('type', Rating::TYPE_CLIENT_TO_MASTER)
            ->whereNotNull('rated_at')
            ->with(['master', 'order.serviceType'])
            ->latest('rated_at');

        $this->applyRatingFilters($query, $filters);

        return $this->paginate($query, $perPage);
    }

    /**
     * Get ratings received by a master (client_to_master)
     */
    public function getReceivedByMaster(int $masterId, array $filters = [], int $perPage = 10)
    {
        $query = $this->query()
            ->where('master_id', $masterId)
            ->where('type', Rating::TYPE_CLIENT_TO_MASTER)
            ->whereNotNull('rated_at')
            ->with(['customer', 'order.serviceType'])
            ->latest('rated_at');

        $this->applyRatingFilters($query, $filters);

        return $this->paginate($query, $perPage);
    }

    /**
     * Get recent ratings for a customer
     */
    public function getRecentForCustomer(int $customerId, int $limit = 3)
    {
        return $this->query()
            ->where('customer_id', $customerId)
            ->where('type', Rating::TYPE_MASTER_TO_CLIENT)
            ->whereNotNull('rated_at')
            ->with('master')
            ->latest('rated_at')
            ->take($limit)
            ->get();
    }

    /**
     * Get recent ratings for a master
     */
    public function getRecentForMaster(int $masterId, int $limit = 5)
    {
        return $this->query()
            ->where('master_id', $masterId)
            ->where('type', Rating::TYPE_CLIENT_TO_MASTER)
            ->whereNotNull('rated_at')
            ->with('customer')
            ->latest('rated_at')
            ->take($limit)
            ->get();
    }

    protected function applyRatingFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['rating'])) {
            $query->where('overall_rating', $filters['rating']);
        }
    }
}
