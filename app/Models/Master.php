<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;

class Master extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'email',
        'photo',
        'bio',
        'birth_date',
        'gender',
        'experience_years',
        'rating',
        'total_orders',
        'status',
    ];

    public $translatable = ['bio'];

    protected $appends = ['photo_url', 'full_name'];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'experience_years' => 'integer',
            'rating' => 'decimal:1',
            'total_orders' => 'integer',
            'status' => 'boolean',
        ];
    }

    /**
     * Get the master's full name
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the photo URL
     */
    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo && file_exists(public_path('storage/' . $this->photo))) {
            return asset('storage/' . $this->photo);
        }
        return asset('images/master-placeholder.svg');
    }

    /**
     * Get the services that the master provides
     */
    public function serviceTypes(): BelongsToMany
    {
        return $this->belongsToMany(ServiceType::class, 'master_service_type');
    }
}
