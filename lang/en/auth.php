<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines (EN)
    |--------------------------------------------------------------------------
    */

    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',

    'otp' => [
        // Send OTP
        'phone_required' => 'Phone number is required.',
        'phone_invalid' => 'Invalid phone number format. Example: +998901234567',
        'rate_limit_exceeded' => 'Too many requests. Please try again later.',
        'cooldown' => 'Please wait before requesting a new code.',
        'sms_failed' => 'Failed to send SMS. Please try again.',
        'sent' => 'Verification code has been sent.',

        // Verify OTP
        'code_required' => 'Verification code is required.',
        'code_invalid' => 'Verification code must be 6 digits.',
        'too_many_attempts' => 'Too many incorrect attempts. Please try again later.',
        'otp_not_found' => 'Verification code not found. Please request a new one.',
        'otp_expired' => 'Verification code has expired. Please request a new one.',
        'max_attempts_blocked' => 'Too many incorrect attempts. Try again in 30 minutes.',
        'invalid_code' => 'Invalid verification code.',
        'verified' => 'Phone number verified successfully.',

        // Login/Logout
        'login_success' => 'Logged in successfully.',
        'logout_success' => 'Logged out successfully.',
    ],

];
