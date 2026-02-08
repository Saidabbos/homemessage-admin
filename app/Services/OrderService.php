<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderLog;
use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\DB;

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
        if (!$order->canChangeStatus()) {
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
            }

            if ($newStatus === Order::STATUS_CANCELLED) {
                $order->update([
                    'cancelled_by' => auth()->id(),
                    'cancelled_at' => now(),
                    'cancel_reason' => $comment,
                ]);
            }
        });

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
        if (!$order->canChangeSlot()) {
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

        return $order->fresh();
    }

    /**
     * Add dispatcher note
     */
    public function addNote(Order $order, string $note): Order
    {
        $order->update([
            'dispatcher_notes' => $order->dispatcher_notes
                ? $order->dispatcher_notes . "\n\n---\n\n" . now()->format('d.m.Y H:i') . " - " . auth()->user()->name . ":\n" . $note
                : now()->format('d.m.Y H:i') . " - " . auth()->user()->name . ":\n" . $note,
        ]);

        OrderLog::log($order, OrderLog::ACTION_NOTE_ADDED, null, null, $note);

        return $order->fresh();
    }

    /**
     * Cancel order
     */
    public function cancel(Order $order, string $reason): Order
    {
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
