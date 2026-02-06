<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Master extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'phone',
        'email',
        'photo',
        'bio',
        'birth_date',
        'gender',
        'experience_years',
        'status',
    ];

    public $translatable = ['bio'];

    protected $appends = ['photo_url', 'full_name'];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'experience_years' => 'integer',
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
        if (!$this->photo) {
            return asset('images/master-placeholder.svg');
        }

        // Check if it's a direct public path (starts with /)
        if (str_starts_with($this->photo, '/')) {
            return asset(ltrim($this->photo, '/'));
        }

        // Check storage path
        if (file_exists(public_path('storage/' . $this->photo))) {
            return asset('storage/' . $this->photo);
        }

        return asset('images/master-placeholder.svg');
    }

    /**
     * Get the user account for the master
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the services that the master provides
     */
    public function serviceTypes(): BelongsToMany
    {
        return $this->belongsToMany(ServiceType::class, 'master_service_type');
    }

    /**
     * Get the oils that the master brings
     */
    public function oils(): BelongsToMany
    {
        return $this->belongsToMany(Oil::class, 'master_oil');
    }

    /**
     * Get the orders assigned to this master
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
