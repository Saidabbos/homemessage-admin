<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class MasterBlockedTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'master_id',
        'blocked_date',
        'start_time',
        'end_time',
        'reason',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'blocked_date' => 'date',
        ];
    }

    /**
     * Kun bo'yi bloklangan mi?
     */
    public function isFullDay(): bool
    {
        return is_null($this->start_time) && is_null($this->end_time);
    }

    /**
     * Berilgan vaqt bloklangan oraliqqa tushadimi?
     */
    public function coversTime(string $time): bool
    {
        // Kun bo'yi bloklangan
        if ($this->isFullDay()) {
            return true;
        }

        $checkTime = Carbon::parse($time)->format('H:i');
        $start = Carbon::parse($this->start_time)->format('H:i');
        $end = Carbon::parse($this->end_time)->format('H:i');

        return $checkTime >= $start && $checkTime < $end;
    }

    /**
     * Berilgan vaqt oralig'i bloklangan vaqt bilan kesishadimi?
     */
    public function overlapsTimeRange(string $rangeStart, string $rangeEnd): bool
    {
        // Kun bo'yi bloklangan
        if ($this->isFullDay()) {
            return true;
        }

        $blockStart = Carbon::parse($this->start_time)->format('H:i');
        $blockEnd = Carbon::parse($this->end_time)->format('H:i');
        $start = Carbon::parse($rangeStart)->format('H:i');
        $end = Carbon::parse($rangeEnd)->format('H:i');

        // Kesishish tekshiruvi
        return $start < $blockEnd && $end > $blockStart;
    }

    /**
     * Get the master
     */
    public function master(): BelongsTo
    {
        return $this->belongsTo(Master::class);
    }

    /**
     * Get the user who created this block
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get display format for the blocked time
     */
    public function getDisplayAttribute(): string
    {
        $date = $this->blocked_date->format('d.m.Y');
        
        if ($this->isFullDay()) {
            return "{$date} (kun bo'yi)";
        }

        $start = Carbon::parse($this->start_time)->format('H:i');
        $end = Carbon::parse($this->end_time)->format('H:i');
        
        return "{$date} {$start}-{$end}";
    }
}
