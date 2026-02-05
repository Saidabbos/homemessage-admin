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
}
