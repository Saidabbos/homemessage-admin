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
    protected $description = 'Auto-update order statuses based on time (NEW/CONFIRMING→CONFIRMED after 24h, CONFIRMED→IN_PROGRESS, IN_PROGRESS→COMPLETED)';

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
        $details = ['to_confirmed' => [], 'to_in_progress' => [], 'to_completed' => []];

        // 0. NEW/CONFIRMING → CONFIRMED (24 soat ichida dispetcher tasdiqlamasa)
        $toAutoConfirm = Order::whereIn('status', [Order::STATUS_NEW, Order::STATUS_CONFIRMING])
            ->where('created_at', '<=', $now->copy()->subHours(24))
            ->get();

        foreach ($toAutoConfirm as $order) {
            try {
                $orderService->updateStatus($order, Order::STATUS_CONFIRMED, 'orders.logAutoConfirmed24h');

                Log::info('Order auto-confirmed after 24h', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'old_status' => $order->status,
                ]);

                $details['to_confirmed'][] = $order->order_number;
                $this->line("  ✓ #{$order->order_number} → CONFIRMED (auto 24h)");
            } catch (\Exception $e) {
                Log::error('Failed to auto-confirm order', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
                $this->error("  ✗ #{$order->order_number}: {$e->getMessage()}");
            }
        }

        // 1. CONFIRMED → IN_PROGRESS (slot vaqti kelganda)
        $toInProgress = Order::where('status', Order::STATUS_CONFIRMED)
            ->where('booking_date', $today)
            ->where('arrival_window_start', '<=', $currentTime)
            ->get();

        foreach ($toInProgress as $order) {
            try {
                $orderService->updateStatus($order, Order::STATUS_IN_PROGRESS, 'orders.logAutoInProgress');

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

        // 2. CONFIRMED/IN_PROGRESS → COMPLETED (seans tugaganidan 1 soat o'tgach)
        $toCompleted = Order::whereIn('status', [Order::STATUS_IN_PROGRESS, Order::STATUS_CONFIRMED])
            ->where('booking_date', '<=', $today)
            ->with('duration')
            ->get()
            ->filter(function ($order) use ($now) {
                $sessionStart = Carbon::parse($order->booking_date->format('Y-m-d') . ' ' . $order->arrival_window_start);
                $duration = $order->duration?->duration ?? 60;
                $sessionEnd = $sessionStart->copy()->addMinutes($duration);
                $autoCompleteTime = $sessionEnd->copy()->addHour();

                return $now->gte($autoCompleteTime);
            });

        foreach ($toCompleted as $order) {
            try {
                $orderService->updateStatus($order, Order::STATUS_COMPLETED, 'orders.logAutoCompleted');
                $order->update(['auto_completed' => true, 'completed_at' => $now]);

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

        $this->info("Done. CONFIRMED: {$toAutoConfirm->count()}, IN_PROGRESS: {$toInProgress->count()}, COMPLETED: {$toCompleted->count()}");

        // Update scheduler run record
        $endTime = microtime(true);
        $run->update([
            'status' => 'success',
            'records_processed' => $toAutoConfirm->count() + $toInProgress->count() + $toCompleted->count(),
            'details' => $details,
            'finished_at' => now(),
            'duration_ms' => (int)(($endTime - $startTime) * 1000),
        ]);

        return Command::SUCCESS;
    }
}
