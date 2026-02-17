<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CreateMiniAppOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $hasMasterIds = $this->has('master_ids') && is_array($this->master_ids) && count($this->master_ids) > 0;

        return [
            'master_id' => $hasMasterIds ? 'nullable' : 'required|exists:masters,id',
            'master_ids' => 'nullable|array|min:1|max:5',
            'master_ids.*' => 'exists:masters,id',
            'service_type_id' => 'required|exists:service_types,id',
            'duration_id' => 'required|exists:service_type_durations,id',
            'date' => 'required|date|after_or_equal:today',
            'arrival_window_start' => 'required|date_format:H:i',
            'people_count' => 'integer|min:1|max:5',
            'pressure_level' => 'in:light,medium,strong,any',
            'notes' => 'nullable|string|max:500',
        ];
    }
}
