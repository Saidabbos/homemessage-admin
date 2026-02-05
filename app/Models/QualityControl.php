<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualityControl extends Model
{
    // Statuses
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_ISSUE_REPORTED = 'issue_reported';

    protected $fillable = [
        'order_id',
        'checked_by',
        'rating_overall',
        'rating_punctuality',
        'rating_quality',
        'rating_cleanliness',
        'rating_communication',
        'has_all_items',
        'arrived_on_time',
        'proper_uniform',
        'polite_behavior',
        'customer_feedback',
        'internal_notes',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'has_all_items' => 'boolean',
        'arrived_on_time' => 'boolean',
        'proper_uniform' => 'boolean',
        'polite_behavior' => 'boolean',
        'completed_at' => 'datetime',
    ];

    // ==================== RELATIONS ====================

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    // ==================== SCOPES ====================

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeWithIssues($query)
    {
        return $query->where('status', self::STATUS_ISSUE_REPORTED);
    }

    // ==================== HELPERS ====================

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function hasIssues(): bool
    {
        return $this->status === self::STATUS_ISSUE_REPORTED;
    }

    public function getAverageRatingAttribute(): ?float
    {
        $ratings = array_filter([
            $this->rating_overall,
            $this->rating_punctuality,
            $this->rating_quality,
            $this->rating_cleanliness,
            $this->rating_communication,
        ]);

        if (empty($ratings)) {
            return null;
        }

        return round(array_sum($ratings) / count($ratings), 1);
    }

    public function getChecklistScoreAttribute(): int
    {
        $score = 0;
        if ($this->has_all_items) $score++;
        if ($this->arrived_on_time) $score++;
        if ($this->proper_uniform) $score++;
        if ($this->polite_behavior) $score++;
        return $score;
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Kutilmoqda',
            self::STATUS_COMPLETED => 'Bajarildi',
            self::STATUS_ISSUE_REPORTED => 'Muammo aniqlandi',
            default => $this->status,
        };
    }

    public function markAsCompleted(): self
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
            'checked_by' => auth()->id(),
        ]);

        return $this;
    }
}
