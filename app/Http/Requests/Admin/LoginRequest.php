<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email majburiy',
            'email.email' => 'Email noto\'g\'ri',
            'password.required' => 'Parol majburiy',
            'password.min' => 'Parol kamida 6 ta belgidan iborat bo\'lishi kerak',
        ];
    }
}
