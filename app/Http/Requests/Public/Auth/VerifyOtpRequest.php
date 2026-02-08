<?php

namespace App\Http\Requests\Public\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
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
            'code' => [
                'required',
                'string',
                'size:6',
                'regex:/^[0-9]{6}$/', // 6 digits
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => __('auth.otp.phone_required'),
            'phone.regex' => __('auth.otp.phone_invalid'),
            'code.required' => __('auth.otp.code_required'),
            'code.size' => __('auth.otp.code_invalid'),
            'code.regex' => __('auth.otp.code_invalid'),
        ];
    }
}
