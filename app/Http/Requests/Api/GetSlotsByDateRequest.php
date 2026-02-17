<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class GetSlotsByDateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'master_id' => 'required|exists:masters,id',
            'start_date' => 'required|date|after_or_equal:today',
            'days' => 'sometimes|integer|min:1|max:14',
            'duration' => 'sometimes|integer|min:30',
        ];
    }
}
