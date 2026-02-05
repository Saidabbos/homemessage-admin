<?php

namespace App\Http\Requests\Admin\Slot;

use Illuminate\Foundation\Http\FormRequest;

class StoreSlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'sort_order' => 'nullable|integer|min:0|max:255',
            'is_active' => 'boolean',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
        ]);
    }

    public function messages(): array
    {
        return [
            'start_time.required' => 'Boshlanish vaqti kiritilishi shart',
            'end_time.required' => 'Tugash vaqti kiritilishi shart',
            'end_time.after' => 'Tugash vaqti boshlanish vaqtidan keyin bo\'lishi kerak',
        ];
    }
}
