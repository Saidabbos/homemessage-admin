<?php

namespace App\Http\Requests\Public\Auth;

use Illuminate\Foundation\Http\FormRequest;

class SendOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => [
                'required',
                'string',
                'regex:/^\+998[0-9]{9}$/', // Uzbekistan phone format
            ],
            'locale' => [
                'nullable',
                'string',
                'in:uz,ru,en',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => __('auth.otp.phone_required'),
            'phone.regex' => __('auth.otp.phone_invalid'),
        ];
    }
}
