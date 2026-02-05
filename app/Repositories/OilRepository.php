<?php

namespace App\Repositories;

use App\Models\Oil;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class OilRepository extends BaseRepository
{
    protected function getModelClass(): string
    {
        return Oil::class;
    }

    public function getFilteredPaginated(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->query();

        $this->applySearch($query, $filters['search'] ?? null, ['name', 'slug']);
        $this->applyStatusFilter($query, $filters['status'] ?? null);

        return $this->paginate($query->latest(), $perPage);
    }

    public function getActive(): Collection
    {
        return $this->query()->where('status', true)->get();
    }
}
