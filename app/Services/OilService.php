<?php

namespace App\Services;

use App\Models\Oil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        Log::info('OilService: Creating new oil', ['slug' => $data['slug'] ?? 'unknown']);

        $data = $this->prepareTranslations($data);

        if ($request->hasFile('image')) {
            $data['image'] = $this->imageService->upload($request->file('image'), 'oils');
            Log::info('OilService: Image uploaded');
        }

        $data['status'] = $request->has('status');

        $oil = Oil::create($data);
        Log::info('OilService: Oil created successfully', ['id' => $oil->id]);

        return $oil;
    }

    /**
     * Update an existing oil
     */
    public function update(Oil $oil, array $data, Request $request): Oil
    {
        Log::info('OilService: Updating oil', ['id' => $oil->id]);

        $data = $this->prepareTranslations($data);

        if ($request->hasFile('image')) {
            $data['image'] = $this->imageService->replace(
                $oil->image,
                $request->file('image'),
                'oils'
            );
            Log::info('OilService: Image updated', ['id' => $oil->id]);
        }

        $data['status'] = $request->has('status');

        $oil->update($data);
        Log::info('OilService: Oil updated successfully', ['id' => $oil->id]);

        return $oil;
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
     * Delete an oil
     */
    public function delete(Oil $oil): void
    {
        Log::info('OilService: Deleting oil', ['id' => $oil->id]);

        $this->imageService->delete($oil->image);
        $oil->delete();

        Log::info('OilService: Oil deleted successfully', ['id' => $oil->id]);
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
