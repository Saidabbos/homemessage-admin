<?php

namespace App\Repositories;

use App\Models\ServiceType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class ServiceTypeRepository extends BaseRepository
{
    protected function getModelClass(): string
    {
        return ServiceType::class;
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

    /**
     * Get active service types for landing page
     */
    public function getActiveForLanding(int $limit = 4): SupportCollection
    {
        return $this->query()
            ->where('status', true)
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->map(fn($service) => [
                'id' => $service->id,
                'name' => $service->name,
                'description' => $service->description,
                'price' => $service->price,
                'duration' => $service->duration,
                'icon' => $service->icon ?? 'spa',
            ]);
    }
}
