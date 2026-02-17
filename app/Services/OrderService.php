<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Order;
use App\Models\OrderLog;
use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function __construct(
        protected OrderRepository $orderRepository,
        protected ?TelegramNotificationService $telegramService = null,
        protected ?TelegramBotService $telegramBotService = null,
    ) {
        $this->telegramService = $telegramService ?? app(TelegramNotificationService::class);
        $this->telegramBotService = $telegramBotService ?? app(TelegramBotService::class);
    }

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

            // System-wide audit log
            AuditLog::log(
                $order,
                AuditLog::ACTION_STATUS_CHANGED,
                ['status' => $oldStatus],
                ['status' => $newStatus],
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

        // Auto-send Telegram notifications on status change
        $order = $order->fresh();
        $this->sendStatusChangeNotifications($order, $oldStatus, $newStatus);

        return $order;
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

            // System-wide audit log
            AuditLog::log(
                $order,
                AuditLog::ACTION_SLOT_CHANGED,
                ['booking_date' => $oldDate, 'window' => $oldWindow],
                ['booking_date' => $newDate, 'window' => $newWindow],
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

    /**
     * Save confirmation form (Block C - Анкета подтверждения)
     */
    public function saveConfirmation(Order $order, array $data): Order
    {
        Log::info('OrderService: Saving confirmation form', [
            'order_id' => $order->id,
            'call_outcome' => $data['call_outcome'] ?? null,
        ]);

        DB::transaction(function () use ($order, $data) {
            $order->update([
                'call_outcome' => $data['call_outcome'] ?? $order->call_outcome,
                'conf_entrance' => $data['conf_entrance'] ?? $order->conf_entrance,
                'conf_floor' => $data['conf_floor'] ?? $order->conf_floor,
                'conf_elevator' => $data['conf_elevator'] ?? false,
                'conf_parking' => $data['conf_parking'] ?? $order->conf_parking,
                'conf_landmark' => $data['conf_landmark'] ?? $order->conf_landmark,
                'conf_onsite_phone' => $data['conf_onsite_phone'] ?? $order->conf_onsite_phone,
                'conf_constraints' => $data['conf_constraints'] ?? $order->conf_constraints,
                'conf_space_ok' => $data['conf_space_ok'] ?? false,
                'conf_pets' => $data['conf_pets'] ?? false,
                'conf_note_to_master' => $data['conf_note_to_master'] ?? $order->conf_note_to_master,
                'confirmed_by' => auth()->id(),
                'confirmed_at' => now(),
            ]);

            // Log the confirmation
            OrderLog::log(
                $order,
                'confirmed',
                null,
                $data['call_outcome'] ?? 'confirmed',
                'Анкета заполнена'
            );
        });

        // Check if ready to send to therapists
        $this->checkAndSendReadyNotification($order->fresh());

        Log::info('OrderService: Confirmation saved successfully', ['order_id' => $order->id]);

        return $order->fresh();
    }

    /**
     * Check if order is ready and send notification to therapists
     */
    public function checkAndSendReadyNotification(Order $order): bool
    {
        if (!$order->isReadyForTherapist()) {
            Log::debug('OrderService: Order not ready for therapist notification', [
                'order_id' => $order->id,
                'call_outcome' => $order->call_outcome,
                'payment_status' => $order->payment_status,
                'status' => $order->status,
                'has_address' => !empty($order->address),
                'ready_sent_at' => $order->ready_sent_at,
            ]);
            return false;
        }

        Log::info('OrderService: Sending READY notification to therapists', ['order_id' => $order->id]);

        $result = $this->telegramService->notifyReady($order);

        if ($result) {
            Log::info('OrderService: READY notification sent successfully', ['order_id' => $order->id]);
        } else {
            Log::warning('OrderService: Failed to send READY notification', ['order_id' => $order->id]);
        }

        return $result;
    }

    /**
     * Get call outcome options
     */
    public function getCallOutcomeOptions(): array
    {
        return [
            ['value' => 'pending', 'label' => 'Kutilmoqda'],
            ['value' => 'confirmed', 'label' => 'Tasdiqlandi'],
            ['value' => 'reschedule', 'label' => 'Qayta rejalashtirish'],
            ['value' => 'no_answer', 'label' => 'Javob bermadi'],
            ['value' => 'cancelled', 'label' => 'Bekor qilindi'],
        ];
    }

    /**
     * Send Telegram notifications when order status changes
     */
    protected function sendStatusChangeNotifications(Order $order, string $oldStatus, string $newStatus): void
    {
        // Load relations for notifications
        $order->load(['customer', 'master.user']);

        // IN_PROGRESS - Session started
        if ($newStatus === Order::STATUS_IN_PROGRESS && $oldStatus !== Order::STATUS_IN_PROGRESS) {
            Log::info('OrderService: Sending session start notifications', ['order_id' => $order->id]);
            
            try {
                // Notify client
                $clientResult = $this->telegramBotService->notifyClientSessionStart($order);
                Log::info('OrderService: Client session start notification', [
                    'order_id' => $order->id,
                    'result' => $clientResult ? 'sent' : 'skipped (no telegram_id)',
                ]);

                // Notify master
                $masterResult = $this->telegramBotService->notifyMasterSessionStart($order);
                Log::info('OrderService: Master session start notification', [
                    'order_id' => $order->id,
                    'result' => $masterResult ? 'sent' : 'skipped (no telegram_id)',
                ]);
            } catch (\Exception $e) {
                Log::error('OrderService: Failed to send session start notifications', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // COMPLETED - Session ended
        if ($newStatus === Order::STATUS_COMPLETED && $oldStatus !== Order::STATUS_COMPLETED) {
            Log::info('OrderService: Sending session end notifications', ['order_id' => $order->id]);
            
            try {
                // Notify client (with rating button)
                $clientResult = $this->telegramBotService->notifyClientSessionEnd($order);
                Log::info('OrderService: Client session end notification', [
                    'order_id' => $order->id,
                    'result' => $clientResult ? 'sent' : 'skipped (no telegram_id)',
                ]);

                // Notify master (with rating button)
                $masterResult = $this->telegramBotService->notifyMasterSessionEnd($order);
                Log::info('OrderService: Master session end notification', [
                    'order_id' => $order->id,
                    'result' => $masterResult ? 'sent' : 'skipped (no telegram_id)',
                ]);
            } catch (\Exception $e) {
                Log::error('OrderService: Failed to send session end notifications', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
