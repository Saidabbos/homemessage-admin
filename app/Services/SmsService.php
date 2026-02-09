<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.eskiz.base_url');
    }

    /**
     * Send OTP SMS via Eskiz.uz
     */
    public function sendOtp(string $phone, string $code, string $locale = 'uz'): bool
    {
        // Skip SMS if disabled (for testing while Eskiz pending moderation)
        if (config('services.eskiz.skip_send', false)) {
            Log::info('SmsService: SMS skipped (testing mode)', ['phone' => $phone, 'code' => $code]);
            return true;
        }

        $token = $this->getAuthToken();

        if (!$token) {
            Log::error('Eskiz SMS send failed: unable to get auth token', ['phone' => $phone]);
            return false;
        }

        $message = $this->buildMessage($code, $locale);

        try {
            $response = Http::withToken($token)
                ->post($this->baseUrl . '/message/sms/send', [
                    'mobile_phone' => $this->formatPhone($phone),
                    'message' => $message,
                    'from' => config('services.eskiz.sender'),
                ]);

            if ($response->successful()) {
                Log::info('SMS sent successfully via Eskiz', [
                    'phone' => $phone,
                    'locale' => $locale,
                ]);
                return true;
            }

            Log::error('Eskiz SMS send failed: API error', [
                'phone' => $phone,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);
            return false;
        } catch (Exception $e) {
            Log::error('Eskiz SMS send failed: exception', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get or refresh auth token from Eskiz.uz
     */
    protected function getAuthToken(): ?string
    {
        $cacheKey = config('services.eskiz.token_cache_key');

        return Cache::remember($cacheKey, config('services.eskiz.token_ttl'), function () {
            return $this->authenticate();
        });
    }

    /**
     * Authenticate with Eskiz.uz API
     */
    protected function authenticate(): ?string
    {
        try {
            $response = Http::post($this->baseUrl . '/auth/login', [
                'email' => config('services.eskiz.email'),
                'password' => config('services.eskiz.password'),
            ]);

            if ($response->successful()) {
                $token = $response->json('data.token');
                Log::info('Authenticated with Eskiz API');
                return $token;
            }

            Log::error('Eskiz authentication failed', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);
            return null;
        } catch (Exception $e) {
            Log::error('Eskiz authentication failed: exception', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Format phone number for Eskiz API (remove + prefix)
     * Converts +998901234567 to 998901234567
     */
    protected function formatPhone(string $phone): string
    {
        return ltrim($phone, '+');
    }

    /**
     * Build localized OTP message
     */
    protected function buildMessage(string $code, string $locale = 'uz'): string
    {
        $messages = [
            'uz' => "Home massage: Platformada kirish uchun tasdiqlash kodi: {$code}",
            'ru' => "Home massage: Код для входа на платформу: {$code}",
            'en' => "Home massage: Platform login verification code: {$code}",
        ];

        return $messages[$locale] ?? $messages['uz'];
    }

    /**
     * Clear cached auth token (for testing or forced refresh)
     */
    public function clearCache(): void
    {
        Cache::forget(config('services.eskiz.token_cache_key'));
    }
}
