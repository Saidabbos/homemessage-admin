<?php

namespace App\Http\Requests\Admin\Oil;

use Illuminate\Foundation\Http\FormRequest;

class StoreOilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'slug' => 'required|unique:oils|alpha_dash',
            'price' => 'nullable|numeric|min:0',
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
