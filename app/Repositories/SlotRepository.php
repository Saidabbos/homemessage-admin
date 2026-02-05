<?php

namespace App\Repositories;

use App\Models\Slot;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SlotRepository extends BaseRepository
{
    protected function getModelClass(): string
    {
        return Slot::class;
    }

    public function getFilteredPaginated(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('start_time', 'like', "%{$search}%")
                  ->orWhere('end_time', 'like', "%{$search}%");
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('is_active', $filters['status'] === 'active');
        }

        return $this->paginate($query->ordered(), $perPage);
    }

    public function getActive(): Collection
    {
        return $this->query()
            ->active()
            ->ordered()
            ->get();
    }

    public function getAll(): Collection
    {
        return $this->query()
            ->ordered()
            ->get();
    }
}
