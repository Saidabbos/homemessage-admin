<?php

namespace App\Http\Requests\Admin\PressureLevel;

use Illuminate\Foundation\Http\FormRequest;

class StorePressureLevelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'slug' => 'required|string|unique:pressure_levels',
            'uz.name' => 'required|string',
            'ru.name' => 'required|string',
            'en.name' => 'required|string',
            'uz.description' => 'nullable|string',
            'ru.description' => 'nullable|string',
            'en.description' => 'nullable|string',
            'sort_order' => 'integer|min:0',
            'status' => 'boolean',
        ];
    }
}
