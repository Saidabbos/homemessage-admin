<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;

class PressureLevel extends Model
{
    use HasTranslations;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'sort_order',
        'status',
    ];

    public $translatable = ['name', 'description'];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    /**
     * Get the masters that support this pressure level
     */
    public function masters(): BelongsToMany
    {
        return $this->belongsToMany(Master::class, 'master_pressure_level');
    }

    /**
     * Scope for active pressure levels only
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Get ordered pressure levels
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
