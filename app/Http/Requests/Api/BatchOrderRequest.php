<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class BatchOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'orders' => 'required|array|min:1|max:10',
            'orders.*.service_type_id' => 'required|exists:service_types,id',
            'orders.*.duration_id' => 'required|exists:service_type_durations,id',
            'orders.*.master_id' => 'required|exists:masters,id',
            'orders.*.date' => 'required|date',
            'orders.*.arrival_window_start' => 'required|string',
            'orders.*.total_duration' => 'required|integer|min:30',
            'orders.*.pressure_level' => 'required|in:soft,medium,hard,any',
            'orders.*.notes' => 'nullable|string|max:1000',
            // Address fields (shared for all orders in batch)
            'address' => 'required|string|max:500',
            'entrance' => 'nullable|string|max:20',
            'floor' => 'nullable|string|max:20',
            'apartment' => 'nullable|string|max:20',
            'landmark' => 'nullable|string|max:255',
            'customer_address_id' => 'nullable|exists:customer_addresses,id',
        ];
    }
}
