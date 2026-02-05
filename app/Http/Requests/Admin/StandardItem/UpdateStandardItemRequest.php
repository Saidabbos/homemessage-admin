<?php

namespace App\Http\Requests\Admin\StandardItem;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStandardItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $itemId = $this->route('standard_item')->id;

        return [
            'slug' => 'required|alpha_dash|unique:standard_items,slug,' . $itemId,
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'integer|min:0',
            'status' => 'boolean',
            'en.name' => 'required|string|max:255',
            'en.description' => 'nullable|string',
            'uz.name' => 'required|string|max:255',
            'uz.description' => 'nullable|string',
            'ru.name' => 'required|string|max:255',
            'ru.description' => 'nullable|string',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->boolean('status'),
        ]);
    }
}
