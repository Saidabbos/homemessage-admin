<?php

namespace App\Http\Requests\Admin\ServiceType;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $serviceTypeId = $this->route('service_type')->id;

        return [
            'slug' => 'required|alpha_dash|unique:service_types,slug,' . $serviceTypeId,
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
            'durations.*.id' => 'nullable|integer|exists:service_type_durations,id',
            'durations.*.duration' => 'required|integer|min:15|max:480',
            'durations.*.price' => 'required|numeric|min:0',
            'durations.*.is_default' => 'boolean',
            'durations.*.status' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'durations.required' => 'Kamida bitta davomiylik kiritilishi kerak',
            'durations.min' => 'Kamida bitta davomiylik kiritilishi kerak',
            'durations.*.duration.required' => 'Davomiylik kiritilishi shart',
            'durations.*.duration.min' => 'Davomiylik kamida 15 daqiqa bo\'lishi kerak',
            'durations.*.price.required' => 'Narx kiritilishi shart',
            'durations.*.price.min' => 'Narx 0 dan katta bo\'lishi kerak',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->has('status'),
        ]);
    }
}
