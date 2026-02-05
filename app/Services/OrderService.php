<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderLog;
use App\Models\MasterSlotBooking;
use App\Models\Slot;
use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        protected OrderRepository $orderRepository,
        protected SlotService $slotService,
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

            // Update slot booking status if needed
            $this->syncSlotStatus($order, $newStatus);

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
     * Update order slot (change time)
     */
    public function updateSlot(Order $order, int $newSlotId, string $newDate, ?string $comment = null): Order
    {
        if (!$order->canChangeSlot()) {
            throw new \Exception('Bu buyurtma vaqtini o\'zgartirish mumkin emas');
        }

        $oldSlotId = $order->slot_id;
        $oldDate = $order->booking_date->toDateString();
        $newSlot = Slot::findOrFail($newSlotId);

        DB::transaction(function () use ($order, $newSlotId, $newDate, $oldSlotId, $oldDate, $newSlot, $comment) {
            // Release old slot
            MasterSlotBooking::where('master_id', $order->master_id)
                ->where('slot_id', $oldSlotId)
                ->where('date', $oldDate)
                ->where('order_id', $order->id)
                ->delete();

            // Check if new slot is available
            $existingBooking = MasterSlotBooking::where('master_id', $order->master_id)
                ->where('slot_id', $newSlotId)
                ->where('date', $newDate)
                ->first();

            if ($existingBooking && $existingBooking->status !== MasterSlotBooking::STATUS_FREE) {
                throw new \Exception('Tanlangan vaqt band');
            }

            // Book new slot
            MasterSlotBooking::updateOrCreate(
                [
                    'master_id' => $order->master_id,
                    'slot_id' => $newSlotId,
                    'date' => $newDate,
                ],
                [
                    'status' => $this->getSlotStatusForOrderStatus($order->status),
                    'order_id' => $order->id,
                ]
            );

            // Update order
            $order->update([
                'slot_id' => $newSlotId,
                'booking_date' => $newDate,
            ]);

            // Log the change
            $oldTime = Slot::find($oldSlotId)?->start_time ?? '';
            $newTime = $newSlot->start_time;

            OrderLog::log(
                $order,
                OrderLog::ACTION_SLOT_CHANGED,
                "{$oldDate} {$oldTime}",
                "{$newDate} {$newTime}",
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
     * Sync slot booking status with order status
     */
    protected function syncSlotStatus(Order $order, string $orderStatus): void
    {
        $slotStatus = $this->getSlotStatusForOrderStatus($orderStatus);

        if ($orderStatus === Order::STATUS_CANCELLED) {
            // Release slot
            MasterSlotBooking::where('master_id', $order->master_id)
                ->where('slot_id', $order->slot_id)
                ->where('date', $order->booking_date)
                ->where('order_id', $order->id)
                ->delete();
        } else {
            // Update slot status
            MasterSlotBooking::updateOrCreate(
                [
                    'master_id' => $order->master_id,
                    'slot_id' => $order->slot_id,
                    'date' => $order->booking_date,
                ],
                [
                    'status' => $slotStatus,
                    'order_id' => $order->id,
                ]
            );
        }
    }

    /**
     * Map order status to slot status
     */
    protected function getSlotStatusForOrderStatus(string $orderStatus): string
    {
        return match($orderStatus) {
            Order::STATUS_NEW, Order::STATUS_CONFIRMING => MasterSlotBooking::STATUS_PENDING,
            Order::STATUS_CONFIRMED, Order::STATUS_IN_PROGRESS, Order::STATUS_COMPLETED => MasterSlotBooking::STATUS_RESERVED,
            default => MasterSlotBooking::STATUS_FREE,
        };
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
