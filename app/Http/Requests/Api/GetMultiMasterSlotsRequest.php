<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class GetMultiMasterSlotsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'master_ids' => 'required|string', // comma-separated
            'date' => 'required|date|after_or_equal:today',
            'duration' => 'sometimes|integer|min:30',
        ];
    }
}
