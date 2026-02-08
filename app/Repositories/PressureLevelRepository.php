<?php

namespace App\Repositories;

use App\Models\PressureLevel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PressureLevelRepository extends BaseRepository
{
    protected function getModelClass(): string
    {
        return PressureLevel::class;
    }

    public function getFilteredPaginated(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->query();

        $this->applySearch($query, $filters['search'] ?? null, ['name', 'slug']);
        $this->applyStatusFilter($query, $filters['status'] ?? null);

        return $this->paginate($query->ordered(), $perPage);
    }

    public function getActive(): Collection
    {
        return $this->query()->active()->ordered()->get();
    }
}
