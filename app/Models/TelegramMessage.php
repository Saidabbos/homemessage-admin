<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelegramMessage extends Model
{
    // Types
    public const TYPE_NEW_ORDER = 'new_order';
    public const TYPE_ORDER_CONFIRMED = 'order_confirmed';
    public const TYPE_PAYMENT_RECEIVED = 'payment_received';
    public const TYPE_ORDER_READY = 'order_ready';
    public const TYPE_ORDER_COMPLETED = 'order_completed';
    public const TYPE_ORDER_CANCELLED = 'order_cancelled';
    public const TYPE_REMINDER = 'reminder';
    public const TYPE_CUSTOM = 'custom';

    // Statuses
    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'order_id',
        'user_id',
        'type',
        'chat_id',
        'message_id',
        'content',
        'status',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
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

    // ==================== SCOPES ====================

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeSent($query)
    {
        return $query->where('status', self::STATUS_SENT);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeForOrder($query, int $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    // ==================== HELPERS ====================

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function markAsSent(int $messageId): self
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'message_id' => $messageId,
            'sent_at' => now(),
        ]);

        return $this;
    }

    public function markAsDelivered(): self
    {
        $this->update([
            'status' => self::STATUS_DELIVERED,
        ]);

        return $this;
    }

    public function markAsFailed(string $error): self
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $error,
        ]);

        return $this;
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            self::TYPE_NEW_ORDER => 'Yangi buyurtma',
            self::TYPE_ORDER_CONFIRMED => 'Buyurtma tasdiqlandi',
            self::TYPE_PAYMENT_RECEIVED => "To'lov qabul qilindi",
            self::TYPE_ORDER_READY => 'Buyurtma tayyor',
            self::TYPE_ORDER_COMPLETED => 'Buyurtma yakunlandi',
            self::TYPE_ORDER_CANCELLED => 'Buyurtma bekor qilindi',
            self::TYPE_REMINDER => 'Eslatma',
            self::TYPE_CUSTOM => 'Boshqa',
            default => $this->type,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Kutilmoqda',
            self::STATUS_SENT => 'Yuborildi',
            self::STATUS_DELIVERED => 'Yetkazildi',
            self::STATUS_FAILED => 'Xatolik',
            default => $this->status,
        };
    }
}
