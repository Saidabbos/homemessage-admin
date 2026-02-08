<?php

namespace App\Services;

use App\Repositories\OtpRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class OtpService
{
    public function __construct(
        protected SmsService $smsService,
        protected OtpRepository $otpRepository,
    ) {}

    /**
     * Send OTP to a phone number
     * Returns array with success status and message
     */
    public function sendOtp(string $phone, string $ipAddress, string $userAgent, string $locale = 'uz'): array
    {
        Log::info('OtpService: Send OTP request', ['phone' => $phone, 'ip' => $ipAddress, 'locale' => $locale]);

        // Check if send is rate-limited
        if ($this->otpRepository->isSendBlocked($phone, $ipAddress)) {
            $hourlyCount = $this->otpRepository->query()
                ->where('phone', $phone)
                ->where('created_at', '>', now()->subHour())
                ->count();

            if ($hourlyCount >= config('auth.otp.max_send_per_hour', 5)) {
                Log::warning('OtpService: Rate limit exceeded', ['phone' => $phone, 'hourly_count' => $hourlyCount]);
                return [
                    'success' => false,
                    'error' => 'rate_limit_exceeded',
                    'message' => __('auth.otp.rate_limit_exceeded'),
                ];
            }
        }

        // Check cooldown between sends (60 seconds)
        $lastOtp = $this->otpRepository->getLatestOtp($phone);
        if ($lastOtp && $lastOtp->created_at->diffInSeconds(now()) < config('auth.otp.send_cooldown_seconds', 60)) {
            $waitTime = config('auth.otp.send_cooldown_seconds', 60) - $lastOtp->created_at->diffInSeconds(now());
            Log::info('OtpService: Cooldown active', ['phone' => $phone, 'wait_time' => $waitTime]);
            return [
                'success' => false,
                'error' => 'cooldown',
                'message' => __('auth.otp.cooldown'),
                'retry_after' => $waitTime,
            ];
        }

        // Generate OTP code
        $code = $this->generateCode();
        Log::info('OtpService: OTP code generated', ['phone' => $phone]);

        // Store OTP with hashed code
        $otpRecord = $this->otpRepository->create([
            'phone' => $phone,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(config('auth.otp.expires_minutes', 3)),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
        Log::info('OtpService: OTP record created', ['phone' => $phone, 'otp_id' => $otpRecord->id]);

        // Send SMS via Eskiz.uz
        $sent = $this->smsService->sendOtp($phone, $code, $locale);

        if (!$sent) {
            // Delete the OTP record if SMS failed
            $otpRecord->delete();
            Log::error('OtpService: SMS send failed, OTP record deleted', ['phone' => $phone]);
            return [
                'success' => false,
                'error' => 'sms_failed',
                'message' => __('auth.otp.sms_failed'),
            ];
        }

        // Track send attempt for rate limiting
        $this->otpRepository->incrementSendCount($phone, $ipAddress);

        Log::info('OtpService: OTP sent successfully', ['phone' => $phone, 'expires_at' => $otpRecord->expires_at]);
        return [
            'success' => true,
            'expires_at' => $otpRecord->expires_at->toIso8601String(),
            'message' => __('auth.otp.sent'),
        ];
    }

    /**
     * Verify OTP code
     * Returns array with success status and message
     */
    public function verifyOtp(string $phone, string $code): array
    {
        Log::info('OtpService: Verify OTP request', ['phone' => $phone]);

        // Check if verification is blocked
        if ($this->otpRepository->isVerifyBlocked($phone)) {
            Log::warning('OtpService: Verification blocked', ['phone' => $phone]);
            return [
                'success' => false,
                'error' => 'verify_blocked',
                'message' => __('auth.otp.too_many_attempts'),
            ];
        }

        // Get active OTP for verification
        $otpRecord = $this->otpRepository->getLatestUnverifiedOtp($phone);

        if (!$otpRecord) {
            Log::warning('OtpService: OTP not found', ['phone' => $phone]);
            return [
                'success' => false,
                'error' => 'otp_not_found',
                'message' => __('auth.otp.otp_not_found'),
            ];
        }

        // Check if OTP is expired
        if ($otpRecord->expires_at->isPast()) {
            Log::warning('OtpService: OTP expired', ['phone' => $phone, 'expired_at' => $otpRecord->expires_at]);
            return [
                'success' => false,
                'error' => 'otp_expired',
                'message' => __('auth.otp.otp_expired'),
            ];
        }

        // Verify code against hash
        if (!Hash::check($code, $otpRecord->code_hash)) {
            $this->otpRepository->incrementVerifyAttempts($otpRecord);

            $attemptsLeft = config('auth.otp.max_verify_attempts', 5) - ($otpRecord->verify_attempts + 1);
            Log::warning('OtpService: Invalid code entered', ['phone' => $phone, 'attempts_left' => $attemptsLeft]);

            // Block after max attempts
            if ($attemptsLeft <= 0) {
                $this->otpRepository->blockVerify($phone);
                Log::error('OtpService: Max attempts reached, verification blocked', ['phone' => $phone]);
                return [
                    'success' => false,
                    'error' => 'max_attempts',
                    'message' => __('auth.otp.max_attempts_blocked'),
                ];
            }

            return [
                'success' => false,
                'error' => 'invalid_code',
                'message' => __('auth.otp.invalid_code'),
                'attempts_left' => $attemptsLeft,
            ];
        }

        // Mark OTP as verified
        $this->otpRepository->markAsVerified($otpRecord);

        Log::info('OtpService: OTP verified successfully', ['phone' => $phone, 'otp_id' => $otpRecord->id]);
        return [
            'success' => true,
            'message' => __('auth.otp.verified'),
        ];
    }

    /**
     * Generate random 6-digit OTP code
     */
    protected function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Clean up expired OTP codes (older than 24 hours)
     */
    public function cleanupExpired(): int
    {
        $deleted = $this->otpRepository->deleteExpired();
        Log::info('OtpService: Expired OTP codes cleaned up', ['deleted_count' => $deleted]);
        return $deleted;
    }
}
