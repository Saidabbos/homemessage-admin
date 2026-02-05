<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterSlotBooking extends Model
{
    use HasFactory;

    const STATUS_FREE = 'FREE';
    const STATUS_PENDING = 'PENDING';
    const STATUS_RESERVED = 'RESERVED';
    const STATUS_BLOCKED = 'BLOCKED';

    protected $fillable = [
        'master_id',
        'slot_id',
        'date',
        'status',
        'order_id',
        'block_reason',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function master(): BelongsTo
    {
        return $this->belongsTo(Master::class);
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(Slot::class);
    }

    public function canBook(): bool
    {
        return $this->status === self::STATUS_FREE;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isReserved(): bool
    {
        return $this->status === self::STATUS_RESERVED;
    }

    public function isBlocked(): bool
    {
        return $this->status === self::STATUS_BLOCKED;
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_FREE => 'Bo\'sh',
            self::STATUS_PENDING => 'Kutilmoqda',
            self::STATUS_RESERVED => 'Band',
            self::STATUS_BLOCKED => 'Yopiq',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_FREE => 'success',
            self::STATUS_PENDING => 'warning',
            self::STATUS_RESERVED => 'info',
            self::STATUS_BLOCKED => 'danger',
            default => 'secondary',
        };
    }

    public function scopeForMaster($query, int $masterId)
    {
        return $query->where('master_id', $masterId);
    }

    public function scopeForDate($query, string $date)
    {
        return $query->where('date', $date);
    }

    public function scopeFree($query)
    {
        return $query->where('status', self::STATUS_FREE);
    }

    public function scopeBooked($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_RESERVED]);
    }
}
