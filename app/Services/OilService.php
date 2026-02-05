<?php

namespace App\Services;

use App\Models\Oil;
use Illuminate\Http\Request;

class OilService
{
    public function __construct(
        protected ImageService $imageService
    ) {}

    /**
     * Create a new oil
     */
    public function create(array $data, Request $request): Oil
    {
        if ($request->hasFile('image')) {
            $data['image'] = $this->imageService->upload($request->file('image'), 'oils');
        }

        $data['status'] = $request->has('status');

        return Oil::create($data);
    }

    /**
     * Update an existing oil
     */
    public function update(Oil $oil, array $data, Request $request): Oil
    {
        if ($request->hasFile('image')) {
            $data['image'] = $this->imageService->replace(
                $oil->image,
                $request->file('image'),
                'oils'
            );
        }

        $data['status'] = $request->has('status');

        $oil->update($data);

        return $oil;
    }

    /**
     * Delete an oil
     */
    public function delete(Oil $oil): void
    {
        $this->imageService->delete($oil->image);
        $oil->delete();
    }

    /**
     * Get oil data formatted for edit form
     */
    public function getEditData(Oil $oil): array
    {
        return [
            'id' => $oil->id,
            'slug' => $oil->slug,
            'price' => $oil->price,
            'image' => $oil->image,
            'image_url' => $oil->image_url,
            'status' => $oil->status,
            'en' => [
                'name' => $oil->getTranslation('name', 'en'),
                'description' => $oil->getTranslation('description', 'en'),
            ],
            'uz' => [
                'name' => $oil->getTranslation('name', 'uz'),
                'description' => $oil->getTranslation('description', 'uz'),
            ],
            'ru' => [
                'name' => $oil->getTranslation('name', 'ru'),
                'description' => $oil->getTranslation('description', 'ru'),
            ],
        ];
    }
}
