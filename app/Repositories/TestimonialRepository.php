<?php

namespace App\Repositories;

use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TestimonialRepository extends BaseRepository
{
    protected function getModelClass(): string
    {
        return Testimonial::class;
    }

    public function getFilteredPaginated(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->query();

        $this->applySearch($query, $filters['search'] ?? null, ['client_name']);
        $this->applyStatusFilter($query, $filters['status'] ?? null);

        return $this->paginate($query->orderBy('sort_order')->latest(), $perPage);
    }

    public function getActiveForLanding(int $limit = 3): Collection
    {
        return $this->query()
            ->where('status', true)
            ->orderBy('sort_order')
            ->limit($limit)
            ->get();
    }
}
