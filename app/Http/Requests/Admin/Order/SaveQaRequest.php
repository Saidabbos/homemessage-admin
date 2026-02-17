<?php

namespace App\Http\Requests\Admin\Order;

use Illuminate\Foundation\Http\FormRequest;

class SaveQaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'qa_overall_rating' => 'required|integer|min:1|max:5',
            'qa_punctuality_rating' => 'required|integer|min:1|max:5',
            'qa_professionalism_rating' => 'required|integer|min:1|max:5',
            'qa_feedback' => 'nullable|string|max:1000',
        ];
    }
}
