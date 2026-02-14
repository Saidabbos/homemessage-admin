<?php

namespace App\Http\Requests\Admin\Testimonial;

use Illuminate\Foundation\Http\FormRequest;

class StoreTestimonialRequest extends FormRequest
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

    public function messages(): array
    {
        return [
            'rating.required' => 'Baho majburiy',
            'rating.min' => 'Baho kamida 1 bo\'lishi kerak',
            'rating.max' => 'Baho ko\'pi bilan 5 bo\'lishi kerak',
            'uz.client_name.required' => 'Mijoz ismi (uz) majburiy',
            'uz.comment.required' => 'Fikr (uz) majburiy',
            'ru.client_name.required' => 'Mijoz ismi (ru) majburiy',
            'ru.comment.required' => 'Fikr (ru) majburiy',
            'en.client_name.required' => 'Mijoz ismi (en) majburiy',
            'en.comment.required' => 'Fikr (en) majburiy',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->has('status'),
        ]);
    }
}
