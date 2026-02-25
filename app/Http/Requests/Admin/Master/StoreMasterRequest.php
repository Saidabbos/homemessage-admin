<?php

namespace App\Http\Requests\Admin\Master;

use Illuminate\Foundation\Http\FormRequest;

class StoreMasterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|unique:masters|unique:users,phone',
            'email' => 'required|email|unique:masters|unique:users,email',
            'password' => 'required|string|min:6',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'birth_date' => 'nullable|date',
            'gender' => 'required|in:male,female',
            'experience_years' => 'required|integer|min:0|max:50',
            'status' => 'boolean',
            'service_types' => 'nullable|array',
            'service_types.*' => 'exists:service_types,id',
            'oils' => 'nullable|array',
            'oils.*' => 'exists:oils,id',
            'pressure_levels' => 'nullable|array',
            'pressure_levels.*' => 'exists:pressure_levels,id',
            'uz.bio' => 'nullable|string',
            'ru.bio' => 'nullable|string',
            'en.bio' => 'nullable|string',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->boolean('status'),
        ]);
    }
}
