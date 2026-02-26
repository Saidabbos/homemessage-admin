<?php

namespace App\Services;

use App\Models\ServiceType;
use App\Models\ServiceTypeDuration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ServiceTypeService
{
    public function __construct(
        protected ImageService $imageService
    ) {}

    /**
     * Create a new service type with durations
     */
    public function create(array $data, Request $request): ServiceType
    {
        Log::info('ServiceTypeService: Creating new service type', ['slug' => $data['slug'] ?? 'unknown']);

        return DB::transaction(function () use ($data, $request) {
            if ($request->hasFile('image')) {
                $data['image'] = $this->imageService->upload($request->file('image'), 'service-types');
                Log::info('ServiceTypeService: Image uploaded');
            }

            $data = $this->prepareTranslations($data);
            $data['status'] = $request->has('status');

            // Remove durations from main data
            $durations = $data['durations'] ?? [];
            unset($data['durations']);

            $serviceType = ServiceType::create($data);
            Log::info('ServiceTypeService: Service type record created', ['id' => $serviceType->id]);

            // Create durations
            $this->syncDurations($serviceType, $durations);
            Log::info('ServiceTypeService: Durations synced', ['count' => count($durations)]);

            Log::info('ServiceTypeService: Service type created successfully', ['id' => $serviceType->id]);
            return $serviceType;
        });
    }

    /**
     * Update an existing service type with durations
     */
    public function update(ServiceType $serviceType, array $data, Request $request): ServiceType
    {
        Log::info('ServiceTypeService: Updating service type', ['id' => $serviceType->id]);

        return DB::transaction(function () use ($serviceType, $data, $request) {
            $data = $this->prepareTranslations($data);

            if ($request->hasFile('image')) {
                $data['image'] = $this->imageService->replace(
                    $serviceType->image,
                    $request->file('image'),
                    'service-types'
                );
                Log::info('ServiceTypeService: Image updated', ['id' => $serviceType->id]);
            }

            $data['status'] = $request->has('status');

            // Remove durations from main data
            $durations = $data['durations'] ?? [];
            unset($data['durations']);

            $serviceType->update($data);

            // Sync durations
            $this->syncDurations($serviceType, $durations);
            Log::info('ServiceTypeService: Durations synced', ['id' => $serviceType->id, 'count' => count($durations)]);

            Log::info('ServiceTypeService: Service type updated successfully', ['id' => $serviceType->id]);
            return $serviceType;
        });
    }

    /**
     * Sync durations for a service type
     */
    protected function syncDurations(ServiceType $serviceType, array $durations): void
    {
        // Get existing duration IDs
        $existingIds = $serviceType->durations()->pluck('id')->toArray();
        $incomingIds = [];

        foreach ($durations as $index => $durationData) {
            if (isset($durationData['id']) && $durationData['id']) {
                // Update existing
                $duration = ServiceTypeDuration::find($durationData['id']);
                if ($duration && $duration->service_type_id === $serviceType->id) {
                    $duration->update([
                        'duration' => $durationData['duration'],
                        'price' => $durationData['price'],
                        'is_default' => $durationData['is_default'] ?? false,
                        'status' => $durationData['status'] ?? true,
                        'sort_order' => $index,
                    ]);
                    $incomingIds[] = $duration->id;
                }
            } else {
                // Create new
                $duration = $serviceType->durations()->create([
                    'duration' => $durationData['duration'],
                    'price' => $durationData['price'],
                    'is_default' => $durationData['is_default'] ?? false,
                    'status' => $durationData['status'] ?? true,
                    'sort_order' => $index,
                ]);
                $incomingIds[] = $duration->id;
            }
        }

        // Delete removed durations
        $toDelete = array_diff($existingIds, $incomingIds);
        if (!empty($toDelete)) {
            ServiceTypeDuration::whereIn('id', $toDelete)->delete();
            Log::info('ServiceTypeService: Deleted removed durations', ['deleted' => count($toDelete)]);
        }

        // Ensure at least one is_default
        if (!$serviceType->durations()->where('is_default', true)->exists()) {
            $serviceType->durations()->orderBy('sort_order')->first()?->update(['is_default' => true]);
        }
    }

    /**
     * Transform locale-keyed translations to field-keyed format for Spatie
     */
    private function prepareTranslations(array $data): array
    {
        foreach (['en', 'uz', 'ru'] as $locale) {
            if (isset($data[$locale])) {
                foreach ($data[$locale] as $field => $value) {
                    $data[$field][$locale] = $value;
                }
                unset($data[$locale]);
            }
        }

        return $data;
    }

    /**
     * Delete a service type
     */
    public function delete(ServiceType $serviceType): void
    {
        Log::info('ServiceTypeService: Deleting service type', ['id' => $serviceType->id]);

        $this->imageService->delete($serviceType->image);
        $serviceType->delete(); // Cascade deletes durations

        Log::info('ServiceTypeService: Service type deleted successfully', ['id' => $serviceType->id]);
    }

    /**
     * Get service type data formatted for edit form
     */
    public function getEditData(ServiceType $serviceType): array
    {
        $serviceType->load('durations');

        return [
            'id' => $serviceType->id,
            'slug' => $serviceType->slug,
            'image' => $serviceType->image,
            'image_url' => $serviceType->image_url,
            'status' => $serviceType->status,
            'created_at' => $serviceType->created_at,
            'updated_at' => $serviceType->updated_at,
            'en' => [
                'name' => $serviceType->getTranslation('name', 'en'),
                'description' => $serviceType->getTranslation('description', 'en'),
            ],
            'uz' => [
                'name' => $serviceType->getTranslation('name', 'uz'),
                'description' => $serviceType->getTranslation('description', 'uz'),
            ],
            'ru' => [
                'name' => $serviceType->getTranslation('name', 'ru'),
                'description' => $serviceType->getTranslation('description', 'ru'),
            ],
            'durations' => $serviceType->durations->map(fn ($d) => [
                'id' => $d->id,
                'duration' => $d->duration,
                'price' => (int) $d->price,
                'is_default' => $d->is_default,
                'status' => $d->status,
            ])->toArray(),
        ];
    }
}
