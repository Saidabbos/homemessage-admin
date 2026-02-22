<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\SchedulerRun;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessOrderStatuses extends Command
{
    protected $signature = 'orders:process-statuses';
    protected $description = 'Auto-update order statuses based on time (CONFIRMED→IN_PROGRESS, IN_PROGRESS→COMPLETED)';

    public function handle(OrderService $orderService): int
    {
        $startTime = microtime(true);
        $now = Carbon::now();
        $today = $now->toDateString();
        $currentTime = $now->format('H:i:s');

        // Create scheduler run record
        $run = SchedulerRun::create([
            'command' => 'orders:process-statuses',
            'status' => 'running',
            'started_at' => $now,
        ]);

        $this->info("Processing orders at {$now}");
        $details = ['to_in_progress' => [], 'to_completed' => []];

        // 1. CONFIRMED → IN_PROGRESS (slot vaqti kelganda)
        $toInProgress = Order::where('status', Order::STATUS_CONFIRMED)
            ->where('booking_date', $today)
            ->where('arrival_window_start', '<=', $currentTime)
            ->get();

        foreach ($toInProgress as $order) {
            try {
                $order->update(['status' => Order::STATUS_IN_PROGRESS]);
                
                Log::info('Order auto-transitioned to IN_PROGRESS', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'arrival_window_start' => $order->arrival_window_start,
                ]);
                
                $details['to_in_progress'][] = $order->order_number;
                $this->line("  ✓ #{$order->order_number} → IN_PROGRESS");
            } catch (\Exception $e) {
                Log::error('Failed to auto-transition order to IN_PROGRESS', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
                $this->error("  ✗ #{$order->order_number}: {$e->getMessage()}");
            }
        }

        // 2. IN_PROGRESS → COMPLETED (seans tugaganidan 1 soat o'tgach)
        $toCompleted = Order::where('status', Order::STATUS_IN_PROGRESS)
            ->where('booking_date', '<=', $today)
            ->with('duration')
            ->get()
            ->filter(function ($order) use ($now) {
                // Calculate when session should end
                $sessionStart = Carbon::parse($order->booking_date->format('Y-m-d') . ' ' . $order->arrival_window_start);
                $duration = $order->duration?->duration ?? 60; // default 60 min
                $sessionEnd = $sessionStart->copy()->addMinutes($duration);
                $autoCompleteTime = $sessionEnd->copy()->addHour(); // +1 soat
                
                return $now->gte($autoCompleteTime);
            });

        foreach ($toCompleted as $order) {
            try {
                $order->update([
                    'status' => Order::STATUS_COMPLETED,
                    'auto_completed' => true,
                    'completed_at' => $now,
                ]);
                
                Log::info('Order auto-completed', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ]);
                
                $details['to_completed'][] = $order->order_number;
                $this->line("  ✓ #{$order->order_number} → COMPLETED (auto)");
            } catch (\Exception $e) {
                Log::error('Failed to auto-complete order', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
                $this->error("  ✗ #{$order->order_number}: {$e->getMessage()}");
            }
        }

        $this->info("Done. IN_PROGRESS: {$toInProgress->count()}, COMPLETED: {$toCompleted->count()}");

        // Update scheduler run record
        $endTime = microtime(true);
        $run->update([
            'status' => 'success',
            'records_processed' => $toInProgress->count() + $toCompleted->count(),
            'details' => $details,
            'finished_at' => now(),
            'duration_ms' => (int)(($endTime - $startTime) * 1000),
        ]);

        return Command::SUCCESS;
    }
}
