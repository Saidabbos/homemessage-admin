<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Order extends Model
{
    // Statuses
    public const STATUS_NEW = 'NEW';
    public const STATUS_CONFIRMING = 'CONFIRMING';
    public const STATUS_CONFIRMED = 'CONFIRMED';
    public const STATUS_IN_PROGRESS = 'IN_PROGRESS';
    public const STATUS_COMPLETED = 'COMPLETED';
    public const STATUS_CANCELLED = 'CANCELLED';

    // Payment statuses
    public const PAY_NOT_PAID = 'NOT_PAID';
    public const PAY_PENDING = 'PENDING';
    public const PAY_PAID = 'PAID';
    public const PAY_FAILED = 'FAILED';
    public const PAY_REFUNDED = 'REFUNDED';
    public const PAY_CANCELLED = 'CANCELLED';

    protected $fillable = [
        'order_number',
        'booking_group_id',
        'customer_id',
        'master_id',
        'service_type_id',
        'duration_id',
        'oil_id',
        'people_count',
        'pressure_level',
        'booking_date',
        'arrival_window_start',
        'arrival_window_end',
        'status',
        'payment_status',
        'total_amount',
        'paid_at',
        'address',
        'entrance',
        'floor',
        'apartment',
        'landmark',
        'contact_phone',
        'dispatcher_notes',
        // Confirmation form fields
        'conf_entrance',
        'conf_floor',
        'conf_elevator',
        'conf_parking',
        'conf_landmark',
        'conf_onsite_phone',
        'conf_constraints',
        'conf_space_ok',
        'conf_pets',
        'conf_note_to_master',
        'call_outcome',
        'confirmed_by',
        'confirmed_at',
        'ready_sent_at',
        'work_order_sent_at',
        'cancellation_reason',
        'cancelled_by',
        'cancelled_at',
        // QA fields
        'qa_completed',
        'qa_overall_rating',
        'qa_punctuality_rating',
        'qa_professionalism_rating',
        'qa_feedback',
        'qa_completed_at',
        'qa_completed_by',
        // Auto-completion
        'auto_completed',
        'completed_at',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'people_count' => 'integer',
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'ready_sent_at' => 'datetime',
        'completed_at' => 'datetime',
        'auto_completed' => 'boolean',
        'conf_elevator' => 'boolean',
        'conf_space_ok' => 'boolean',
        'conf_pets' => 'boolean',
        'work_order_sent_at' => 'datetime',
        'qa_completed' => 'boolean',
        'qa_completed_at' => 'datetime',
    ];

    /**
     * Arrival window display (10:00–10:30)
     */
    public function getArrivalWindowDisplayAttribute(): ?string
    {
        if (!$this->arrival_window_start || !$this->arrival_window_end) {
            return null;
        }
        
        $start = substr($this->arrival_window_start, 0, 5);
        $end = substr($this->arrival_window_end, 0, 5);
        
        return "{$start}–{$end}";
    }

    /**
     * Booking time (alias for arrival_window_display)
     */
    public function getBookingTimeAttribute(): ?string
    {
        return $this->arrival_window_display;
    }

    /**
     * Generate order number: HM-YYYYMMDD-XXX
     */
    public static function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = "HM-{$date}-";

        $lastOrder = static::where('order_number', 'like', "{$prefix}%")
            ->orderBy('order_number', 'desc')
            ->first();

        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->order_number, -3);
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        return $prefix . $newNumber;
    }

    // ==================== RELATIONS ====================

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function master(): BelongsTo
    {
        return $this->belongsTo(Master::class);
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function duration(): BelongsTo
    {
        return $this->belongsTo(ServiceTypeDuration::class, 'duration_id');
    }

    public function oil(): BelongsTo
    {
        return $this->belongsTo(Oil::class);
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(OrderLog::class)->latest();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest();
    }

    public function latestPayment()
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function qualityControl()
    {
        return $this->hasOne(QualityControl::class);
    }

    public function telegramMessages(): HasMany
    {
        return $this->hasMany(TelegramMessage::class)->latest();
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    public function customerRating()
    {
        return $this->hasOne(Rating::class)->where('type', Rating::TYPE_CLIENT_TO_MASTER);
    }

    public function masterRating()
    {
        return $this->hasOne(Rating::class)->where('type', Rating::TYPE_MASTER_TO_CLIENT);
    }

    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable')->latest();
    }

    // ==================== SCOPES ====================

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeNew($query)
    {
        return $query->where('status', self::STATUS_NEW);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('booking_date', $date);
    }

    public function scopeForMaster($query, $masterId)
    {
        return $query->where('master_id', $masterId);
    }

    /**
     * Get orders in the same booking group
     */
    public function scopeInGroup($query, $groupId)
    {
        return $query->where('booking_group_id', $groupId);
    }

    /**
     * Get related orders in same booking group (excluding self)
     */
    public function getGroupOrdersAttribute()
    {
        if (!$this->booking_group_id) {
            return collect();
        }
        return static::where('booking_group_id', $this->booking_group_id)
            ->where('id', '!=', $this->id)
            ->get();
    }

    /**
     * Check if order is ready for THERAPISTS notification
     * Conditions: confirmed + paid + has address
     */
    public function isReadyForTherapist(): bool
    {
        return $this->call_outcome === 'confirmed'
            && $this->payment_status === self::PAY_PAID
            && $this->status === self::STATUS_CONFIRMED
            && !empty($this->address)
            && empty($this->ready_sent_at);
    }

    /**
     * Mark READY notification as sent
     */
    public function markReadySent(): self
    {
        $this->update(['ready_sent_at' => now()]);
        return $this;
    }

    /**
     * Get call outcome label
     */
    public function getCallOutcomeLabelAttribute(): string
    {
        return match($this->call_outcome) {
            'pending' => 'Kutilmoqda',
            'confirmed' => 'Tasdiqlandi',
            'reschedule' => 'Qayta rejalashtirish',
            'no_answer' => 'Javob bermadi',
            'cancelled' => 'Bekor qilindi',
            default => $this->call_outcome ?? 'Kutilmoqda',
        };
    }

    /**
     * Check if order is part of a multi-master booking
     */
    public function isGroupBooking(): bool
    {
        return !empty($this->booking_group_id);
    }

    /**
     * Orders that overlap with a given time range on a date
     */
    public function scopeOverlappingWindow($query, $date, $windowStart, $windowEnd)
    {
        return $query->whereDate('booking_date', $date)
            ->where(function ($q) use ($windowStart, $windowEnd) {
                $q->where('arrival_window_start', '<', $windowEnd)
                  ->where('arrival_window_end', '>', $windowStart);
            });
    }

    // ==================== HELPERS ====================

    public function isNew(): bool
    {
        return $this->status === self::STATUS_NEW;
    }

    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isPaid(): bool
    {
        return $this->payment_status === self::PAY_PAID;
    }

    public function canChangeStatus(): bool
    {
        return !in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function canChangeSlot(): bool
    {
        return in_array($this->status, [self::STATUS_NEW, self::STATUS_CONFIRMING, self::STATUS_CONFIRMED]);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_NEW => 'Yangi',
            self::STATUS_CONFIRMING => 'Tasdiqlanmoqda',
            self::STATUS_CONFIRMED => 'Tasdiqlangan',
            self::STATUS_IN_PROGRESS => 'Jarayonda',
            self::STATUS_COMPLETED => 'Yakunlangan',
            self::STATUS_CANCELLED => 'Bekor qilingan',
            default => $this->status,
        };
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match($this->payment_status) {
            self::PAY_NOT_PAID => "To'lanmagan",
            self::PAY_PENDING => "Kutilmoqda",
            self::PAY_PAID => "To'langan",
            self::PAY_FAILED => "Xatolik",
            self::PAY_REFUNDED => "Qaytarilgan",
            self::PAY_CANCELLED => "Bekor qilingan",
            default => $this->payment_status,
        };
    }

    /**
     * Get payment status color for UI
     */
    public function getPaymentStatusColorAttribute(): string
    {
        return match($this->payment_status) {
            self::PAY_NOT_PAID => 'gray',
            self::PAY_PENDING => 'yellow',
            self::PAY_PAID => 'green',
            self::PAY_FAILED => 'red',
            self::PAY_REFUNDED => 'orange',
            self::PAY_CANCELLED => 'red',
            default => 'gray',
        };
    }

    /**
     * Mark order as payment pending
     */
    public function markPaymentPending(): self
    {
        $this->update(['payment_status' => self::PAY_PENDING]);
        return $this;
    }

    /**
     * Mark order as paid
     */
    public function markAsPaid(): self
    {
        $this->update([
            'payment_status' => self::PAY_PAID,
            'paid_at' => now(),
        ]);
        return $this;
    }

    /**
     * Mark order payment as failed
     */
    public function markPaymentFailed(): self
    {
        $this->update(['payment_status' => self::PAY_FAILED]);
        return $this;
    }

    /**
     * Mark order as refunded
     */
    public function markAsRefunded(): self
    {
        $this->update(['payment_status' => self::PAY_REFUNDED]);
        return $this;
    }

    /**
     * Check if order can be paid
     */
    public function canBePaid(): bool
    {
        return in_array($this->payment_status, [
            self::PAY_NOT_PAID,
            self::PAY_PENDING,
            self::PAY_FAILED,
        ]) && !$this->isCancelled();
    }

    /**
     * Check if order can be refunded
     */
    public function canBeRefunded(): bool
    {
        return $this->payment_status === self::PAY_PAID;
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->entrance ? "Pod: {$this->entrance}" : null,
            $this->floor ? "Qavat: {$this->floor}" : null,
            $this->apartment ? "Xonadon: {$this->apartment}" : null,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get selected duration in minutes
     */
    public function getDurationMinutesAttribute(): int
    {
        return $this->duration?->duration ?? 60;
    }
}
