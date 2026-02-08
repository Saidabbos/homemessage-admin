<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class ServiceType extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'image',
        'status',
    ];

    public $translatable = ['name', 'description'];

    protected $appends = ['image_url'];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    /**
     * Get all durations for this service type.
     */
    public function durations(): HasMany
    {
        return $this->hasMany(ServiceTypeDuration::class)->ordered();
    }

    /**
     * Get only active durations.
     */
    public function activeDurations(): HasMany
    {
        return $this->hasMany(ServiceTypeDuration::class)->active()->ordered();
    }

    /**
     * Get the default duration for this service type.
     */
    public function defaultDuration()
    {
        return $this->durations()->where('is_default', true)->first()
            ?? $this->durations()->first();
    }

    /**
     * Get min price across all active durations.
     */
    public function getMinPriceAttribute(): ?float
    {
        return $this->activeDurations()->min('price');
    }

    /**
     * Get max price across all active durations.
     */
    public function getMaxPriceAttribute(): ?float
    {
        return $this->activeDurations()->max('price');
    }

    /**
     * Get price range formatted (e.g., "150 000 - 300 000 UZS")
     */
    public function getPriceRangeAttribute(): string
    {
        $min = $this->min_price;
        $max = $this->max_price;

        if ($min === null) {
            return '-';
        }

        $minFormatted = number_format($min, 0, '', ' ');
        
        if ($min === $max || $max === null) {
            return "{$minFormatted} UZS";
        }

        $maxFormatted = number_format($max, 0, '', ' ');
        return "{$minFormatted} - {$maxFormatted} UZS";
    }

    /**
     * Get the image URL.
     */
    public function getImageUrlAttribute(): string
    {
        if ($this->image && file_exists(public_path('storage/' . $this->image))) {
            return asset('storage/' . $this->image);
        }
        return asset('images/placeholder.svg');
    }

    /**
     * Scope for active service types only.
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
