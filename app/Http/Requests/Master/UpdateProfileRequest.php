<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->master;
    }

    public function rules(): array
    {
        $masterId = Auth::user()->master?->id;

        return [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'nullable|email|max:255|unique:masters,email,' . $masterId,
            'bio_uz' => 'nullable|string|max:1000',
            'bio_ru' => 'nullable|string|max:1000',
            'locale' => 'nullable|in:uz,ru,en',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }
}
