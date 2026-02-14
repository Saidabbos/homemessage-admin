<?php

namespace App\Http\Requests\Admin\Testimonial;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTestimonialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rating' => 'required|integer|min:1|max:5',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
            'uz.client_name' => 'required|string|max:255',
            'uz.client_role' => 'nullable|string|max:255',
            'uz.comment' => 'required|string|max:1000',
            'ru.client_name' => 'required|string|max:255',
            'ru.client_role' => 'nullable|string|max:255',
            'ru.comment' => 'required|string|max:1000',
            'en.client_name' => 'required|string|max:255',
            'en.client_role' => 'nullable|string|max:255',
            'en.comment' => 'required|string|max:1000',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->has('status'),
        ]);
    }
}
