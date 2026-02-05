<?php

namespace App\Services;

use App\Models\ServiceType;
use Illuminate\Http\Request;

class ServiceTypeService
{
    public function __construct(
        protected ImageService $imageService
    ) {}

    /**
     * Create a new service type
     */
    public function create(array $data, Request $request): ServiceType
    {
        if ($request->hasFile('image')) {
            $data['image'] = $this->imageService->upload($request->file('image'), 'service-types');
        }

        $data['status'] = $request->has('status');

        return ServiceType::create($data);
    }

    /**
     * Update an existing service type
     */
    public function update(ServiceType $serviceType, array $data, Request $request): ServiceType
    {
        if ($request->hasFile('image')) {
            $data['image'] = $this->imageService->replace(
                $serviceType->image,
                $request->file('image'),
                'service-types'
            );
        }

        $data['status'] = $request->has('status');

        $serviceType->update($data);

        return $serviceType;
    }

    /**
     * Delete a service type
     */
    public function delete(ServiceType $serviceType): void
    {
        $this->imageService->delete($serviceType->image);
        $serviceType->delete();
    }

    /**
     * Get service type data formatted for edit form
     */
    public function getEditData(ServiceType $serviceType): array
    {
        return [
            'id' => $serviceType->id,
            'slug' => $serviceType->slug,
            'duration' => $serviceType->duration,
            'price' => $serviceType->price,
            'image' => $serviceType->image,
            'image_url' => $serviceType->image_url,
            'status' => $serviceType->status,
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
        ];
    }
}
