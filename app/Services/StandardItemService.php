<?php

namespace App\Services;

use App\Models\StandardItem;
use Illuminate\Http\Request;

class StandardItemService
{
    /**
     * Create a new standard item
     */
    public function create(array $data, Request $request): StandardItem
    {
        $data = $this->prepareTranslations($data);
        $data['status'] = $request->boolean('status');
        $data['sort_order'] = $request->input('sort_order', 0);

        return StandardItem::create($data);
    }

    /**
     * Update an existing standard item
     */
    public function update(StandardItem $item, array $data, Request $request): StandardItem
    {
        $data = $this->prepareTranslations($data);
        $data['status'] = $request->boolean('status');

        $item->update($data);

        return $item;
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
     * Delete a standard item
     */
    public function delete(StandardItem $item): void
    {
        $item->delete();
    }

    /**
     * Get standard item data formatted for edit form
     */
    public function getEditData(StandardItem $item): array
    {
        return [
            'id' => $item->id,
            'slug' => $item->slug,
            'icon' => $item->icon,
            'sort_order' => $item->sort_order,
            'status' => $item->status,
            'en' => [
                'name' => $item->getTranslation('name', 'en'),
                'description' => $item->getTranslation('description', 'en'),
            ],
            'uz' => [
                'name' => $item->getTranslation('name', 'uz'),
                'description' => $item->getTranslation('description', 'uz'),
            ],
            'ru' => [
                'name' => $item->getTranslation('name', 'ru'),
                'description' => $item->getTranslation('description', 'ru'),
            ],
        ];
    }
}
