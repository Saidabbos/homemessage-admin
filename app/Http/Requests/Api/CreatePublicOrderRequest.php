<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CreatePublicOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'service_type_id' => 'required|exists:service_types,id',
            'duration_id' => 'required|exists:service_type_durations,id',
            'master_id' => 'required|exists:masters,id',
            'date' => 'required|date',
            'arrival_window_start' => 'required|string',
            'people_count' => 'required|integer|min:1|max:5',
            'total_duration' => 'required|integer|min:30',
            'pressure_level' => 'required|in:soft,medium,hard,any',
            'notes' => 'nullable|string|max:1000',
            'services' => 'nullable|array',
            'services.*.service_type_id' => 'required_with:services|exists:service_types,id',
            'services.*.duration_id' => 'required_with:services|exists:service_type_durations,id',
        ];
    }
}
