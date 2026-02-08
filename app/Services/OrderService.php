<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderLog;
use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function __construct(
        protected OrderRepository $orderRepository,
    ) {}

    /**
     * Update order status
     */
    public function updateStatus(Order $order, string $newStatus, ?string $comment = null): Order
    {
        Log::info('OrderService: Status update requested', [
            'order_id' => $order->id,
            'current_status' => $order->status,
            'new_status' => $newStatus,
        ]);

        if (!$order->canChangeStatus()) {
            Log::warning('OrderService: Status change not allowed', ['order_id' => $order->id]);
            throw new \Exception('Bu buyurtma statusini o\'zgartirish mumkin emas');
        }

        $oldStatus = $order->status;

        DB::transaction(function () use ($order, $newStatus, $oldStatus, $comment) {
            $order->update(['status' => $newStatus]);

            // Log the change
            OrderLog::log(
                $order,
                OrderLog::ACTION_STATUS_CHANGED,
                $oldStatus,
                $newStatus,
                $comment
            );

            // Handle specific status changes
            if ($newStatus === Order::STATUS_CONFIRMED && !$order->confirmed_at) {
                $order->update([
                    'confirmed_by' => auth()->id(),
                    'confirmed_at' => now(),
                ]);
                Log::info('OrderService: Order confirmed', ['order_id' => $order->id, 'confirmed_by' => auth()->id()]);
            }

            if ($newStatus === Order::STATUS_CANCELLED) {
                $order->update([
                    'cancelled_by' => auth()->id(),
                    'cancelled_at' => now(),
                    'cancel_reason' => $comment,
                ]);
                Log::info('OrderService: Order cancelled', ['order_id' => $order->id, 'reason' => $comment]);
            }
        });

        Log::info('OrderService: Status updated successfully', [
            'order_id' => $order->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);

        return $order->fresh();
    }

    /**
     * Reschedule order (change date/time)
     */
    public function reschedule(
        Order $order,
        string $newDate,
        string $arrivalWindowStart,
        string $arrivalWindowEnd,
        ?string $comment = null
    ): Order {
        Log::info('OrderService: Reschedule requested', [
            'order_id' => $order->id,
            'new_date' => $newDate,
            'window' => "{$arrivalWindowStart}-{$arrivalWindowEnd}",
        ]);

        if (!$order->canChangeSlot()) {
            Log::warning('OrderService: Reschedule not allowed', ['order_id' => $order->id]);
            throw new \Exception('Bu buyurtma vaqtini o\'zgartirish mumkin emas');
        }

        $oldDate = $order->booking_date->toDateString();
        $oldWindow = $order->arrival_window_display;

        DB::transaction(function () use ($order, $newDate, $arrivalWindowStart, $arrivalWindowEnd, $oldDate, $oldWindow, $comment) {
            // Update order
            $order->update([
                'booking_date' => $newDate,
                'arrival_window_start' => $arrivalWindowStart,
                'arrival_window_end' => $arrivalWindowEnd,
            ]);

            // Log the change
            $newWindow = substr($arrivalWindowStart, 0, 5) . '–' . substr($arrivalWindowEnd, 0, 5);

            OrderLog::log(
                $order,
                'rescheduled',
                "{$oldDate} {$oldWindow}",
                "{$newDate} {$newWindow}",
                $comment
            );
        });

        Log::info('OrderService: Order rescheduled successfully', [
            'order_id' => $order->id,
            'old' => "{$oldDate} {$oldWindow}",
            'new' => "{$newDate} {$arrivalWindowStart}-{$arrivalWindowEnd}",
        ]);

        return $order->fresh();
    }

    /**
     * Add dispatcher note
     */
    public function addNote(Order $order, string $note): Order
    {
        Log::info('OrderService: Adding note', ['order_id' => $order->id]);

        $order->update([
            'dispatcher_notes' => $order->dispatcher_notes
                ? $order->dispatcher_notes . "\n\n---\n\n" . now()->format('d.m.Y H:i') . " - " . auth()->user()->name . ":\n" . $note
                : now()->format('d.m.Y H:i') . " - " . auth()->user()->name . ":\n" . $note,
        ]);

        OrderLog::log($order, OrderLog::ACTION_NOTE_ADDED, null, null, $note);

        Log::info('OrderService: Note added successfully', ['order_id' => $order->id]);

        return $order->fresh();
    }

    /**
     * Cancel order
     */
    public function cancel(Order $order, string $reason): Order
    {
        Log::info('OrderService: Cancellation requested', ['order_id' => $order->id, 'reason' => $reason]);
        return $this->updateStatus($order, Order::STATUS_CANCELLED, $reason);
    }

    /**
     * Get available statuses for transition
     */
    public function getAvailableStatuses(Order $order): array
    {
        if (!$order->canChangeStatus()) {
            return [];
        }

        return match($order->status) {
            Order::STATUS_NEW => [
                Order::STATUS_CONFIRMING,
                Order::STATUS_CANCELLED,
            ],
            Order::STATUS_CONFIRMING => [
                Order::STATUS_CONFIRMED,
                Order::STATUS_CANCELLED,
            ],
            Order::STATUS_CONFIRMED => [
                Order::STATUS_IN_PROGRESS,
                Order::STATUS_CANCELLED,
            ],
            Order::STATUS_IN_PROGRESS => [
                Order::STATUS_COMPLETED,
                Order::STATUS_CANCELLED,
            ],
            default => [],
        };
    }
}
