<?php

namespace App\Http\Requests\Admin\Order;

use Illuminate\Foundation\Http\FormRequest;

class SaveConfirmationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'call_outcome' => 'required|in:pending,confirmed,reschedule,no_answer,cancelled',
            'conf_entrance' => 'nullable|string|max:50',
            'conf_floor' => 'nullable|string|max:20',
            'conf_elevator' => 'boolean',
            'conf_parking' => 'nullable|string|max:200',
            'conf_landmark' => 'nullable|string|max:200',
            'conf_onsite_phone' => 'nullable|string|max:20',
            'conf_constraints' => 'nullable|string|max:500',
            'conf_space_ok' => 'boolean',
            'conf_pets' => 'boolean',
            'conf_note_to_master' => 'nullable|string|max:1000',
        ];
    }
}
