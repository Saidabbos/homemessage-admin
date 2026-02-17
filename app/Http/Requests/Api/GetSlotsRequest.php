<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class GetSlotsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'master_id' => 'required|exists:masters,id',
            'date' => 'required|date|after_or_equal:today',
            'duration' => 'sometimes|integer|min:30',
        ];
    }
}
