<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Payment extends Model
{
    // Providers
    public const PROVIDER_PAYME = 'payme';
    public const PROVIDER_CLICK = 'click';

    // Statuses
    public const STATUS_PENDING = 'PENDING';
    public const STATUS_PROCESSING = 'PROCESSING';
    public const STATUS_PAID = 'PAID';
    public const STATUS_CANCELLED = 'CANCELLED';
    public const STATUS_FAILED = 'FAILED';
    public const STATUS_REFUNDED = 'REFUNDED';

    protected $fillable = [
        'order_id',
        'provider',
        'transaction_id',
        'external_id',
        'amount',
        'currency',
        'status',
        'payment_url',
        'paid_at',
        'cancelled_at',
        'refunded_at',
        'expires_at',
        'error_code',
        'error_message',
        'provider_response',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'refunded_at' => 'datetime',
        'expires_at' => 'datetime',
        'provider_response' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->transaction_id)) {
                $payment->transaction_id = static::generateTransactionId();
            }
        });
    }

    /**
     * Generate unique transaction ID
     */
    public static function generateTransactionId(): string
    {
        do {
            $id = 'PAY-' . strtoupper(Str::random(12));
        } while (static::where('transaction_id', $id)->exists());

        return $id;
    }

    // ==================== RELATIONS ====================

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // ==================== SCOPES ====================

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopeByProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    // ==================== HELPERS ====================

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isRefunded(): bool
    {
        return $this->status === self::STATUS_REFUNDED;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }

    public function canBeRefunded(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function markAsPaid(?string $externalId = null): self
    {
        $this->update([
            'status' => self::STATUS_PAID,
            'paid_at' => now(),
            'external_id' => $externalId ?? $this->external_id,
        ]);

        // Update order payment status
        $this->order->update([
            'payment_status' => Order::PAY_PAID,
            'paid_at' => now(),
        ]);

        return $this;
    }

    public function markAsCancelled(?string $reason = null): self
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'error_message' => $reason,
        ]);

        return $this;
    }

    public function markAsFailed(string $errorCode, string $errorMessage): self
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
        ]);

        return $this;
    }

    public function markAsRefunded(): self
    {
        $this->update([
            'status' => self::STATUS_REFUNDED,
            'refunded_at' => now(),
        ]);

        // Update order payment status
        $this->order->update([
            'payment_status' => Order::PAY_REFUNDED,
        ]);

        return $this;
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Kutilmoqda',
            self::STATUS_PROCESSING => 'Jarayonda',
            self::STATUS_PAID => "To'langan",
            self::STATUS_CANCELLED => 'Bekor qilingan',
            self::STATUS_FAILED => 'Xatolik',
            self::STATUS_REFUNDED => 'Qaytarilgan',
            default => $this->status,
        };
    }

    public function getProviderLabelAttribute(): string
    {
        return match($this->provider) {
            self::PROVIDER_PAYME => 'Payme',
            self::PROVIDER_CLICK => 'Click',
            default => $this->provider,
        };
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 0, '.', ' ') . ' ' . $this->currency;
    }
}
