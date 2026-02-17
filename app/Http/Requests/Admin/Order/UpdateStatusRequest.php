<?php

namespace App\Http\Requests\Admin\Order;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:' . implode(',', [
                Order::STATUS_NEW,
                Order::STATUS_CONFIRMING,
                Order::STATUS_CONFIRMED,
                Order::STATUS_IN_PROGRESS,
                Order::STATUS_COMPLETED,
                Order::STATUS_CANCELLED,
            ]),
            'comment' => 'nullable|string|max:500',
        ];
    }
}
