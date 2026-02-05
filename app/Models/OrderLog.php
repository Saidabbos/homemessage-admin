<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderLog extends Model
{
    // Actions
    public const ACTION_CREATED = 'created';
    public const ACTION_STATUS_CHANGED = 'status_changed';
    public const ACTION_SLOT_CHANGED = 'slot_changed';
    public const ACTION_NOTE_ADDED = 'note_added';
    public const ACTION_PAYMENT_RECEIVED = 'payment_received';
    public const ACTION_CANCELLED = 'cancelled';

    protected $fillable = [
        'order_id',
        'user_id',
        'action',
        'old_value',
        'new_value',
        'comment',
    ];

    // ==================== RELATIONS ====================

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ==================== HELPERS ====================

    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            self::ACTION_CREATED => 'Buyurtma yaratildi',
            self::ACTION_STATUS_CHANGED => 'Status o\'zgartirildi',
            self::ACTION_SLOT_CHANGED => 'Vaqt o\'zgartirildi',
            self::ACTION_NOTE_ADDED => 'Izoh qo\'shildi',
            self::ACTION_PAYMENT_RECEIVED => 'To\'lov qabul qilindi',
            self::ACTION_CANCELLED => 'Bekor qilindi',
            default => $this->action,
        };
    }

    /**
     * Create a log entry for an order
     */
    public static function log(Order $order, string $action, ?string $oldValue = null, ?string $newValue = null, ?string $comment = null): self
    {
        return static::create([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'action' => $action,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'comment' => $comment,
        ]);
    }
}
