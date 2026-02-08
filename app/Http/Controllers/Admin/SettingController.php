<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Setting\UpdateSettingRequest;
use App\Models\Setting;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Settings/Index', [
            'settings' => Setting::getAllGrouped(),
        ]);
    }

    public function update(UpdateSettingRequest $request)
    {
        $validated = $request->validated();

        // General settings
        Setting::set('app_name', $validated['app_name'] ?? '', 'general', 'text');
        Setting::set('company_phone', $validated['company_phone'] ?? '', 'general', 'text');
        Setting::set('company_email', $validated['company_email'] ?? '', 'general', 'text');
        Setting::set('company_address', $validated['company_address'] ?? '', 'general', 'text');
        Setting::set('working_hours_start', $validated['working_hours_start'] ?? '09:00', 'general', 'text');
        Setting::set('working_hours_end', $validated['working_hours_end'] ?? '21:00', 'general', 'text');

        // Booking settings
        Setting::set('min_booking_hours', $validated['min_booking_hours'] ?? 2, 'booking', 'number');
        Setting::set('max_booking_days', $validated['max_booking_days'] ?? 30, 'booking', 'number');
        Setting::set('cancellation_hours', $validated['cancellation_hours'] ?? 1, 'booking', 'number');
        Setting::set('auto_confirm_booking', $validated['auto_confirm_booking'] ?? false, 'booking', 'boolean');

        // Social settings
        Setting::set('telegram_link', $validated['telegram_link'] ?? '', 'social', 'text');
        Setting::set('instagram_link', $validated['instagram_link'] ?? '', 'social', 'text');
        Setting::set('facebook_link', $validated['facebook_link'] ?? '', 'social', 'text');

        // Hero section settings
        Setting::set('hero_title', $validated['hero_title'] ?? '', 'hero', 'text');
        Setting::set('hero_subtitle', $validated['hero_subtitle'] ?? '', 'hero', 'text');
        Setting::set('hero_badge', $validated['hero_badge'] ?? '', 'hero', 'text');
        Setting::set('hero_cta_text', $validated['hero_cta_text'] ?? '', 'hero', 'text');
        Setting::set('hero_view_services_text', $validated['hero_view_services_text'] ?? '', 'hero', 'text');

        return redirect()->route('admin.settings.index')
            ->with('success', 'Sozlamalar muvaffaqiyatli saqlandi');
    }
}
