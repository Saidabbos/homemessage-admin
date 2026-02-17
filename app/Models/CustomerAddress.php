<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAddress extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'address',
        'entrance',
        'floor',
        'apartment',
        'landmark',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    // ==================== RELATIONS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ==================== SCOPES ====================

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    // ==================== HELPERS ====================

    /**
     * Get full address display string
     */
    public function getFullAddressAttribute(): string
    {
        $parts = [$this->address];

        if ($this->entrance) {
            $parts[] = "Kirish: {$this->entrance}";
        }
        if ($this->floor) {
            $parts[] = "Qavat: {$this->floor}";
        }
        if ($this->apartment) {
            $parts[] = "Xonadon: {$this->apartment}";
        }

        return implode(', ', $parts);
    }

    /**
     * Set this address as default (unset others)
     */
    public function setAsDefault(): void
    {
        // Unset other defaults for this user
        static::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        $this->update(['is_default' => true]);
    }
}
