<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceTypeDuration extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_type_id',
        'duration',
        'price',
        'is_default',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'duration' => 'integer',
            'price' => 'decimal:2',
            'is_default' => 'boolean',
            'status' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get the service type that owns this duration.
     */
    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    /**
     * Get formatted duration (e.g., "60 min" or "1.5 soat")
     */
    public function getFormattedDurationAttribute(): string
    {
        if ($this->duration >= 60) {
            $hours = floor($this->duration / 60);
            $minutes = $this->duration % 60;
            if ($minutes > 0) {
                return "{$hours} soat {$minutes} min";
            }
            return "{$hours} soat";
        }
        return "{$this->duration} min";
    }

    /**
     * Get formatted price (e.g., "150 000 UZS")
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 0, '', ' ') . ' UZS';
    }

    /**
     * Scope for active durations only.
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope ordered by sort_order then duration.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('duration');
    }
}
