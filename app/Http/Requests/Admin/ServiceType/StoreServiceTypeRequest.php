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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'boolean',
            'en.name' => 'required|string|max:255',
            'en.description' => 'nullable|string',
            'uz.name' => 'required|string|max:255',
            'uz.description' => 'nullable|string',
            'ru.name' => 'required|string|max:255',
            'ru.description' => 'nullable|string',
            // Durations validation
            'durations' => 'required|array|min:1',
            'durations.*.duration' => 'required|integer|min:15|max:480',
            'durations.*.price' => 'required|numeric|min:0',
            'durations.*.is_default' => 'boolean',
            'durations.*.status' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'slug.required' => 'Slug majburiy',
            'slug.unique' => 'Bu slug oldin ishlatilgan',
            'en.name.required' => 'English nomi majburiy',
            'uz.name.required' => 'Uzbek nomi majburiy',
            'ru.name.required' => 'Russian nomi majburiy',
            'durations.required' => 'Kamida bitta davomiylik kiritilishi kerak',
            'durations.min' => 'Kamida bitta davomiylik kiritilishi kerak',
            'durations.*.duration.required' => 'Davomiylik kiritilishi shart',
            'durations.*.duration.min' => 'Davomiylik kamida 15 daqiqa bo\'lishi kerak',
            'durations.*.price.required' => 'Narx kiritilishi shart',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->has('status'),
        ]);
    }
}
