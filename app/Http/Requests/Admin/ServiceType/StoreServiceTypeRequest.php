<?php

namespace App\Http\Requests\Admin\ServiceType;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'slug' => 'required|unique:service_types|alpha_dash',
            'duration' => 'required|integer|min:15|max:480',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'boolean',
            'en.name' => 'required|string|max:255',
            'en.description' => 'nullable|string',
            'uz.name' => 'required|string|max:255',
            'uz.description' => 'nullable|string',
            'ru.name' => 'required|string|max:255',
            'ru.description' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'slug.required' => 'Slug majburiy',
            'slug.unique' => 'Bu slug oldin ishlatilgan',
            'duration.required' => 'Davomiyligi majburiy',
            'price.required' => 'Narx majburiy',
            'en.name.required' => 'English nomi majburiy',
            'uz.name.required' => 'Uzbek nomi majburiy',
            'ru.name.required' => 'Russian nomi majburiy',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->has('status'),
        ]);
    }
}
