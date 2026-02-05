<?php

namespace App\Repositories;

use App\Models\StandardItem;
use Illuminate\Pagination\LengthAwarePaginator;

class StandardItemRepository extends BaseRepository
{
    protected function getModelClass(): string
    {
        return StandardItem::class;
    }

    public function getFilteredPaginated(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->query();

        $this->applySearch($query, $filters['search'] ?? null, ['name', 'slug']);
        $this->applyStatusFilter($query, $filters['status'] ?? null);

        return $this->paginate($query->orderBy('sort_order'), $perPage);
    }
}
