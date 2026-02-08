<?php

namespace App\Repositories;

use App\Models\OtpCode;
use App\Models\OtpRateLimit;

class OtpRepository extends BaseRepository
{
    protected function getModelClass(): string
    {
        return OtpCode::class;
    }

    /**
     * Get the most recent OTP for a phone number
     */
    public function getLatestOtp(string $phone): ?OtpCode
    {
        return $this->query()
            ->where('phone', $phone)
            ->latest()
            ->first();
    }

    /**
     * Get the latest unverified and not-expired OTP
     */
    public function getLatestUnverifiedOtp(string $phone): ?OtpCode
    {
        return $this->query()
            ->where('phone', $phone)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
    }

    /**
     * Check if sending OTP is currently blocked for a phone number
     */
    public function isSendBlocked(string $phone, string $ipAddress): bool
    {
        // Check if phone is rate-limited
        $phoneLimit = OtpRateLimit::where('phone', $phone)
            ->where('action', 'send')
            ->first();

        if ($phoneLimit && $phoneLimit->blocked_until && $phoneLimit->blocked_until->isFuture()) {
            return true;
        }

        // Check hourly limit (max 5 sends per hour)
        $recentSends = $this->query()
            ->where('phone', $phone)
            ->where('created_at', '>', now()->subHour())
            ->count();

        return $recentSends >= config('auth.otp.max_send_per_hour', 5);
    }

    /**
     * Check if verification is blocked due to too many failed attempts
     */
    public function isVerifyBlocked(string $phone): bool
    {
        $limit = OtpRateLimit::where('phone', $phone)
            ->where('action', 'verify')
            ->first();

        return $limit && $limit->blocked_until && $limit->blocked_until->isFuture();
    }

    /**
     * Increment send attempt count for rate limiting
     */
    public function incrementSendCount(string $phone, string $ipAddress): void
    {
        $rateLimit = OtpRateLimit::firstOrCreate(
            ['phone' => $phone, 'action' => 'send'],
            ['ip_address' => $ipAddress, 'attempts_count' => 0]
        );

        $rateLimit->increment('attempts_count');
    }

    /**
     * Increment failed verification attempts
     */
    public function incrementVerifyAttempts(OtpCode $otpCode): void
    {
        $otpCode->increment('verify_attempts');
    }

    /**
     * Block verification for a phone number (15 minutes)
     */
    public function blockVerify(string $phone): void
    {
        OtpRateLimit::updateOrCreate(
            ['phone' => $phone, 'action' => 'verify'],
            ['blocked_until' => now()->addMinutes(config('auth.otp.verify_block_minutes', 15))]
        );
    }

    /**
     * Mark OTP code as verified
     */
    public function markAsVerified(OtpCode $otpCode): void
    {
        $otpCode->update(['verified_at' => now()]);
    }

    /**
     * Delete expired OTP codes (older than 24 hours)
     */
    public function deleteExpired(): int
    {
        return $this->query()
            ->where('expires_at', '<', now()->subDay())
            ->delete();
    }

    /**
     * Get count of unverified OTPs for a phone number
     */
    public function getUnverifiedCount(string $phone): int
    {
        return $this->query()
            ->where('phone', $phone)
            ->whereNull('verified_at')
            ->count();
    }
}
