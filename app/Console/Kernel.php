<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Clean up expired OTP codes daily at 2 AM
        $schedule->command('otp:cleanup')
            ->dailyAt('02:00')
            ->timezone('Asia/Tashkent');

        // Auto-update order statuses every 5 minutes
        // CONFIRMED → IN_PROGRESS (slot vaqti kelganda)
        // IN_PROGRESS → COMPLETED (seans + 1 soat o'tgach)
        $schedule->command('orders:process-statuses')
            ->everyFiveMinutes()
            ->timezone('Asia/Tashkent')
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
