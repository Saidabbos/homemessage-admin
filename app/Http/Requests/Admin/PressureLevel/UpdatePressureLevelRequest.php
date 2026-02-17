<?php

namespace App\Http\Requests\Admin\PressureLevel;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePressureLevelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $pressureLevelId = $this->route('pressure_level')?->id ?? $this->route('pressureLevel')?->id;

        return [
            'slug' => "required|string|unique:pressure_levels,slug,{$pressureLevelId}",
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
