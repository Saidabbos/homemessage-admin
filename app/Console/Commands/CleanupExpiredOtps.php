<?php

namespace App\Console\Commands;

use App\Services\OtpService;
use Illuminate\Console\Command;

class CleanupExpiredOtps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'otp:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete expired OTP codes older than 24 hours';

    /**
     * Execute the console command.
     */
    public function handle(OtpService $otpService): int
    {
        $deleted = $otpService->cleanupExpired();

        $this->info("Deleted {$deleted} expired OTP records.");

        return 0;
    }
}
