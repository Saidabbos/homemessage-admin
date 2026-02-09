<?php

namespace App\Repositories;

use App\Models\Master;
use Illuminate\Pagination\LengthAwarePaginator;

class MasterRepository extends BaseRepository
{
    protected function getModelClass(): string
    {
        return Master::class;
    }

    public function getFilteredPaginated(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->query()->with('serviceTypes');

        $this->applySearch($query, $filters['search'] ?? null, [
            'first_name', 'last_name', 'phone', 'email'
        ]);

        $this->applyStatusFilter($query, $filters['status'] ?? null);

        if (!empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        if (!empty($filters['service_type'])) {
            $query->whereHas('serviceTypes', fn($q) => $q->where('service_types.id', $filters['service_type']));
        }

        return $this->paginate($query->latest(), $perPage);
    }

    /**
     * Get featured masters for landing page
     */
    public function getFeaturedForLanding(int $limit = 4): \Illuminate\Support\Collection
    {
        return $this->query()
            ->where('status', true)
            ->take($limit)
            ->get()
            ->map(fn($master) => [
                'id' => $master->id,
                'name' => $master->first_name . ' ' . $master->last_name,
                'bio' => $master->bio,
                'photo' => $master->photo_url,
                'rating' => $master->average_rating ?? 4.9,
            ]);
    }

    /**
     * Get all active masters (simple list with service types)
     */
    public function getActive(): \Illuminate\Support\Collection
    {
        return $this->query()
            ->where('status', true)
            ->with('serviceTypes:id')
            ->orderBy('first_name')
            ->get();
    }

    /**
     * Get all active masters with service types
     */
    public function getActiveWithDetails(): \Illuminate\Support\Collection
    {
        return $this->query()
            ->where('status', true)
            ->with(['serviceTypes'])
            ->withCount(['orders as completed_orders_count' => fn($q) => $q->where('status', 'COMPLETED')])
            ->orderBy('first_name')
            ->get()
            ->map(fn($master) => [
                'id' => $master->id,
                'first_name' => $master->first_name,
                'last_name' => $master->last_name,
                'full_name' => $master->full_name,
                'bio' => $master->bio,
                'photo_url' => $master->photo_url,
                'experience_years' => $master->experience_years,
                'completed_orders' => $master->completed_orders_count ?? 0,
                'service_types' => $master->serviceTypes->map(fn($st) => [
                    'id' => $st->id,
                    'name' => $st->getTranslation('name', app()->getLocale()),
                    'price' => (float) $st->price,
                ]),
            ]);
    }
}
