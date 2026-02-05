<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // General settings
        Setting::set('app_name', 'HomeMessage', 'general', 'text');
        Setting::set('company_phone', '+998 90 123 45 67', 'general', 'text');
        Setting::set('company_email', 'info@homemessage.uz', 'general', 'text');
        Setting::set('company_address', 'Toshkent shahri', 'general', 'text');
        Setting::set('working_hours_start', '09:00', 'general', 'text');
        Setting::set('working_hours_end', '21:00', 'general', 'text');

        // Booking settings
        Setting::set('min_booking_hours', 2, 'booking', 'number');
        Setting::set('max_booking_days', 30, 'booking', 'number');
        Setting::set('cancellation_hours', 1, 'booking', 'number');
        Setting::set('auto_confirm_booking', false, 'booking', 'boolean');

        // Social settings
        Setting::set('telegram_link', '', 'social', 'text');
        Setting::set('instagram_link', '', 'social', 'text');
        Setting::set('facebook_link', '', 'social', 'text');
    }
}
