<?php

namespace App\Http\Requests\Admin\Order;

use Illuminate\Foundation\Http\FormRequest;

class RescheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'booking_date' => 'required|date',
            'arrival_window_start' => 'required|date_format:H:i',
            'arrival_window_end' => 'required|date_format:H:i|after:arrival_window_start',
            'comment' => 'nullable|string|max:500',
        ];
    }
}
