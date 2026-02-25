<?php

namespace App\Http\Requests\Admin\Setting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // General
            'app_name' => 'nullable|string|max:255',
            'company_phone' => 'nullable|string|max:20',
            'company_email' => 'nullable|email|max:255',
            'company_address' => 'nullable|string|max:500',
            'working_hours_start' => 'nullable|string|max:10',
            'working_hours_end' => 'nullable|string|max:10',

            // Booking
            'min_booking_hours' => 'nullable|integer|min:1|max:72',
            'max_booking_days' => 'nullable|integer|min:1|max:90',
            'cancellation_hours' => 'nullable|integer|min:0|max:48',
            'auto_confirm_booking' => 'nullable|boolean',
            
            // Gender-based booking restrictions
            'gender_time_restriction_enabled' => 'nullable|boolean',
            'male_booking_cutoff_hour' => 'nullable|integer|min:0|max:23',
            'female_booking_cutoff_hour' => 'nullable|integer|min:0|max:23',

            // Social
            'telegram_link' => 'nullable|url|max:255',
            'instagram_link' => 'nullable|url|max:255',
            'facebook_link' => 'nullable|url|max:255',

            // Hero Section - Translatable (nested under language keys)
            'uz.hero_title' => 'nullable|string|max:255',
            'ru.hero_title' => 'nullable|string|max:255',
            'en.hero_title' => 'nullable|string|max:255',
            'uz.hero_subtitle' => 'nullable|string|max:500',
            'ru.hero_subtitle' => 'nullable|string|max:500',
            'en.hero_subtitle' => 'nullable|string|max:500',
            'hero_badge' => 'nullable|string|max:100',
            'hero_cta_text' => 'nullable|string|max:100',
            'hero_view_services_text' => 'nullable|string|max:100',
            'hero_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ];
    }
}
