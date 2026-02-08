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
        $query = $this->query()->with('durations');

        $this->applySearch($query, $filters['search'] ?? null, ['name', 'slug']);
        $this->applyStatusFilter($query, $filters['status'] ?? null);

        return $this->paginate($query->latest(), $perPage);
    }

    public function getActive(): Collection
    {
        return $this->query()->where('status', true)->get();
    }

    /**
     * Get active service types with durations for Mini App
     */
    public function getActiveWithDurations(): Collection
    {
        return $this->query()
            ->where('status', true)
            ->with(['durations' => fn($q) => $q->where('status', true)->orderBy('sort_order')])
            ->orderBy('id')
            ->get();
    }

    /**
     * Get active service types for landing page
     */
    public function getActiveForLanding(int $limit = 4): SupportCollection
    {
        // Service type emoji icons mapping
        $icons = [
            1 => '💆', // Klassik
            2 => '🧘', // Relaks
            3 => '💪', // Sport
            4 => '🙏', // Tailand
            5 => '🪨', // Issiq tosh
            6 => '🔙', // Orqa va bo'yin
            7 => '🦶', // Oyoq
            8 => '✨', // Anti-sellyulit
        ];

        return $this->query()
            ->where('status', true)
            ->with('durations')
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->map(fn($service) => [
                'id' => $service->id,
                'name' => $service->name,
                'description' => $service->description,
                'price_range' => $service->price_range,
                'duration_list' => $service->duration_list,
                'default_duration' => $service->defaultDuration()?->duration,
                'icon' => $icons[$service->id] ?? '💆',
                'image' => $service->image,
            ]);
    }
}
